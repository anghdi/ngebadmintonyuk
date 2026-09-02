---
paths:
  - 'app/{Actions,Models,Services,Http}/**'
---

# Actions Models Services Http

## Push notifications are manual and synchronous
FCM push notifications are initiated manually by an administrator and sent synchronously without jobs or queues. Target Firebase Installation IDs stored per player device; service-account credentials are referenced only through config/services.php and FIREBASE_CREDENTIALS, never committed.
