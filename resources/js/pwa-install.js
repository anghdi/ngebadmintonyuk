export function isIosDevice(userAgent, platform, maxTouchPoints) {
    return /iphone|ipad|ipod/i.test(userAgent)
        || (platform === 'MacIntel' && maxTouchPoints > 1);
}

export function resolvePwaInstallMode({ standalone, ios, promptAvailable }) {
    if (standalone) {
        return 'installed';
    }

    if (promptAvailable) {
        return 'prompt';
    }

    return ios ? 'ios-guide' : 'hidden';
}

const installedStorageKey = 'ngebadmintonyuk-pwa-installed';
const reportedStorageKey = 'ngebadmintonyuk-pwa-reported';

export function isStandalonePwa(windowObject) {
    return windowObject.matchMedia?.('(display-mode: standalone)').matches === true
        || windowObject.navigator?.standalone === true;
}

export function markPwaInstalled(windowObject) {
    try {
        windowObject.localStorage?.setItem(installedStorageKey, 'true');
    } catch {}
}

export function forgetPwaInstalled(windowObject) {
    try {
        windowObject.localStorage?.removeItem(installedStorageKey);
        windowObject.localStorage?.removeItem(reportedStorageKey);
    } catch {}
}

export function hasInstalledPwa(windowObject) {
    if (isStandalonePwa(windowObject)) {
        return true;
    }

    try {
        return windowObject.localStorage?.getItem(installedStorageKey) === 'true';
    } catch {
        return false;
    }
}

export function installPwaAppGate(windowObject, documentObject) {
    const gate = documentObject.querySelector('[data-pwa-app-gate]');

    if (!gate) {
        return;
    }

    const openGate = () => {
        if (gate.open) {
            return true;
        }

        if (typeof gate.showModal === 'function') {
            gate.showModal();
        } else {
            gate.setAttribute('open', '');
        }

        return true;
    };

    const reportStandaloneInstall = () => {
        let reported = false;

        try {
            reported = windowObject.localStorage?.getItem(reportedStorageKey) === 'true';
        } catch {}

        if (reported || !gate.dataset.pwaInstallationUrl) {
            return;
        }

        const csrf = documentObject.querySelector('meta[name="csrf-token"]')?.content;

        windowObject.fetch(gate.dataset.pwaInstallationUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        }).then((response) => {
            if (response.ok) {
                windowObject.localStorage?.setItem(reportedStorageKey, 'true');
            }
        }).catch(() => {});
    };

    const enforce = async () => {
        if (isStandalonePwa(windowObject)) {
            markPwaInstalled(windowObject);
            reportStandaloneInstall();
            gate.close?.();

            return false;
        }

        if (typeof windowObject.navigator?.getInstalledRelatedApps === 'function') {
            try {
                const relatedApps = await windowObject.navigator.getInstalledRelatedApps();
                const installed = relatedApps.some((app) => app.platform === 'webapp');

                if (installed) {
                    markPwaInstalled(windowObject);

                    return openGate();
                }

                forgetPwaInstalled(windowObject);

                return false;
            } catch {}
        }

        if (gate.dataset.pwaKnownInstalled === 'true' || hasInstalledPwa(windowObject)) {
            return openGate();
        }

        return false;
    };

    gate.addEventListener('cancel', (event) => event.preventDefault());
    windowObject.addEventListener('appinstalled', () => {
        markPwaInstalled(windowObject);
        openGate();
    });

    return { ready: enforce() };
}
