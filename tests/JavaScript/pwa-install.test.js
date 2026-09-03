import assert from 'node:assert/strict';
import test from 'node:test';
import { isIosDevice, resolvePwaInstallMode } from '../../resources/js/pwa-install.js';

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
