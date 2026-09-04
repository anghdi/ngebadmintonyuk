---
paths:
  - 'app/{Actions,Models,Services,Http}/**'
---

# Actions Models Services Http

## Push notifications are manual and synchronous
FCM push notifications are initiated manually by an administrator and sent synchronously without jobs or queues. Target Firebase Installation IDs stored per player device; service-account credentials are referenced only through config/services.php and FIREBASE_CREDENTIALS, never committed.

## Session activity uses standard Web Push
New player-device subscriptions use standard VAPID Web Push for Android browsers and installed iOS PWAs. Joining or cancelling a play session sends a synchronous notification to all subscribed member devices; admin manual broadcasts remain available. Keep legacy FCM delivery only for existing stored FCM subscriptions.

## Session activity uses standard Web Push
This supersedes the earlier manual-only FCM rule. New player-device subscriptions use standard VAPID Web Push for Android browsers and installed iOS PWAs. Joining or cancelling a play session sends synchronously to all subscribed member devices; admin manual broadcasts remain available. Keep FCM delivery only for legacy stored FCM subscriptions.
