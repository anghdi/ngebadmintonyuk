import assert from 'node:assert/strict';
import test from 'node:test';
import { installRequiredProfileDialog } from '../../resources/js/profile-required.js';

test('opens the profile gate as a modal and prevents escape dismissal', () => {
    let removedOpen = false;
    let opened = false;
    let cancel;
    const dialog = {
        addEventListener(event, handler) { if (event === 'cancel') { cancel = handler; } },
        removeAttribute(attribute) { removedOpen = attribute === 'open'; },
        showModal() { opened = true; },
    };
    installRequiredProfileDialog({ querySelector: () => dialog });
    let prevented = false;
    cancel({ preventDefault() { prevented = true; } });
    assert.equal(removedOpen, true);
    assert.equal(opened, true);
    assert.equal(prevented, true);
});

test('does nothing for a complete profile page', () => {
    assert.equal(installRequiredProfileDialog({ querySelector: () => null }), undefined);
});
