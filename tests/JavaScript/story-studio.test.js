import assert from 'node:assert/strict';
import test from 'node:test';
import { readFileSync } from 'node:fs';
import { getStoryPhotoPlacement, storySize, photoBox, drawStory, installStoryStudio } from '../../resources/js/story-studio.js';

test('exports the portrait story dimensions', () => {
    assert.deepEqual(storySize, { width: 1080, height: 1920 });
});

test('portrait and landscape photos cover the frame without empty edges', () => {
    for (const [width, height] of [[4000, 3000], [3000, 4000], [800, 800]]) {
        for (const x of [-1, 0, 1]) {
            for (const y of [-1, 0, 1]) {
                const placement = getStoryPhotoPlacement(width, height, 2, x, y);
                assert.ok(placement.x <= 48);
                assert.ok(placement.y <= photoBox.y);
                assert.ok(placement.x + placement.width >= 1032);
                assert.ok(placement.y + placement.height >= photoBox.y + photoBox.height);
            }
        }
    }
});

test('limits zoom and position to the frame bounds', () => {
    assert.deepEqual(getStoryPhotoPlacement(800, 800, 0, -10, 10), getStoryPhotoPlacement(800, 800, 1, -1, 1));
});

test('does not initialize outside the story page', () => {
    assert.equal(installStoryStudio({}, { querySelector: () => null }), undefined);
});

test('all three designs use a compact tagline without an extra caption', () => {
    for (const theme of ['blue', 'yellow', 'minimal']) {
        const texts = [];
        const context = new Proxy({ fillText(text) { texts.push({ text, font: this.font }); } }, {
            get(target, key) { return key in target ? target[key] : () => {}; },
        });
        drawStory(context, null, null, { theme, zoom: 1, x: 0, y: 0 });
        const tagline = texts.find(({ text }) => text === 'MAIN BARENG, SEHAT & SERU!');
        assert.ok(tagline.font.includes('42px'));
        assert.equal(texts.some(({ text }) => text.includes('Jumat')), false);
    }
});

test('design selection comes before photo upload and has three previews', () => {
    const template = readFileSync(new URL('../../resources/views/story-studio.blade.php', import.meta.url), 'utf8');
    assert.ok(template.indexOf('story-design-picker') < template.indexOf('data-story-camera>'));
    assert.equal((template.match(/data-story-thumbnail=/g) ?? []).length, 3);
    assert.equal(template.includes('data-story-caption'), false);
});

test('accepts photos and videos while keeping export controls device local', () => {
    const template = readFileSync(new URL('../../resources/views/story-studio.blade.php', import.meta.url), 'utf8');
    const script = readFileSync(new URL('../../resources/js/story-studio.js', import.meta.url), 'utf8');

    assert.ok(template.includes('accept="image/*,video/*"'));
    assert.ok(script.includes('canvas.captureStream(30)'));
    assert.ok(script.includes('new windowObject.MediaRecorder'));
    assert.equal(script.includes('fetch('), false);
});

test('twibbon designs have different compositions, not just different colors', () => {
    const compositions = ['blue', 'yellow', 'minimal'].map((theme) => {
        const commands = [];
        const context = new Proxy({}, {
            get(target, key) {
                return key in target ? target[key] : (...args) => commands.push([key, ...args]);
            },
        });
        drawStory(context, null, null, { theme, zoom: 1, x: 0, y: 0 });
        assert.ok(commands.some(([name]) => name === 'bezierCurveTo'));
        assert.ok(commands.some(([name]) => name === 'ellipse'));

        return JSON.stringify(commands);
    });

    assert.equal(new Set(compositions).size, 3);
});
