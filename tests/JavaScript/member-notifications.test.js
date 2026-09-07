import assert from 'node:assert/strict';
import test from 'node:test';
import { installMemberNotifications } from '../../resources/js/member-notifications.js';

function browser({ setup = true, permission = 'default', subscription = true, supported = true, saveFails = false } = {}) {
    const events = {};
    const button = { dataset: {}, addEventListener: (name, callback) => { events[name] = callback; } };
    const status = {};
    const calls = { requests: [], redirects: [], permission: 0, subscriptions: 0 };
    const dataset = {
        pushSetup: String(setup), pushSetupUrl: '/aktifkan-notifikasi', pushCurrentInstallation: 'current-device',
        webpushVapidKey: 'YWJj', firebaseServiceWorkerUrl: '/firebase-messaging-sw.js',
        pushStoreUrl: '/push-subscriptions', pushDeleteUrl: '/push-subscriptions',
    };
    const pushSubscription = { toJSON: () => ({ endpoint: 'https://push.test/device', keys: { p256dh: 'key', auth: 'auth' } }) };
    const registration = { pushManager: {
        getSubscription: async () => subscription ? pushSubscription : null,
        subscribe: async () => { calls.subscriptions++; return pushSubscription; },
    } };
    const window = {
        isSecureContext: true, PushManager: {},
        Notification: { permission, requestPermission: async () => { calls.permission++; return window.Notification.permission; } },
        navigator: {
            userAgent: 'test', serviceWorker: {
                register: async () => registration, ready: Promise.resolve(registration), getRegistration: async () => registration,
            },
        },
        atob: (value) => Buffer.from(value, 'base64').toString('binary'),
        addEventListener: (name, callback) => { events[name] = callback; },
        location: { replace: (url) => calls.redirects.push(url) },
        fetch: async (url, options) => {
            calls.requests.push({ url, ...options });
            return { ok: !saveFails, json: async () => ({ redirect_url: '/jadwal?month=2026-10' }) };
        },
    };
    if (!supported) delete window.Notification;
    const document = { querySelector: (selector) => ({
        '[data-push-client]': { dataset },
        '[data-push-opt-in]': { querySelector: (selector) => selector === '[data-push-toggle]' ? button : status },
        'meta[name="csrf-token"]': { content: 'csrf' },
    }[selector]) };
    const client = installMemberNotifications(window, document, { run: (callback) => callback() });
    return { client, window, button, status, calls, events };
}

test('permission is requested only after the member clicks and dismissal keeps activation available', async () => {
    const { client, calls, button, events } = browser();
    await client.ready;
    assert.equal(calls.permission, 0);
    await events.click();
    assert.equal(calls.permission, 1);
    assert.equal(button.disabled, false);
    assert.deepEqual(calls.redirects, []);
    assert.deepEqual(calls.requests, []);
});

test('existing granted subscription binds the device before continuing without another permission prompt', async () => {
    const { client, calls } = browser({ permission: 'granted' });
    await client.ready;
    assert.equal(calls.permission, 0);
    assert.equal(calls.subscriptions, 0);
    assert.equal(calls.requests[0].method, 'POST');
    assert.equal(JSON.parse(calls.requests[0].body).endpoint, 'https://push.test/device');
    assert.deepEqual(calls.redirects, ['/jadwal?month=2026-10']);
});

test('failed server registration keeps the member on setup with a retry button', async () => {
    const { client, calls, button } = browser({ permission: 'granted', saveFails: true });
    await client.ready;
    assert.deepEqual(calls.redirects, []);
    assert.equal(button.disabled, false);
});

test('denied and unsupported browsers show help without requesting permission or continuing', async () => {
    for (const options of [{ permission: 'denied' }, { supported: false }]) {
        const { client, calls, button, status } = browser(options);
        await client.ready;
        assert.equal(button.disabled, true);
        assert.ok(status.textContent.length > 30);
        assert.equal(calls.permission, 0);
        assert.deepEqual(calls.redirects, []);
    }
});

test('revoked permission and missing subscription invalidate the session and return to setup', async () => {
    for (const options of [{ permission: 'denied' }, { permission: 'granted', subscription: false }]) {
        const { client, calls } = browser({ setup: false, ...options });
        await client.ready;
        assert.equal(calls.requests[0].method, 'DELETE');
        assert.deepEqual(calls.redirects, ['/aktifkan-notifikasi']);
    }
});

test('active member pages avoid repeated registration and expose no disable action', async () => {
    const { client, calls, button } = browser({ setup: false, permission: 'granted' });
    await client.ready;
    assert.equal(button.textContent, 'Notifikasi aktif');
    assert.equal(button.disabled, true);
    assert.deepEqual(calls.requests, []);
    assert.deepEqual(calls.redirects, []);
});

test('returning to an open member tab detects a revoked permission', async () => {
    const { client, calls, window, events } = browser({ setup: false, permission: 'granted' });
    await client.ready;
    window.Notification.permission = 'denied';
    await events.focus();
    assert.deepEqual(calls.redirects, ['/aktifkan-notifikasi']);
});

test('new subscriptions are created once even when activation is triggered twice', async () => {
    const { client, calls, window } = browser({ subscription: false });
    await client.ready;
    window.Notification.permission = 'granted';
    await Promise.all([client.enable(), client.enable()]);
    assert.equal(calls.subscriptions, 1);
    assert.equal(calls.requests.length, 1);
});
