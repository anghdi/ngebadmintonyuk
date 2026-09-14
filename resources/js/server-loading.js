export function createLoadingController({ show, hide, slow, setTimer = setTimeout, clearTimer = clearTimeout }) {
    const pending = new Set();
    let showTimer;
    let slowTimer;

    const clearTimers = () => {
        clearTimer(showTimer);
        clearTimer(slowTimer);
    };

    const dismiss = () => {
        clearTimers();
        hide();
    };

    const reset = () => {
        pending.clear();
        dismiss();
    };

    const start = (message = 'Sedang memuat halaman…', { immediate = false } = {}) => {
        const token = Symbol('request');
        pending.add(token);

        if (pending.size === 1) {
            if (immediate) {
                show(message);
            } else {
                showTimer = setTimer(() => show(message), 160);
            }
            slowTimer = setTimer(slow, 10000);
        } else if (immediate) {
            clearTimer(showTimer);
            show(message);
        }

        return () => {
            pending.delete(token);
            if (pending.size === 0) {
                dismiss();
            }
        };
    };

    const run = async (operation, message) => {
        const finish = start(message);
        try {
            return await operation();
        } finally {
            finish();
        }
    };

    return { start, run, reset, dismiss };
}

export function shouldLoadLink(event, link, currentUrl) {
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey
        || link.hasAttribute('download') || link.hasAttribute('data-no-loading')
        || (link.target && link.target.toLowerCase() !== '_self')) {
        return false;
    }

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#')) {
        return false;
    }

    const current = new URL(currentUrl);
    const destination = new URL(href, current);

    return ['http:', 'https:'].includes(destination.protocol)
        && destination.origin === current.origin
        && !(destination.pathname === current.pathname && destination.search === current.search && destination.hash);
}

export function bindServerLoading(window, document, controller) {
    let submitting = false;
    let restoreSubmission = () => {};

    const reset = () => {
        submitting = false;
        restoreSubmission();
        restoreSubmission = () => {};
        controller.reset();
    };

    window.addEventListener('pageshow', reset);
    window.addEventListener('pagehide', reset);
    window.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');
        if (shouldLoadLink(event, link, window.location.href)) {
            controller.start('Sedang memuat halaman…');
        }
    });

    window.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter;
        const target = submitter?.getAttribute('formtarget') ?? form.target;
        const method = submitter?.getAttribute('formmethod') ?? form.method;

        if (event.defaultPrevented || form.hasAttribute('data-no-loading') || submitter?.hasAttribute('data-no-loading')
            || (target && target.toLowerCase() !== '_self') || method?.toLowerCase() === 'dialog') {
            return;
        }

        if (submitting) {
            event.preventDefault();
            return;
        }

        submitting = true;
        const uploading = Array.from(form.querySelectorAll?.('input[type="file"]') ?? []).some((input) => input.files?.length > 0);
        const message = uploading
            ? (form.dataset?.uploadLoadingMessage ?? 'Sedang mengunggah file…')
            : (form.dataset?.loadingMessage ?? (method?.toLowerCase() === 'get' ? 'Sedang memuat data…' : 'Sedang mengirim data…'));
        const originalLabel = submitter?.textContent;
        const originalBusy = form.getAttribute?.('aria-busy');
        const originalDisabled = submitter?.getAttribute('aria-disabled');
        form.setAttribute?.('aria-busy', 'true');
        if (submitter?.tagName === 'BUTTON') {
            submitter.textContent = uploading ? 'Mengunggah…' : 'Memproses…';
            submitter.setAttribute('aria-disabled', 'true');
        }
        restoreSubmission = () => {
            if (originalBusy == null) {
                form.removeAttribute?.('aria-busy');
            } else {
                form.setAttribute?.('aria-busy', originalBusy);
            }
            if (submitter?.tagName === 'BUTTON') {
                submitter.textContent = originalLabel;
                if (originalDisabled == null) {
                    submitter.removeAttribute('aria-disabled');
                } else {
                    submitter.setAttribute('aria-disabled', originalDisabled);
                }
            }
        };
        controller.start(message, { immediate: true });
    });

    document.querySelector('[data-loading-dismiss]')?.addEventListener('click', controller.dismiss);

    if (document.readyState !== 'complete') {
        const finish = controller.start();
        window.addEventListener('load', finish, { once: true });
    }
}

export function installServerLoading(window, document) {
    const overlay = document.querySelector('[data-server-loading]');
    if (!overlay) {
        return { run: (operation) => operation() };
    }

    const title = overlay.querySelector('[data-loading-title]');
    const description = overlay.querySelector('[data-loading-description]');
    const controller = createLoadingController({
        show(message) {
            title.textContent = message;
            description.textContent = 'Sebentar ya, permintaanmu sedang diproses.';
            overlay.hidden = false;
            document.body.classList.add('server-is-loading');
        },
        hide() {
            overlay.hidden = true;
            document.body.classList.remove('server-is-loading');
        },
        slow() {
            description.textContent = 'Proses lebih lama dari biasanya. Tunggu sebentar dan jangan kirim ulang data.';
        },
    });

    bindServerLoading(window, document, controller);

    return controller;
}
