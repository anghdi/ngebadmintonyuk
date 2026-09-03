const showWebPushNotification = (event) => {
    let payload;

    try {
        payload = event.data?.json();
    } catch {
        return;
    }

    if (payload?.source !== 'webpush' || !payload.title) {
        return;
    }

    event.waitUntil(self.registration.showNotification(payload.title, {
        body: payload.body ?? '',
        icon: payload.icon ?? '/notification-icon.png',
        badge: payload.badge ?? '/notification-badge-96.png',
        data: { source: 'webpush', url: payload.url ?? '/dashboard' },
    }));
};

self.addEventListener('push', showWebPushNotification);
self.addEventListener('notificationclick', (event) => {
    if (event.notification.data?.source !== 'webpush') {
        return;
    }

    event.notification.close();
    const targetUrl = new URL(event.notification.data?.url ?? '/dashboard', self.location.origin).href;

    event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
        const openWindow = windows.find((windowClient) => windowClient.url === targetUrl);

        return openWindow ? openWindow.focus() : clients.openWindow(targetUrl);
    }));
});

importScripts('https://www.gstatic.com/firebasejs/12.18.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.18.0/firebase-messaging-compat.js');

try {
    firebase.initializeApp(@json($firebaseConfig));
    firebase.messaging();
} catch {
    // Safari menggunakan Web Push standar melalui listener di atas.
}
