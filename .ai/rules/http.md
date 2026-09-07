---
paths:
  - 'app/Http/**'
---

# Http

## Member notifications are mandatory per device session
Members must activate standard Web Push on the current browser/session before using member features, including authenticated public schedule pages. A subscription on another device is insufficient. Preserve setup, subscription endpoints and logout access; administrators and guest schedule viewing are exempt. Browser permission remains user-controlled; no skip or in-app opt-out. Keep the legacy FCM one-time reset behavior.
