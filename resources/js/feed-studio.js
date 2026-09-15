export const POST_WIDTH = 1080;
export const POST_HEIGHT = 1350;
export const GUTTER_SAFE_ZONE = 96;

const VARIANTS = ['editorial', 'kinetic', 'sideline'];
const SEED_COPY = ['Lagi nyari temen main?', 'NgeBadminton YUK!', 'Ramean lebih seru.'];
const PALETTES = {
    'off-white': { background: '#f7f4ec', ink: '#102656', muted: '#53617a', contrast: '#2455f5' },
    'royal-blue': { background: '#2455f5', ink: '#ffffff', muted: '#dfe7ff', contrast: '#102656' },
    navy: { background: '#102656', ink: '#ffffff', muted: '#dce4f4', contrast: '#2455f5' },
};

export function panelSafeArea(panelIndex, inset = GUTTER_SAFE_ZONE) {
    return { x: panelIndex * POST_WIDTH + inset, y: inset, width: POST_WIDTH - inset * 2, height: POST_HEIGHT - inset * 2 };
}

export function resolveHeroPanel(postCount, variant) {
    if (postCount === 1) return 0;
    if (postCount === 2) return variant === 'sideline' ? 0 : 1;
    return { editorial: 2, kinetic: 1, sideline: 0 }[variant] ?? 2;
}

export function resolvePhotoBox(postCount, variant) {
    const panel = resolveHeroPanel(postCount, variant);
    const safe = panelSafeArea(panel);
    return { panel, x: safe.x, y: 142, width: safe.width, height: 1066 };
}

export function resolveConnectionPath(connection, width, height, variant) {
    const incoming = connection?.incoming_connection?.[0] ?? { x: 0.08, edge: 'bottom', direction: 'diagonal-right' };
    const outgoing = connection?.court_line_exit?.[0] ?? { x: 0.78, edge: 'top', direction: 'diagonal-right' };
    const start = { x: Number(incoming.x) * width, y: height };
    const end = { x: Number(outgoing.x) * width, y: 0 };
    const bends = {
        editorial: [{ x: start.x - width * 0.08, y: height * 0.68 }, { x: end.x + width * 0.12, y: height * 0.34 }],
        kinetic: [{ x: start.x + width * 0.18, y: height * 0.7 }, { x: end.x - width * 0.16, y: height * 0.28 }],
        sideline: [{ x: start.x, y: height * 0.62 }, { x: end.x, y: height * 0.36 }],
    };
    return { start, end, controls: bends[variant] ?? bends.editorial, incoming, outgoing };
}

function wrapText(context, text, x, y, maxWidth, lineHeight, maxLines = 3) {
    const words = String(text || '').split(/\s+/).filter(Boolean);
    let line = '';
    let lineNumber = 0;
    for (const word of words) {
        const candidate = line ? `${line} ${word}` : word;
        if (context.measureText(candidate).width > maxWidth && line) {
            context.fillText(line, x, y + lineNumber * lineHeight);
            line = word;
            lineNumber++;
            if (lineNumber >= maxLines - 1) break;
        } else {
            line = candidate;
        }
    }
    if (lineNumber < maxLines && line) context.fillText(line, x, y + lineNumber * lineHeight);
}

function loadImage(windowObject, source) {
    if (!source) return Promise.resolve(null);
    return new Promise((resolve, reject) => {
        const image = new windowObject.Image();
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = source;
    });
}

function drawPhoto(context, image, box, settings) {
    context.save();
    context.beginPath();
    context.roundRect(box.x, box.y, box.width, box.height, 34);
    context.clip();
    context.fillStyle = '#dce4f4';
    context.fillRect(box.x, box.y, box.width, box.height);
    if (image) {
        const scale = Math.max(box.width / image.naturalWidth, box.height / image.naturalHeight) * settings.zoom;
        const width = image.naturalWidth * scale;
        const height = image.naturalHeight * scale;
        const x = box.x - (width - box.width) * (settings.x + 1) / 2;
        const y = box.y - (height - box.height) * (settings.y + 1) / 2;
        context.drawImage(image, x, y, width, height);
    }
    context.restore();
    context.strokeStyle = '#ffd23f';
    context.lineWidth = 12;
    context.strokeRect(box.x + 18, box.y + 18, box.width - 36, box.height - 36);
}

function drawConnectionGeometry(context, width, connection, variant, palette) {
    const path = resolveConnectionPath(connection, width, POST_HEIGHT, variant);
    context.save();
    context.strokeStyle = palette.ink;
    context.globalAlpha = 0.17;
    context.lineWidth = 7;
    context.strokeRect(54, 76, width - 108, POST_HEIGHT - 152);
    context.beginPath();
    context.moveTo(0, POST_HEIGHT * 0.32);
    context.lineTo(width, POST_HEIGHT * 0.32);
    context.moveTo(0, POST_HEIGHT * 0.74);
    context.lineTo(width, POST_HEIGHT * 0.74);
    context.stroke();
    context.globalAlpha = 1;
    context.strokeStyle = '#ffd23f';
    context.lineWidth = 22;
    context.lineCap = 'round';
    context.beginPath();
    context.moveTo(path.start.x, path.start.y + 18);
    context.bezierCurveTo(path.controls[0].x, path.controls[0].y, path.controls[1].x, path.controls[1].y, path.end.x, path.end.y - 18);
    context.stroke();
    context.strokeStyle = palette.ink;
    context.globalAlpha = 0.72;
    context.lineWidth = 5;
    context.beginPath();
    context.moveTo(path.start.x - 34, path.start.y + 18);
    context.bezierCurveTo(path.controls[0].x - 34, path.controls[0].y, path.controls[1].x - 34, path.controls[1].y, path.end.x - 34, path.end.y - 18);
    context.stroke();
    context.restore();
}

function drawBrandLockup(context, safe, palette, label = 'NGE BADMINTON YUK!') {
    context.fillStyle = '#ffd23f';
    context.fillRect(safe.x, safe.y + 8, 72, 13);
    context.fillStyle = palette.ink;
    context.font = '800 25px "Plus Jakarta Sans", sans-serif';
    context.fillText(label, safe.x, safe.y + 62);
}

export function drawSeedMaster(context, canvas, connection = {}) {
    const width = POST_WIDTH * 3;
    canvas.width = width;
    canvas.height = POST_HEIGHT;
    context.fillStyle = PALETTES['off-white'].background;
    context.fillRect(0, 0, width, POST_HEIGHT);
    context.fillStyle = '#2455f5';
    context.fillRect(POST_WIDTH, 0, POST_WIDTH, POST_HEIGHT);
    context.fillStyle = '#102656';
    context.fillRect(POST_WIDTH * 2 + 1010, 0, 70, POST_HEIGHT);
    drawConnectionGeometry(context, width, connection, 'kinetic', PALETTES['off-white']);

    SEED_COPY.forEach((copy, panel) => {
        const safe = panelSafeArea(panel);
        const palette = panel === 1 ? PALETTES['royal-blue'] : PALETTES['off-white'];
        drawBrandLockup(context, safe, palette, panel === 1 ? 'ROW 0 · EST. 2026' : 'NGE BADMINTON YUK!');
        context.fillStyle = palette.ink;
        context.font = `${panel === 1 ? 900 : 800} ${panel === 1 ? 94 : 84}px "Plus Jakarta Sans", sans-serif`;
        wrapText(context, copy, safe.x, 630, safe.width, panel === 1 ? 104 : 96, 3);
        context.fillStyle = palette.muted;
        context.font = '700 25px "Plus Jakarta Sans", sans-serif';
        context.fillText(panel === 0 ? 'CARI LAWAN · CARI KAWAN' : panel === 1 ? 'PLAY · CONNECT · REPEAT' : 'SATU LAPANGAN, BANYAK CERITA', safe.x, 1120);
    });
    context.fillStyle = '#ffd23f';
    context.beginPath();
    context.arc(POST_WIDTH * 2 + 700, 250, 58, 0, Math.PI * 2);
    context.fill();
}

function drawMaster(context, canvas, design, image, settings) {
    const width = canvas.width;
    const palette = PALETTES[design.connection?.background] ?? PALETTES['off-white'];
    context.fillStyle = palette.background;
    context.fillRect(0, 0, width, POST_HEIGHT);
    if (design.postCount === 3) {
        context.fillStyle = palette.contrast;
        context.globalAlpha = 0.14;
        context.fillRect(POST_WIDTH, 0, POST_WIDTH, POST_HEIGHT);
        context.globalAlpha = 1;
    }
    const heroPanel = resolveHeroPanel(design.postCount, design.variant);
    const ctaPanel = heroPanel === design.postCount - 1 && design.postCount > 1 ? design.postCount - 2 : design.postCount - 1;
    if (image) drawPhoto(context, image, resolvePhotoBox(design.postCount, design.variant), settings);
    drawConnectionGeometry(context, width, design.connection, design.variant, palette);
    for (let panel = 0; panel < design.postCount; panel++) {
        const safe = panelSafeArea(panel);
        drawBrandLockup(context, safe, palette);
        if (panel === heroPanel && image) continue;
        if (panel === 0 || design.postCount === 1) {
            context.fillStyle = palette.ink;
            context.font = `900 ${design.postCount > 1 ? 82 : 88}px "Plus Jakarta Sans", sans-serif`;
            wrapText(context, design.headline, safe.x, 430, safe.width, 94, 4);
            context.fillStyle = palette.muted;
            context.font = '600 30px "Plus Jakarta Sans", sans-serif';
            wrapText(context, design.supportingText, safe.x, 840, safe.width, 43, 3);
        } else {
            context.fillStyle = palette.ink;
            context.font = '900 66px "Plus Jakarta Sans", sans-serif';
            wrapText(context, panel === design.postCount - 1 ? design.headline : design.supportingText, safe.x, 480, safe.width, 76, 4);
        }
        const details = [design.eventDate, design.eventTime, design.venue, design.price].filter(Boolean);
        context.fillStyle = palette.ink;
        context.font = '700 28px "Plus Jakarta Sans", sans-serif';
        details.slice(0, 3).forEach((detail, index) => wrapText(context, detail, safe.x, 1000 + index * 48, safe.width, 38, 1));
        if (design.cta && panel === ctaPanel) {
            context.fillStyle = '#ffd23f';
            context.beginPath();
            context.roundRect(safe.x, 1160, Math.min(330, safe.width), 82, 41);
            context.fill();
            context.fillStyle = '#102656';
            context.font = '800 25px "Plus Jakarta Sans", sans-serif';
            wrapText(context, design.cta.toUpperCase(), safe.x + 36, 1212, Math.min(258, safe.width - 72), 30, 1);
        }
    }
}

function renderSeedCanvases(documentObject) {
    documentObject.querySelectorAll('[data-feed-seed-canvas]').forEach((canvas) => {
        const connection = JSON.parse(canvas.dataset.connection || '{}');
        if (canvas.dataset.feedSeedGrid !== undefined) {
            const master = documentObject.createElement('canvas');
            drawSeedMaster(master.getContext('2d'), master, connection);
            canvas.width = POST_WIDTH * 3;
            canvas.height = POST_WIDTH;
            canvas.getContext('2d').drawImage(master, 0, 135, POST_WIDTH * 3, POST_WIDTH, 0, 0, POST_WIDTH * 3, POST_WIDTH);
            return;
        }
        drawSeedMaster(canvas.getContext('2d'), canvas, connection);
    });
}

function canvasBlob(canvas) {
    return new Promise((resolve) => canvas.toBlob(resolve, 'image/png', 0.96));
}

function download(windowObject, documentObject, blob, name) {
    const url = windowObject.URL.createObjectURL(blob);
    const link = documentObject.createElement('a');
    link.href = url;
    link.download = name;
    documentObject.body.append(link);
    link.click();
    link.remove();
    windowObject.setTimeout(() => windowObject.URL.revokeObjectURL(url), 30000);
}

export function installFeedStudio(windowObject, documentObject) {
    renderSeedCanvases(documentObject);
    const root = documentObject.querySelector('[data-feed-editor]');
    if (!root) return;
    const design = JSON.parse(root.dataset.design);
    const canvas = root.querySelector('[data-feed-canvas]');
    const context = canvas.getContext('2d');
    const status = root.querySelector('[data-feed-status]');
    const settings = { zoom: Number(design.settings?.zoom || 1), x: Number(design.settings?.x || 0), y: Number(design.settings?.y || 0) };
    let image = null;
    let variantIndex = Math.max(0, VARIANTS.indexOf(design.variant));
    const render = () => {
        design.variant = VARIANTS[variantIndex];
        drawMaster(context, canvas, design, image, settings);
        const grid = root.querySelector('[data-feed-grid]');
        grid.querySelectorAll('[data-feed-new]').forEach((item) => item.remove());
        for (let slice = design.postCount - 1; slice >= 0; slice--) {
            const preview = documentObject.createElement('canvas');
            preview.width = POST_WIDTH;
            preview.height = POST_WIDTH;
            preview.dataset.feedNew = '';
            preview.getContext('2d').drawImage(canvas, slice * POST_WIDTH, 135, POST_WIDTH, POST_WIDTH, 0, 0, POST_WIDTH, POST_WIDTH);
            grid.insertBefore(preview, grid.firstChild);
        }
        root.querySelector('[data-feed-order]').innerHTML = Array.from({ length: design.postCount }, (_, index) => `<li><b>${String(index + 1).padStart(2, '0')}</b> ${index === 0 ? 'Upload pertama' : index === design.postCount - 1 ? 'Upload terakhir' : 'Upload kedua'} · potongan ${design.postCount - index}</li>`).join('');
        status.textContent = 'Preview siap.';
    };
    root.querySelectorAll('[data-feed-setting]').forEach((input) => input.addEventListener('input', () => {
        settings[input.dataset.feedSetting] = Number(input.value);
        render();
    }));
    root.querySelector('[data-feed-regenerate]').addEventListener('click', () => {
        variantIndex = (variantIndex + 1) % VARIANTS.length;
        render();
        status.textContent = `Komposisi ${design.variant} dipilih.`;
    });
    root.querySelector('[data-feed-export]').addEventListener('click', async () => {
        status.textContent = 'Menyiapkan file export…';
        const assets = [];
        for (let uploadIndex = 0; uploadIndex < design.postCount; uploadIndex++) {
            const sliceIndex = design.postCount - uploadIndex - 1;
            const slice = documentObject.createElement('canvas');
            slice.width = POST_WIDTH;
            slice.height = POST_HEIGHT;
            slice.getContext('2d').drawImage(canvas, sliceIndex * POST_WIDTH, 0, POST_WIDTH, POST_HEIGHT, 0, 0, POST_WIDTH, POST_HEIGHT);
            const blob = await canvasBlob(slice);
            assets.push(blob);
            download(windowObject, documentObject, blob, `${String(uploadIndex + 1).padStart(2, '0')}-upload-${uploadIndex === 0 ? 'first' : uploadIndex === design.postCount - 1 ? 'last' : 'second'}.png`);
        }
        const thumbnail = documentObject.createElement('canvas');
        thumbnail.width = 432;
        thumbnail.height = 540;
        thumbnail.getContext('2d').drawImage(canvas, 0, 0, POST_WIDTH, POST_HEIGHT, 0, 0, 432, 540);
        const data = new FormData();
        assets.forEach((blob, index) => data.append('assets[]', blob, `${index + 1}.png`));
        data.append('thumbnail', await canvasBlob(thumbnail), 'thumbnail.png');
        data.append('layout_variant', design.variant);
        data.append('zoom', settings.zoom);
        data.append('position_x', settings.x);
        data.append('position_y', settings.y);
        try {
            const response = await windowObject.fetch(root.dataset.assetsUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': root.dataset.csrf, Accept: 'application/json' }, body: data });
            if (!response.ok) throw new Error();
            status.textContent = 'File diunduh dan tersimpan di Feed History.';
        } catch {
            status.textContent = 'File sudah diunduh, tetapi riwayat export belum tersimpan.';
        }
    });
    loadImage(windowObject, design.photoUrl).then((loaded) => { image = loaded; render(); }).catch(() => { render(); status.textContent = 'Foto tidak dapat dimuat. Layout teks tetap tersedia.'; });
    documentObject.fonts?.ready.then(render);
}
