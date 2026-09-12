const storageKey = 'ngebadmintonyuk-app-version';
const minimumCheckInterval = 60_000;

function readStoredVersion(windowObject) {
    try {
        return windowObject.localStorage.getItem(storageKey);
    } catch {
        return null;
    }
}

function storeVersion(windowObject, version) {
    try {
        windowObject.localStorage.setItem(storageKey, version);
    } catch {
        // The update flow still works when browser storage is unavailable.
    }
}

export function installAppVersionUpdates(windowObject, documentObject) {
    const banner = documentObject.querySelector('[data-app-update]');

    if (!banner) {
        return null;
    }

    const versionUrl = banner.dataset.versionUrl;
    const reloadButton = banner.querySelector('[data-app-update-reload]');
    const laterButton = banner.querySelector('[data-app-update-later]');
    let availableVersion = null;
    let dismissedVersion = null;
    let lastCheckedAt = 0;

    const checkVersion = async (force = false) => {
        const checkedAt = Date.now();

        if (!force && checkedAt - lastCheckedAt < minimumCheckInterval) {
            return;
        }

        lastCheckedAt = checkedAt;

        try {
            const response = await windowObject.fetch(versionUrl, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();

            if (typeof payload.version !== 'string' || payload.version.length === 0) {
                return;
            }

            const currentVersion = readStoredVersion(windowObject);

            if (!currentVersion) {
                storeVersion(windowObject, payload.version);

                return;
            }

            if (currentVersion !== payload.version && dismissedVersion !== payload.version) {
                availableVersion = payload.version;
                banner.hidden = false;
            }
        } catch {
            // A temporary network failure should not interrupt the current page.
        }
    };

    reloadButton?.addEventListener('click', () => {
        if (availableVersion) {
            storeVersion(windowObject, availableVersion);
        }

        windowObject.location.reload();
    });

    laterButton?.addEventListener('click', () => {
        dismissedVersion = availableVersion;
        banner.hidden = true;
    });

    documentObject.addEventListener('visibilitychange', () => {
        if (documentObject.visibilityState === 'visible') {
            void checkVersion();
        }
    });
    windowObject.addEventListener('focus', () => void checkVersion());
    void checkVersion(true);

    return { checkVersion };
}
