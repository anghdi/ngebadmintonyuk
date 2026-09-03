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
import { addBadmintonPoint } from './scoreboard.js';
import { isIosDevice, resolvePwaInstallMode } from './pwa-install.js';

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
sidebar?.querySelectorAll('nav a').forEach((link) => {
    link.addEventListener('click', () => toggleSidebar(false));
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleSidebar(false);
    }
});

const installButton = document.querySelector('[data-pwa-install]');
const installGuide = document.querySelector('[data-pwa-guide]');

if (installButton) {
    const ios = isIosDevice(navigator.userAgent, navigator.platform, navigator.maxTouchPoints);
    const standalone = window.matchMedia('(display-mode: standalone)').matches
        || navigator.standalone === true;
    let deferredInstallPrompt;

    const updateInstallButton = () => {
        const mode = resolvePwaInstallMode({
            standalone,
            ios,
            promptAvailable: Boolean(deferredInstallPrompt),
        });

        installButton.hidden = mode === 'installed' || mode === 'hidden';
        installButton.dataset.installMode = mode;
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        updateInstallButton();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = undefined;
        installButton.hidden = true;
        installButton.dataset.installMode = 'installed';
    });

    installButton.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            await deferredInstallPrompt.prompt();
            deferredInstallPrompt = undefined;
            updateInstallButton();

            return;
        }

        if (ios && installGuide) {
            if (typeof installGuide.showModal === 'function') {
                installGuide.showModal();
            } else {
                installGuide.setAttribute('open', '');
            }
        }
    });

    document.querySelectorAll('[data-pwa-guide-close]').forEach((button) => {
        button.addEventListener('click', () => installGuide?.close());
    });
    installGuide?.addEventListener('click', (event) => {
        if (event.target === installGuide) {
            installGuide.close();
        }
    });

    updateInstallButton();
}

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

const scoreboard = document.querySelector('[data-scoreboard]');

if (scoreboard) {
    const storageKey = 'ngebadmintonyuk-scoreboard';
    const defaultState = {
        teams: ['Tim A', 'Tim B'],
        scores: [0, 0],
        games: [0, 0],
        completedGames: [],
        servingTeam: 0,
        gameOver: false,
        matchWinner: null,
    };
    let history = [];
    let state = { ...defaultState };

    try {
        const storedState = JSON.parse(localStorage.getItem(storageKey));

        if (Array.isArray(storedState?.teams) && Array.isArray(storedState?.scores) && Array.isArray(storedState?.games)) {
            state = { ...defaultState, ...storedState };
        }
    } catch {
        localStorage.removeItem(storageKey);
    }

    const copyState = () => JSON.parse(JSON.stringify(state));
    const teamIndex = (team) => team === 'a' ? 0 : 1;
    const teamName = (index) => state.teams[index] || `Tim ${index === 0 ? 'A' : 'B'}`;
    const save = () => localStorage.setItem(storageKey, JSON.stringify(state));

    const render = () => {
        ['a', 'b'].forEach((team) => {
            const index = teamIndex(team);
            const teamCard = scoreboard.querySelector(`[data-score-team="${team}"]`);
            const nameInput = scoreboard.querySelector(`[data-team-name="${team}"]`);

            nameInput.value = state.teams[index];
            scoreboard.querySelector(`[data-score="${team}"]`).textContent = state.scores[index];
            scoreboard.querySelector(`[data-games="${team}"]`).textContent = state.games[index];
            teamCard.classList.toggle('is-serving', state.servingTeam === index && state.matchWinner === null);
            scoreboard.querySelector(`[data-add-point="${team}"]`).disabled = state.gameOver || state.matchWinner !== null;
        });

        const currentGame = state.completedGames.length + 1;
        const servingSide = state.scores[state.servingTeam] % 2 === 0 ? 'kanan' : 'kiri';
        const status = state.matchWinner !== null
            ? `${teamName(state.matchWinner)} memenangkan pertandingan.`
            : state.gameOver
                ? `${teamName(state.scores[0] > state.scores[1] ? 0 : 1)} memenangkan game ${currentGame}.`
                : `${teamName(state.servingTeam)} melakukan servis dari sisi ${servingSide}.`;

        scoreboard.querySelector('[data-game-label]').textContent = `Game ${Math.min(currentGame, 3)}`;
        scoreboard.querySelector('[data-game-history]').textContent = state.completedGames.length
            ? state.completedGames.map((score) => score.join('–')).join(' · ')
            : 'Belum ada game selesai';
        scoreboard.querySelector('[data-score-status]').textContent = status;
        scoreboard.querySelector('[data-score-next]').hidden = !state.gameOver || state.matchWinner !== null;
        scoreboard.querySelector('[data-score-undo]').disabled = history.length === 0;
        save();
    };

    const remember = () => {
        history.push(copyState());
        history = history.slice(-50);
    };

    scoreboard.querySelectorAll('[data-add-point]').forEach((button) => {
        button.addEventListener('click', () => {
            if (state.gameOver || state.matchWinner !== null) {
                return;
            }

            remember();
            const index = teamIndex(button.dataset.addPoint);
            state = addBadmintonPoint(state, index);
            render();
        });
    });

    scoreboard.querySelectorAll('[data-team-name]').forEach((input) => {
        input.addEventListener('input', () => {
            state.teams[teamIndex(input.dataset.teamName)] = input.value.slice(0, 30);
            save();
            render();
            input.focus();
        });
    });

    scoreboard.querySelector('[data-score-next]').addEventListener('click', () => {
        if (!state.gameOver || state.matchWinner !== null) {
            return;
        }

        remember();
        state.scores = [0, 0];
        state.gameOver = false;
        state.servingTeam = state.completedGames.length % 2;
        render();
    });

    scoreboard.querySelector('[data-score-undo]').addEventListener('click', () => {
        const previousState = history.pop();

        if (previousState) {
            state = previousState;
            render();
        }
    });

    scoreboard.querySelector('[data-score-reset]').addEventListener('click', () => {
        if (!window.confirm('Mulai ulang seluruh pertandingan?')) {
            return;
        }

        state = { ...defaultState, teams: [...state.teams], scores: [0, 0], games: [0, 0], completedGames: [] };
        history = [];
        render();
    });

    render();
}
