import assert from 'node:assert/strict';
import test from 'node:test';
import {
    GUTTER_SAFE_ZONE,
    POST_HEIGHT,
    POST_WIDTH,
    drawFeedMaster,
    drawSeedMaster,
    panelSafeArea,
    resolveConnectionPath,
    resolveContentLayout,
    resolveHeroPanel,
    resolvePhotoBox,
    resolveSeedTrajectory,
} from '../../resources/js/feed-studio.js';

test('seed is rendered as one connected master with the fixed three-part copy', () => {
    const text = [];
    const curves = [];
    const lines = [];
    const context = new Proxy({
        fillText(value) { text.push(value); },
        measureText(value) { return { width: value.length * 20 }; },
        lineTo(...values) { lines.push(values); },
        bezierCurveTo(...values) { curves.push(values); },
    }, {
        get(target, key) { return key in target ? target[key] : () => {}; },
    });
    const canvas = {};
    drawSeedMaster(context, canvas, {
        court_line_exit: [{ x: 0.78, edge: 'top', direction: 'diagonal-right' }],
    });

    assert.equal(canvas.width, POST_WIDTH * 3);
    assert.equal(canvas.height, POST_HEIGHT);
    assert.ok(text.includes('Lagi nyari temen main?'));
    assert.ok(text.includes('NgeBadminton YUK!'));
    assert.ok(text.includes('Ramean lebih seru.'));
    assert.ok(curves.some((values) => values.at(-1) === 135));
    assert.ok(lines.some(([, y]) => y < 0));
});

test('seed trajectory follows the stored exit coordinate and direction', () => {
    const width = POST_WIDTH * 3;
    const diagonal = resolveSeedTrajectory({
        court_line_exit: [{ x: 0.78, edge: 'top', direction: 'diagonal-right' }],
    });
    const vertical = resolveSeedTrajectory({
        court_line_exit: [{ x: 0.64, edge: 'top', direction: 'vertical' }],
    });

    assert.equal(diagonal.end.x, width * 0.78);
    assert.ok(diagonal.start.y > POST_HEIGHT * 0.5);
    assert.ok(diagonal.controls.every(({ y }) => y > POST_HEIGHT * 0.5));
    assert.ok(diagonal.controls[1].x < diagonal.end.x);
    assert.equal(vertical.end.x, width * 0.64);
    assert.equal(vertical.controls[1].x, vertical.end.x);
});

test('semantic content areas stay clear of every post gutter', () => {
    for (let panel = 0; panel < 3; panel++) {
        const safe = panelSafeArea(panel);
        assert.equal(safe.x, panel * POST_WIDTH + GUTTER_SAFE_ZONE);
        assert.ok(safe.x + safe.width <= (panel + 1) * POST_WIDTH - GUTTER_SAFE_ZONE);
    }
});

test('a portrait hero always belongs to exactly one panel', () => {
    for (const variant of ['editorial', 'kinetic', 'sideline']) {
        const box = resolvePhotoBox(3, variant);
        assert.equal(box.panel, resolveHeroPanel(3, variant));
        assert.ok(box.x >= box.panel * POST_WIDTH + GUTTER_SAFE_ZONE);
        assert.ok(box.x + box.width <= (box.panel + 1) * POST_WIDTH - GUTTER_SAFE_ZONE);
    }
});

test('connected layouts assign every semantic role to a safe non-photo panel', () => {
    assert.deepEqual(resolveContentLayout(3, 'editorial', true), {
        heroPanel: 2,
        headlinePanel: 0,
        supportingPanel: 1,
        detailsPanel: 1,
        ctaPanel: 1,
    });
    assert.deepEqual(resolveContentLayout(3, 'sideline', true), {
        heroPanel: 0,
        headlinePanel: 1,
        supportingPanel: 2,
        detailsPanel: 2,
        ctaPanel: 2,
    });
});

test('connected renderer draws semantic copy once and keeps single photo posts informative', () => {
    const renderedText = [];
    const context = new Proxy({
        fillText(value) { renderedText.push(value); },
        measureText(value) { return { width: value.length * 10 }; },
    }, {
        get(target, key) { return key in target ? target[key] : () => {}; },
    });
    const design = {
        postCount: 3,
        variant: 'editorial',
        connection: { background: 'off-white' },
        headline: 'Main Minggu Pagi',
        supportingText: 'Datang, main, kenalan.',
        eventDate: '20.09.2026',
        eventTime: '08:00',
        venue: 'GOR Komunitas',
        price: 'Rp35.000',
        cta: 'Daftar sekarang',
    };
    const image = { naturalWidth: 1200, naturalHeight: 1600 };

    drawFeedMaster(context, { width: POST_WIDTH * 3 }, design, image, { zoom: 1, x: 0, y: 0 });

    assert.equal(renderedText.filter((text) => text === design.headline).length, 1);
    assert.equal(renderedText.filter((text) => text === design.supportingText).length, 1);
    assert.equal(renderedText.filter((text) => text.includes('GOR Komunitas')).length, 1);

    renderedText.length = 0;
    drawFeedMaster(context, { width: POST_WIDTH }, { ...design, postCount: 1 }, image, { zoom: 1, x: 0, y: 0 });
    assert.ok(renderedText.includes(design.headline));
    assert.ok(renderedText.includes(design.supportingText));
});

test('connection starts at the matching bottom position and exits through the top edge', () => {
    const connection = {
        incoming_connection: [{ x: 0.78, edge: 'bottom', direction: 'diagonal-right' }],
        court_line_exit: [{ x: 0.26, edge: 'top', direction: 'diagonal-left' }],
    };
    const width = POST_WIDTH * 3;
    const path = resolveConnectionPath(connection, width, POST_HEIGHT, 'editorial');
    assert.deepEqual(path.start, { x: width * 0.78, y: POST_HEIGHT });
    assert.deepEqual(path.end, { x: width * 0.26, y: 0 });
});

test('regeneration changes composition without moving connection endpoints', () => {
    const connection = {
        incoming_connection: [{ x: 0.78, edge: 'bottom', direction: 'diagonal-right' }],
        court_line_exit: [{ x: 0.26, edge: 'top', direction: 'diagonal-left' }],
    };
    const paths = ['editorial', 'kinetic', 'sideline'].map((variant) => resolveConnectionPath(connection, POST_WIDTH * 3, POST_HEIGHT, variant));
    assert.deepEqual(paths[0].start, paths[1].start);
    assert.deepEqual(paths[1].end, paths[2].end);
    assert.equal(new Set(paths.map(({ controls }) => JSON.stringify(controls))).size, 3);
});
