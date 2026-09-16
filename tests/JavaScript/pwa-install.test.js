import assert from 'node:assert/strict';
import test from 'node:test';
import { hasInstalledPwa, installPwaAppGate, isIosDevice, isStandalonePwa, markPwaInstalled, resolvePwaInstallMode } from '../../resources/js/pwa-install.js';

test('detects iPhone and iPad desktop mode', () => {
    assert.equal(isIosDevice('Mozilla/5.0 (iPhone)', 'iPhone', 5), true);
    assert.equal(isIosDevice('Mozilla/5.0 (Macintosh)', 'MacIntel', 5), true);
    assert.equal(isIosDevice('Mozilla/5.0 (Linux; Android 15)', 'Linux armv8l', 5), false);
});

test('uses the native install prompt when it is available', () => {
    assert.equal(resolvePwaInstallMode({ standalone: false, ios: false, promptAvailable: true }), 'prompt');
});

test('shows install guidance on iOS and hides it after installation', () => {
    assert.equal(resolvePwaInstallMode({ standalone: false, ios: true, promptAvailable: false }), 'ios-guide');
    assert.equal(resolvePwaInstallMode({ standalone: true, ios: true, promptAvailable: true }), 'installed');
});

test('keeps the install action hidden on unsupported browsers', () => {
    assert.equal(resolvePwaInstallMode({ standalone: false, ios: false, promptAvailable: false }), 'hidden');
});

function browser({ standalone = false, installed = false, serverKnown = false, relatedApps } = {}) {
    const events = {};
    const storage = new Map(installed ? [['ngebadmintonyuk-pwa-installed', 'true']] : []);
    const requests = [];
    const gateEvents = {};
    const gate = {
        open: false,
        dataset: { pwaInstallationUrl: '/app-installation', pwaKnownInstalled: String(serverKnown) },
        addEventListener: (name, callback) => { gateEvents[name] = callback; },
        showModal: () => { gate.open = true; },
        close: () => { gate.open = false; },
    };
    const window = {
        navigator: { standalone },
        matchMedia: () => ({ matches: standalone }),
        localStorage: {
            getItem: (key) => storage.get(key) ?? null,
            setItem: (key, value) => storage.set(key, value),
            removeItem: (key) => storage.delete(key),
        },
        addEventListener: (name, callback) => { events[name] = callback; },
        fetch: async (url, options) => { requests.push({ url, ...options }); return { ok: true }; },
    };
    if (relatedApps !== undefined) {
        window.navigator.getInstalledRelatedApps = async () => relatedApps;
    }
    const document = { querySelector: (selector) => ({
        '[data-pwa-app-gate]': gate,
        'meta[name="csrf-token"]': { content: 'csrf' },
    }[selector] ?? null) };

    return { window, gate, events, gateEvents, document, requests };
}

test('records standalone launches locally and on the member account', async () => {
    const { window, document, requests } = browser({ standalone: true });

    assert.equal(isStandalonePwa(window), true);
    assert.equal(hasInstalledPwa(window), true);
    markPwaInstalled(window);
    const client = installPwaAppGate(window, document);
    assert.equal(await client.ready, false);
    await Promise.resolve();
    assert.equal(window.localStorage.getItem('ngebadmintonyuk-pwa-installed'), 'true');
    assert.equal(requests[0].url, '/app-installation');
    assert.equal(requests[0].headers['X-CSRF-TOKEN'], 'csrf');
});

test('blocks browser access when Chromium confirms the PWA is installed', async () => {
    const { window, document, gate, events, gateEvents } = browser({ relatedApps: [{ platform: 'webapp' }] });

    const client = installPwaAppGate(window, document);

    assert.equal(await client.ready, true);
    assert.equal(gate.open, true);
    let prevented = false;
    gateEvents.cancel({ preventDefault: () => { prevented = true; } });
    assert.equal(prevented, true);

    gate.open = false;
    events.appinstalled();
    assert.equal(gate.open, true);
});

test('uses the account installation marker when browser detection is unavailable', async () => {
    const { window, document, gate } = browser({ serverKnown: true });

    const client = installPwaAppGate(window, document);

    assert.equal(await client.ready, true);
    assert.equal(gate.open, true);
});

test('clears a stale browser marker when Chromium reports the PWA was removed', async () => {
    const { window, document, gate } = browser({ installed: true, serverKnown: true, relatedApps: [] });

    const client = installPwaAppGate(window, document);

    assert.equal(await client.ready, false);
    assert.equal(gate.open, false);
    assert.equal(window.localStorage.getItem('ngebadmintonyuk-pwa-installed'), null);
});

test('allows an installed PWA to continue in standalone mode', async () => {
    const { window, document, gate } = browser({ standalone: true, installed: true });

    const client = installPwaAppGate(window, document);

    assert.equal(await client.ready, false);
    assert.equal(gate.open, false);
});
