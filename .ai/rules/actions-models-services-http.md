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

## Top up cash and session quota settle without duplicate income
Approved top ups atomically grant exactly four ledger credits and create one linked Top Up Kuota income using the submitted amount and approval date. Old approvals are not backfilled automatically. Protect linked income from manual edits/deletion. Membership session payment creates no income; consume one credit only on present attendance, and restore it when corrected to no_show or listed. No-show never consumes credits. This supersedes the older rule separating registration attendance from the membership ledger; cash/transfer attendance does not consume quota. Keep legacy standalone membership attendance compatible.

## No refund workflow
The user confirmed that the community has no refunds. Existing paid-to-unpaid operations are corrections of payment records, not money-return transactions. Do not introduce a refund workflow or describe refunds as an existing operational process.
