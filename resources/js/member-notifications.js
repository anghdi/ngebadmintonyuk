export function installMemberNotifications(window, document, serverLoading) {
    const client = document.querySelector('[data-push-client]');

    if (!client) return null;

    const { navigator, Notification } = window;
    const card = document.querySelector('[data-push-opt-in]');
    const button = card?.querySelector('[data-push-toggle]');
    const status = card?.querySelector('[data-push-status]');
    const setup = client.dataset.pushSetup === 'true';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let busy = false;

    const setState = (state, message) => {
        if (status) status.textContent = message;
        if (!button) return;
        button.disabled = ['loading', 'active', 'unsupported', 'denied'].includes(state);
        button.dataset.pushState = state;
        button.textContent = {
            active: 'Notifikasi aktif', loading: 'Memproses…', inactive: 'Aktifkan notifikasi',
            unsupported: 'Belum tersedia', denied: 'Ubah izin di pengaturan browser',
        }[state];
    };

    const request = async (method, url, payload) => {
        const response = await window.fetch(url, {
            method, credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload),
        });
        if (!response.ok) throw new Error('Gagal menyimpan pengaturan. Periksa koneksi lalu coba lagi.');
        return response;
    };

    const requireSetup = async () => {
        if (setup) return;
        try {
            if (client.dataset.pushCurrentInstallation) {
                await request('DELETE', client.dataset.pushDeleteUrl, {
                    installation_id: client.dataset.pushCurrentInstallation,
                });
            }
        } finally {
            window.location.replace(client.dataset.pushSetupUrl);
        }
    };

    const supported = () => window.isSecureContext && Notification
        && 'serviceWorker' in navigator && 'PushManager' in window;

    const enable = async () => {
        if (busy) return;
        busy = true;
        setState('loading', 'Menyiapkan notifikasi pada perangkat ini…');
        try {
            const permission = Notification.permission === 'granted'
                ? 'granted' : await Notification.requestPermission();
            if (permission !== 'granted') {
                setState(permission === 'denied' ? 'denied' : 'inactive',
                    permission === 'denied'
                        ? 'Izin ditolak. Ubah izin Notifikasi menjadi Izinkan pada pengaturan situs, lalu muat ulang.'
                        : 'Aktivasi belum selesai. Tekan Aktifkan notifikasi, lalu pilih Izinkan.');
                return;
            }

            await serverLoading.run(async () => {
                await navigator.serviceWorker.register(client.dataset.firebaseServiceWorkerUrl);
                const registration = await navigator.serviceWorker.ready;
                const value = client.dataset.webpushVapidKey;
                const base64 = (value + '='.repeat((4 - value.length % 4) % 4)).replaceAll('-', '+').replaceAll('_', '/');
                const subscription = await registration.pushManager.getSubscription()
                    ?? await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: Uint8Array.from(window.atob(base64), (character) => character.charCodeAt(0)),
                    });
                const serialized = subscription.toJSON();
                const response = await request('POST', client.dataset.pushStoreUrl, {
                    driver: 'webpush', endpoint: serialized.endpoint,
                    public_key: serialized.keys?.p256dh, auth_token: serialized.keys?.auth,
                    content_encoding: 'aes128gcm', user_agent: navigator.userAgent,
                });
                const payload = await response.json();
                setState('active', 'Notifikasi aktif pada perangkat ini.');
                if (setup) window.location.replace(payload.redirect_url);
            }, 'Sedang mengaktifkan notifikasi…');
        } catch {
            setState('inactive', 'Aktivasi gagal. Periksa koneksi dan izin browser, lalu coba lagi. Jika tetap gagal, hubungi admin.');
        } finally {
            busy = false;
        }
    };

    const check = async () => {
        if (busy) return;
        busy = true;
        try {
            if (!supported()) {
                setState('unsupported', 'Browser belum mendukung notifikasi. Di iPhone/iPad, pasang ke Layar Utama lalu buka dari ikon aplikasi. Gunakan HTTPS dan browser yang mendukung Web Push.');
                await requireSetup();
            } else if (!client.dataset.webpushVapidKey) {
                setState('unsupported', 'Layanan notifikasi belum disiapkan. Hubungi admin komunitas.');
                await requireSetup();
            } else if (Notification.permission !== 'granted') {
                setState(Notification.permission === 'denied' ? 'denied' : 'inactive',
                    Notification.permission === 'denied'
                        ? 'Izin ditolak. Ubah izin Notifikasi menjadi Izinkan pada pengaturan situs, lalu muat ulang.'
                        : 'Tekan Aktifkan notifikasi, lalu pilih Izinkan pada permintaan browser.');
                await requireSetup();
            } else if (!setup) {
                const registration = await navigator.serviceWorker.getRegistration();
                const subscription = await registration?.pushManager.getSubscription();
                if (!subscription) await requireSetup();
                else setState('active', 'Notifikasi aktif pada perangkat ini.');
            }
        } catch {
            setState('inactive', 'Pemeriksaan notifikasi gagal. Muat ulang halaman untuk mencoba kembali.');
        } finally {
            busy = false;
        }
    };

    button?.addEventListener('click', enable);
    window.addEventListener('focus', check);
    window.addEventListener('pageshow', (event) => { if (event.persisted) check(); });
    navigator.permissions?.query({ name: 'notifications' }).then((permission) => {
        permission.addEventListener('change', check);
    }).catch(() => {});

    const ready = check().then(() => {
        if (setup && supported() && client.dataset.webpushVapidKey && Notification.permission === 'granted') {
            return enable();
        }
    });

    return { ready, enable, check };
}
