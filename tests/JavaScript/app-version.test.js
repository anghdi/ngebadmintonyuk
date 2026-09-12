import assert from 'node:assert/strict';
import test from 'node:test';
import { installAppVersionUpdates } from '../../resources/js/app-version.js';

function createEnvironment(version) {
    const listeners = new Map();
    const storage = new Map();
    const banner = {
        dataset: { versionUrl: '/app-version' },
        hidden: true,
        querySelector(selector) {
            return {
                addEventListener(event, handler) {
                    listeners.set(`${selector}:${event}`, handler);
                },
            };
        },
    };
    const documentObject = {
        visibilityState: 'visible',
        addEventListener() {},
        querySelector: () => banner,
    };
    const windowObject = {
        addEventListener() {},
        fetch: async () => ({ ok: true, json: async () => ({ version: version.value }) }),
        localStorage: {
            getItem: (key) => storage.get(key) ?? null,
            setItem: (key, value) => storage.set(key, value),
        },
        location: { reload() {} },
    };

    return { banner, documentObject, listeners, storage, windowObject };
}

test('stores the first known version without showing an update', async () => {
    const version = { value: '1.0.0' };
    const environment = createEnvironment(version);
    const updater = installAppVersionUpdates(environment.windowObject, environment.documentObject);

    await updater.checkVersion(true);

    assert.equal(environment.storage.get('ngebadmintonyuk-app-version'), '1.0.0');
    assert.equal(environment.banner.hidden, true);
});

test('shows the update banner when the server version changes', async () => {
    const version = { value: '1.0.0' };
    const environment = createEnvironment(version);
    const updater = installAppVersionUpdates(environment.windowObject, environment.documentObject);

    await updater.checkVersion(true);
    version.value = '1.1.0';
    await updater.checkVersion(true);

    assert.equal(environment.banner.hidden, false);
});
