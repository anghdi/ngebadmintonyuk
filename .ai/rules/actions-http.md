---
paths:
  - 'app/{Actions,Http}/**'
---

# Actions Http

## Players may cancel eligible registrations
A signed-in non-admin player may cancel only their own registration for a scheduled future play session. Self-cancellation uses the existing deletion invariant: only unpaid registrations still in listed status may be removed; paid or attendance-processed registrations remain auditable.

## Legacy FCM requires one-time reinstall
Member accounts with an FCM push subscription must have those legacy rows deleted and be logged out once with reinstall instructions. New push subscription registration accepts Web Push only; after reinstall, the member logs in again and follows the normal Web Push permission flow. Administrators are exempt.
