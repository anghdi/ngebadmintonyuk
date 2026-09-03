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
