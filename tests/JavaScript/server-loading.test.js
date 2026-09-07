import assert from 'node:assert/strict';
import test from 'node:test';
import { bindServerLoading, createLoadingController, shouldLoadLink } from '../../resources/js/server-loading.js';

function setup() {
    const timers = new Map();
    const events = [];
    let timerId = 0;
    const controller = createLoadingController({
        show: (message) => events.push(['show', message]),
        hide: () => events.push(['hide']),
        slow: () => events.push(['slow']),
        setTimer: (callback, delay) => { timers.set(++timerId, { callback, delay }); return timerId; },
        clearTimer: (id) => timers.delete(id),
    });
    const tick = (delay) => {
        for (const [id, timer] of timers) {
            if (timer.delay === delay) { timers.delete(id); timer.callback(); }
        }
    };

    return { controller, timers, events, tick };
}

const link = (href, attributes = {}) => ({
    target: attributes.target,
    hasAttribute: (name) => name in attributes,
    getAttribute: (name) => name === 'href' ? href : attributes[name],
});
const click = { button: 0, defaultPrevented: false };
const current = 'https://community.test/jadwal?month=2026-09';

test('only same-window page navigation shows loading', () => {
    assert.equal(shouldLoadLink(click, link('/dashboard'), current), true);
    for (const [href, attributes] of [
        ['#players', {}], ['/jadwal?month=2026-09#players', {}],
        ['mailto:admin@example.com', {}], ['https://other.test', {}],
        ['/proof', { target: '_blank' }], ['/report', { download: '' }],
        ['/reports/pdf', { 'data-no-loading': '' }],
    ]) {
        assert.equal(shouldLoadLink(click, link(href, attributes), current), false);
    }
    for (const modifier of ['ctrlKey', 'metaKey', 'shiftKey', 'altKey', 'defaultPrevented']) {
        assert.equal(shouldLoadLink({ ...click, [modifier]: true }, link('/dashboard'), current), false);
    }
    assert.equal(shouldLoadLink({ ...click, button: 1 }, link('/dashboard'), current), false);
});

test('fast requests do not flash and concurrent requests finish independently', () => {
    const { controller, tick, events, timers } = setup();
    controller.start()();
    tick(160);
    assert.equal(events.some(([type]) => type === 'show'), false);
    const first = controller.start('Saving');
    const second = controller.start();
    tick(160);
    assert.deepEqual(events.at(-1), ['show', 'Saving']);
    first();
    assert.deepEqual(events.at(-1), ['show', 'Saving']);
    tick(10000);
    assert.deepEqual(events.at(-1), ['slow']);
    second();
    assert.deepEqual(events.at(-1), ['hide']);
    assert.equal(timers.size, 0);
});

test('failed async work clears loading and preserves its error', async () => {
    const { controller, events, timers } = setup();
    await assert.rejects(controller.run(async () => { throw new Error('Offline'); }), /Offline/);
    assert.deepEqual(events.at(-1), ['hide']);
    assert.equal(timers.size, 0);
    assert.equal(await controller.run(async () => 42), 42);
});

test('dismiss hides the indicator without cancelling the running operation', async () => {
    const { controller, events, tick } = setup();
    let resolve;
    const operation = controller.run(() => new Promise((done) => { resolve = done; }));
    tick(160);
    controller.dismiss();
    assert.deepEqual(events.at(-1), ['hide']);
    resolve('saved');
    assert.equal(await operation, 'saved');
});

test('reset clears stale navigation and old completion does not hide a new request', () => {
    const { controller, tick, events } = setup();
    const stale = controller.start();
    controller.reset();
    const finish = controller.start('New page');
    tick(160);
    stale();
    assert.deepEqual(events.at(-1), ['show', 'New page']);
    finish();
    assert.deepEqual(events.at(-1), ['hide']);
});

test('submission respects cancellation and preserves named submit buttons while preventing duplicates', () => {
    const handlers = new Map();
    const { controller, tick, events } = setup();
    const window = { location: { href: current }, addEventListener: (name, handler) => handlers.set(name, handler) };
    const document = { readyState: 'complete', querySelector: () => null };
    bindServerLoading(window, document, controller);
    const submitter = { name: 'status', value: 'approved', disabled: false, getAttribute: () => null, hasAttribute: () => false };
    const form = { method: 'post', target: '', hasAttribute: () => false };
    const event = { target: form, submitter, defaultPrevented: true, preventDefault() { this.defaultPrevented = true; } };
    handlers.get('submit')(event);
    tick(160);
    assert.equal(events.length, 0);
    event.defaultPrevented = false;
    handlers.get('submit')(event);
    tick(160);
    assert.deepEqual(events.at(-1), ['show', 'Sedang mengirim data…']);
    assert.equal(submitter.disabled, false);
    assert.equal(submitter.value, 'approved');
    handlers.get('submit')(event);
    assert.equal(event.defaultPrevented, true);
    handlers.get('pageshow')();
    event.defaultPrevented = false;
    handlers.get('submit')(event);
    assert.equal(event.defaultPrevented, false);
});
