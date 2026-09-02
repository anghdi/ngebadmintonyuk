import { initializeApp } from 'firebase/app';
import {
    getMessaging,
    isSupported,
    onMessage,
    onRegistered,
    onUnregistered,
    register as registerForPush,
    unregister as unregisterFromPush,
} from 'firebase/messaging';

const sidebar = document.querySelector('#sidebar');
const menu = document.querySelector('[data-sidebar-open]');

function toggleSidebar(open) {
    sidebar?.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
    menu?.setAttribute('aria-expanded', open ? 'true' : 'false');
}

menu?.addEventListener('click', () => toggleSidebar(true));
document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => toggleSidebar(false));
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleSidebar(false);
    }
});

document.querySelectorAll('[data-member-select]').forEach((select) => {
    select.addEventListener('change', () => {
        const option = select.options[select.selectedIndex];
        const form = select.closest('form');

        if (!option?.value || !form) {
            return;
        }

        const nameInput = form.querySelector('input[name="name"]');
        const phoneInput = form.querySelector('input[name="phone"]');

        if (nameInput) {
            nameInput.value = option.dataset.memberName ?? '';
        }

        if (phoneInput && option.dataset.memberPhone) {
            phoneInput.value = option.dataset.memberPhone;
        }
    });
});

document.querySelectorAll('[data-copy-text]').forEach((button) => {
    button.addEventListener('click', async () => {
        const text = button.dataset.copyText;

        if (!text) {
            return;
        }

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                const input = document.createElement('textarea');
                input.value = text;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.append(input);
                input.select();

                const copied = document.execCommand('copy');
                input.remove();

                if (!copied) {
                    throw new Error('Browser tidak mendukung penyalinan otomatis.');
                }
            }

            button.textContent = 'Tersalin';
            button.setAttribute('aria-label', `${button.dataset.copyLabel ?? 'Nomor rekening'} tersalin`);
        } catch {
            button.textContent = 'Gagal menyalin';
        }

        window.setTimeout(() => {
            button.textContent = 'Salin';
            button.setAttribute('aria-label', button.dataset.copyLabel ?? 'Salin nomor rekening');
        }, 1800);
    });
});

const pushClient = document.querySelector('[data-push-client]');

if (pushClient) {
    const pushCard = document.querySelector('[data-push-opt-in]');
    const pushButton = pushCard?.querySelector('[data-push-toggle]');
    const pushStatus = pushCard?.querySelector('[data-push-status]');
    const installationStorageKey = 'ngebadmintonyuk-push-installation-id';
    const legacyInstallationStorageKey = 'ngebadmintonyuk-firebase-installation-id';
    const driverStorageKey = 'ngebadmintonyuk-push-driver';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let messaging;
    let pushDriver;
    let serviceWorkerRegistration;

    const setPushState = (state, message) => {
        if (pushStatus) {
            pushStatus.textContent = message;
        }

        if (!pushButton) {
            return;
        }

        pushButton.disabled = state === 'loading' || state === 'unsupported' || state === 'denied';
        pushButton.dataset.pushState = state;
        pushButton.textContent = {
            active: 'Nonaktifkan',
            denied: 'Izin dinonaktifkan',
            inactive: 'Aktifkan',
            loading: 'Memproses',
            unsupported: 'Tidak tersedia',
        }[state];
    };

    const storeSubscription = async (payload) => {
        const response = await fetch(pushClient.dataset.pushStoreUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                ...payload,
                user_agent: navigator.userAgent,
            }),
        });

        if (!response.ok) {
            throw new Error('Gagal menyimpan perangkat.');
        }

        const responsePayload = await response.json();

        localStorage.setItem(installationStorageKey, responsePayload.installation_id);
        localStorage.setItem(driverStorageKey, payload.driver);
        localStorage.removeItem(legacyInstallationStorageKey);
        setPushState('active', 'Notifikasi aktif pada perangkat ini.');
    };

    const removeInstallation = async (installationId) => {
        if (!installationId) {
            return;
        }

        await fetch(pushClient.dataset.pushDeleteUrl, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ installation_id: installationId }),
        });
        localStorage.removeItem(installationStorageKey);
        localStorage.removeItem(legacyInstallationStorageKey);
        localStorage.removeItem(driverStorageKey);
    };

    const base64UrlToUint8Array = (value) => {
        const padding = '='.repeat((4 - (value.length % 4)) % 4);
        const base64 = (value + padding).replaceAll('-', '+').replaceAll('_', '/');
        const rawData = window.atob(base64);

        return Uint8Array.from([...rawData].map((character) => character.charCodeAt(0)));
    };

    const serializeWebPushSubscription = (subscription) => {
        const serialized = subscription.toJSON();

        return {
            driver: 'webpush',
            endpoint: serialized.endpoint,
            public_key: serialized.keys?.p256dh,
            auth_token: serialized.keys?.auth,
            content_encoding: 'aes128gcm',
        };
    };

    const showForegroundNotification = (payload) => {
        const title = payload.notification?.title;

        if (!title) {
            return;
        }

        const toast = document.createElement(payload.data?.url ? 'a' : 'div');
        toast.className = 'push-toast';
        toast.textContent = title;

        if (payload.data?.url) {
            toast.href = payload.data.url;
        }

        const body = document.createElement('small');
        body.textContent = payload.notification?.body ?? '';
        toast.append(body);
        document.body.append(toast);
        window.setTimeout(() => toast.remove(), 7000);
    };

    const enablePush = async () => {
        setPushState('loading', 'Memproses permintaan...');

        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            setPushState('denied', 'Izin notifikasi dinonaktifkan pada browser.');

            return;
        }

        serviceWorkerRegistration ??= await navigator.serviceWorker.register(
            pushClient.dataset.firebaseServiceWorkerUrl,
        );

        if (pushDriver === 'fcm') {
            await registerForPush(messaging, {
                vapidKey: pushClient.dataset.firebaseVapidKey,
                serviceWorkerRegistration,
            });

            return;
        }

        if (!pushClient.dataset.webpushVapidKey) {
            throw new Error('Konfigurasi Web Push belum tersedia.');
        }

        const subscription = await serviceWorkerRegistration.pushManager.getSubscription()
            ?? await serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: base64UrlToUint8Array(pushClient.dataset.webpushVapidKey),
            });

        await storeSubscription(serializeWebPushSubscription(subscription));
    };

    const disablePush = async () => {
        setPushState('loading', 'Memproses permintaan...');
        const installationId = localStorage.getItem(installationStorageKey);

        if (localStorage.getItem(driverStorageKey) === 'webpush') {
            serviceWorkerRegistration ??= await navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            await subscription?.unsubscribe();
        } else {
            await unregisterFromPush(messaging);
        }

        await removeInstallation(installationId);
        setPushState('inactive', 'Notifikasi dinonaktifkan pada perangkat ini.');
    };

    const initializePush = async () => {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            setPushState('unsupported', 'Notifikasi tidak didukung pada browser ini.');

            return;
        }

        const supportsFirebase = await isSupported().catch(() => false);
        const supportsWebPush = 'PushManager' in window && Boolean(pushClient.dataset.webpushVapidKey);

        if (!supportsFirebase && !supportsWebPush) {
            setPushState('unsupported', 'Notifikasi tidak tersedia pada browser ini.');

            return;
        }

        pushDriver = supportsFirebase ? 'fcm' : 'webpush';

        if (pushDriver === 'fcm') {
            const firebaseConfig = JSON.parse(pushClient.dataset.firebaseConfig);
            messaging = getMessaging(initializeApp(firebaseConfig));

            onRegistered(messaging, (installationId) => {
                storeSubscription({ driver: 'fcm', installation_id: installationId }).catch(() => {
                    setPushState('inactive', 'Aktivasi gagal. Silakan coba kembali.');
                });
            });
            onUnregistered(messaging, (installationId) => {
                removeInstallation(installationId).catch(() => {});
            });
            onMessage(messaging, showForegroundNotification);
        }

        pushButton?.addEventListener('click', () => {
            const operation = pushButton.dataset.pushState === 'active' ? disablePush() : enablePush();

            operation.catch(() => {
                setPushState('inactive', 'Permintaan gagal diproses. Silakan coba kembali.');
            });
        });

        const installationId = localStorage.getItem(installationStorageKey)
            ?? localStorage.getItem(legacyInstallationStorageKey);

        if (Notification.permission === 'denied') {
            setPushState('denied', 'Izin notifikasi dinonaktifkan pada browser.');
        } else if (Notification.permission === 'granted') {
            if (installationId && localStorage.getItem(driverStorageKey) === pushDriver) {
                setPushState('active', 'Notifikasi aktif pada perangkat ini.');
            }

            await enablePush();
        } else {
            setPushState('inactive', 'Terima pembaruan jadwal, ketersediaan slot, dan informasi penting.');
        }
    };

    initializePush().catch(() => {
        setPushState('inactive', 'Layanan notifikasi belum tersedia. Silakan coba kembali.');
    });
}
