---
paths:
  - 'app/{Actions,Http}/**'
---

# Actions Http

## Players may cancel eligible registrations
A signed-in non-admin player may cancel only their own registration for a scheduled future play session. Self-cancellation uses the existing deletion invariant: only unpaid registrations still in listed status may be removed; paid or attendance-processed registrations remain auditable.
