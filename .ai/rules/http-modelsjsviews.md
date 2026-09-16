---
paths:
  - 'app/{Http,Models}/**, resources/{js,views}/**, public/manifest.webmanifest'
---

# Http Modelsjsviews

## Installed PWA blocks member browser sessions
Standalone PWA launches must record pwa_installed_at for the authenticated member. Regular browser pages block known installations; Chromium should verify with getInstalledRelatedApps and clear stale local markers after uninstall, while iOS falls back to the account marker because Home Screen storage is isolated. Notification setup and the recording endpoint remain reachable before push activation.
