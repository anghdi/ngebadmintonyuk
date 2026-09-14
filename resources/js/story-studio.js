import { drawFrameBackground, drawFrameForeground } from './story-frames.js';

export const storySize = { width: 1080, height: 1920 };
export const photoBox = { x: 48, y: 260, width: 984, height: 1420 };

export function getStoryPhotoPlacement(width, height, zoom = 1, x = 0, y = 0) {
    const scale = Math.max(photoBox.width / width, photoBox.height / height) * Math.max(1, Math.min(3, zoom));
    const drawnWidth = width * scale;
    const drawnHeight = height * scale;

    return {
        x: photoBox.x - (drawnWidth - photoBox.width) * (1 + Math.max(-1, Math.min(1, x))) / 2,
        y: photoBox.y - (drawnHeight - photoBox.height) * (1 + Math.max(-1, Math.min(1, y))) / 2,
        width: drawnWidth,
        height: drawnHeight,
    };
}

export function drawStory(context, photo, logo, settings) {
    const yellow = settings.theme === 'yellow';
    const minimal = settings.theme === 'minimal';
    const background = minimal ? '#f2f0e8' : yellow ? '#ffd23f' : '#102656';
    const ink = yellow || minimal ? '#102656' : '#ffffff';
    context.fillStyle = background;
    context.fillRect(0, 0, storySize.width, storySize.height);
    drawFrameBackground(context, settings.theme);

    if (logo) {
        const height = 225;
        const width = height * logo.naturalWidth / logo.naturalHeight;
        context.drawImage(logo, 28, 0, width, height);
    } else {
        context.fillStyle = ink;
        context.font = '800 45px "Plus Jakarta Sans", sans-serif';
        context.fillText('NgeBadmintonYuk', 60, 150);
    }

    context.save();
    context.beginPath();
    context.roundRect(photoBox.x, photoBox.y, photoBox.width, photoBox.height, 22);
    context.clip();
    context.fillStyle = '#263b61';
    context.fillRect(photoBox.x, photoBox.y, photoBox.width, photoBox.height);
    if (photo) {
        const placement = getStoryPhotoPlacement(photo.naturalWidth, photo.naturalHeight, settings.zoom, settings.x, settings.y);
        context.drawImage(photo, placement.x, placement.y, placement.width, placement.height);
    }
    context.restore();

    drawFrameForeground(context, settings.theme);
}

function loadImage(windowObject, source) {
    return new Promise((resolve, reject) => {
        const image = new windowObject.Image();
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Foto tidak dapat dibaca. Gunakan JPG, PNG, atau WebP.'));
        image.src = source;
    });
}

function downloadStory(windowObject, documentObject, file) {
    const url = windowObject.URL.createObjectURL(file);
    const link = documentObject.createElement('a');
    link.href = url;
    link.download = file.name;
    documentObject.body.append(link);
    link.click();
    link.remove();
    windowObject.setTimeout(() => windowObject.URL.revokeObjectURL(url), 30_000);
}

function installPhotoDrag(canvas, find, settings, getPhoto, render) {
    let drag = null;
    canvas.addEventListener('pointerdown', (event) => {
        const photo = getPhoto();
        if (!photo) {
            return;
        }
        const placement = getStoryPhotoPlacement(photo.naturalWidth, photo.naturalHeight, settings.zoom);
        drag = { pointerId: event.pointerId, x: event.clientX, y: event.clientY, startX: settings.x, startY: settings.y, placement };
        canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!drag || event.pointerId !== drag.pointerId) {
            return;
        }
        const ratio = storySize.width / canvas.getBoundingClientRect().width;
        const update = (name, delta, overflow, initial) => {
            settings[name] = overflow > 0 ? Math.max(-1, Math.min(1, initial - 2 * delta * ratio / overflow)) : 0;
            find(name).value = settings[name];
        };
        update('x', event.clientX - drag.x, drag.placement.width - photoBox.width, drag.startX);
        update('y', event.clientY - drag.y, drag.placement.height - photoBox.height, drag.startY);
        render();
    });
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach((event) => canvas.addEventListener(event, () => { drag = null; }));
}

export function installStoryStudio(windowObject, documentObject) {
    const root = documentObject.querySelector('[data-story-studio]');
    if (!root) {
        return;
    }
    const find = (name) => root.querySelector(`[data-story-${name}]`);
    const canvas = find('canvas');
    const context = canvas.getContext('2d');
    const status = find('status');
    const settings = { theme: 'blue', zoom: 1, x: 0, y: 0 };
    let photo = null;
    let logo = null;
    let hasUserPhoto = false;
    let loadSequence = 0;
    let exportedFile = null;
    let renderSequence = 0;
    const render = () => {
        drawStory(context, photo, logo, settings);
        root.querySelectorAll('[data-story-thumbnail]').forEach((thumbnail) => {
            const thumbnailContext = thumbnail.getContext('2d');
            thumbnailContext.save();
            thumbnailContext.scale(thumbnail.width / storySize.width, thumbnail.height / storySize.height);
            drawStory(thumbnailContext, photo, logo, { ...settings, theme: thumbnail.dataset.storyThumbnail });
            thumbnailContext.restore();
        });
        const sequence = ++renderSequence;
        exportedFile = null;
        find('download').disabled = true;
        find('share').disabled = true;
        if (hasUserPhoto) {
            canvas.toBlob((blob) => {
                if (!blob || sequence !== renderSequence) {
                    return;
                }
                exportedFile = new windowObject.File([blob], 'ngebadmintonyuk-story.png', { type: 'image/png' });
                find('download').disabled = false;
                find('share').hidden = !windowObject.navigator.canShare?.({ files: [exportedFile] });
                find('share').disabled = false;
            }, 'image/png');
        }
    };
    const resetPosition = () => {
        ['zoom', 'x', 'y'].forEach((name) => {
            settings[name] = name === 'zoom' ? 1 : 0;
            find(name).value = settings[name];
        });
    };
    const selectPhoto = async (event) => {
        const file = event.target.files?.[0];
        event.target.value = '';
        if (!file) {
            return;
        }
        if (!file.type.startsWith('image/') || file.size > 20 * 1024 * 1024) {
            status.textContent = 'Pilih foto dengan ukuran maksimal 20 MB.';
            return;
        }
        const sequence = ++loadSequence;
        const url = windowObject.URL.createObjectURL(file);
        status.textContent = 'Menyiapkan foto…';
        try {
            const loaded = await loadImage(windowObject, url);
            if (sequence !== loadSequence) {
                return;
            }
            photo = loaded;
            hasUserPhoto = true;
            canvas.dataset.draggable = '';
            resetPosition();
            find('adjust').disabled = false;
            find('preview-note').textContent = 'Geser foto langsung di pratinjau untuk mengatur posisi.';
            status.textContent = 'Foto siap. Atur frame, lalu unduh.';
            render();
        } catch (error) {
            if (sequence === loadSequence) {
                status.textContent = error.message;
            }
        } finally {
            windowObject.URL.revokeObjectURL(url);
        }
    };
    ['camera', 'file'].forEach((name) => find(`${name}-input`).addEventListener('change', selectPhoto));
    installPhotoDrag(canvas, find, settings, () => hasUserPhoto ? photo : null, render);
    find('camera').addEventListener('click', () => find('camera-input').click());
    find('upload').addEventListener('click', () => find('file-input').click());
    root.querySelectorAll('[name="story-theme"]').forEach((input) => input.addEventListener('change', () => {
        settings.theme = input.value;
        render();
    }));
    ['zoom', 'x', 'y'].forEach((name) => find(name).addEventListener('input', () => {
        settings[name] = Number(find(name).value);
        render();
    }));
    find('reset').addEventListener('click', () => { resetPosition(); render(); });
    find('download').addEventListener('click', () => {
        if (exportedFile) {
            downloadStory(windowObject, documentObject, exportedFile);
            status.textContent = 'Story diunduh. Cek folder unduhan perangkatmu.';
        }
    });
    find('share').addEventListener('click', async () => {
        if (!exportedFile) {
            return;
        }
        try {
            await windowObject.navigator.share({ files: [exportedFile], title: 'NgeBadmintonYuk' });
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Belum bisa dibagikan. Gunakan tombol Unduh story.';
            }
        }
    });
    render();
    loadImage(windowObject, root.dataset.logo).then((image) => { logo = image; render(); }).catch(() => {});
    loadImage(windowObject, root.dataset.demo).then((image) => {
        if (!hasUserPhoto) { photo = image; render(); }
    }).catch(() => {});
    documentObject.fonts?.ready.then(render);
}
