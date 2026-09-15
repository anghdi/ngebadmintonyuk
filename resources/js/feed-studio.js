const POST_WIDTH = 1080;
const POST_HEIGHT = 1350;

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
    if (lineNumber < maxLines) context.fillText(line, x, y + lineNumber * lineHeight);
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
    context.roundRect(box.x, box.y, box.width, box.height, 26);
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
}

function drawMaster(context, canvas, design, image, settings) {
    const width = canvas.width;
    const palette = design.connection?.background === 'navy'
        ? { background: '#102656', ink: '#ffffff', muted: '#dce4f4' }
        : design.connection?.background === 'royal-blue'
            ? { background: '#2455f5', ink: '#ffffff', muted: '#dfe7ff' }
            : { background: '#f7f4ec', ink: '#102656', muted: '#53617a' };
    context.fillStyle = palette.background;
    context.fillRect(0, 0, width, POST_HEIGHT);

    context.strokeStyle = palette.ink;
    context.globalAlpha = 0.16;
    context.lineWidth = 7;
    context.strokeRect(90, 90, width - 180, POST_HEIGHT - 180);
    context.beginPath();
    context.moveTo(width * 0.12, 0);
    context.lineTo(width * 0.46, POST_HEIGHT);
    context.moveTo(width * 0.72, 0);
    context.lineTo(width * 0.9, POST_HEIGHT);
    context.stroke();
    context.globalAlpha = 1;

    const photoLeft = design.variant === 'sideline' ? width * 0.52 : width * 0.43;
    drawPhoto(context, image, { x: photoLeft, y: 120, width: width - photoLeft - 90, height: 1110 }, settings);

    context.fillStyle = '#ffd23f';
    context.fillRect(72, 112, 230, 18);
    context.beginPath();
    context.arc(width * 0.78, 208, 34, 0, Math.PI * 2);
    context.fill();

    context.fillStyle = palette.ink;
    context.font = '800 42px "Plus Jakarta Sans", sans-serif';
    context.fillText('NGE BADMINTON YUK!', 72, 200);
    context.font = `800 ${design.postCount > 1 ? 104 : 92}px "Plus Jakarta Sans", sans-serif`;
    wrapText(context, design.headline, 72, 440, Math.min(880, width * 0.39), 112, 4);
    context.fillStyle = palette.muted;
    context.font = '600 34px "Plus Jakarta Sans", sans-serif';
    wrapText(context, design.supportingText, 76, 820, Math.min(820, width * 0.36), 48, 3);

    const details = [design.eventDate, design.eventTime, design.venue, design.price].filter(Boolean).join('  ·  ');
    context.fillStyle = palette.ink;
    context.font = '700 30px "Plus Jakarta Sans", sans-serif';
    wrapText(context, details, 76, 1040, Math.min(850, width * 0.38), 42, 2);
    if (design.cta) {
        context.fillStyle = '#ffd23f';
        context.roundRect(72, 1155, 310, 82, 41);
        context.fill();
        context.fillStyle = '#102656';
        context.font = '800 28px "Plus Jakarta Sans", sans-serif';
        context.fillText(design.cta.toUpperCase(), 112, 1208);
    }

    context.strokeStyle = '#ffd23f';
    context.lineWidth = 15;
    context.beginPath();
    context.moveTo(width * 0.18, POST_HEIGHT - 34);
    context.bezierCurveTo(width * 0.38, POST_HEIGHT - 180, width * 0.66, POST_HEIGHT + 40, width * 0.92, POST_HEIGHT - 110);
    context.stroke();

    for (let index = 1; index < design.postCount; index++) {
        context.strokeStyle = 'rgba(255,255,255,.34)';
        context.lineWidth = 2;
        context.setLineDash([14, 14]);
        context.beginPath();
        context.moveTo(index * POST_WIDTH, 0);
        context.lineTo(index * POST_WIDTH, POST_HEIGHT);
        context.stroke();
        context.setLineDash([]);
    }
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
    const root = documentObject.querySelector('[data-feed-editor]');
    if (!root) return;
    const design = JSON.parse(root.dataset.design);
    const canvas = root.querySelector('[data-feed-canvas]');
    const context = canvas.getContext('2d');
    const status = root.querySelector('[data-feed-status]');
    const settings = { zoom: Number(design.settings?.zoom || 1), x: Number(design.settings?.x || 0), y: Number(design.settings?.y || 0) };
    let image = null;
    let variantIndex = ['editorial', 'kinetic', 'sideline'].indexOf(design.variant);
    const render = () => {
        design.variant = ['editorial', 'kinetic', 'sideline'][Math.max(0, variantIndex)];
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
        variantIndex = (variantIndex + 1) % 3;
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
