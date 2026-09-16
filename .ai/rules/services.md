---
paths:
  - app/Services/RotationScheduleService.php
  - app/Services/FeedLayoutStateService.php
---

# Services

## Prioritize whole-match encounter diversity
Rotation generation must track every pair of players sharing a court, regardless of whether they are partners or opponents. Choose players and court arrangements by minimizing each pair's maximum encounter count first, then repeated partner/opponent frequencies, while keeping total games per player within one.

## Synchronize connected rows after mutations
When a Feed Studio row changes or is deleted, recompute following grid positions and connection_state in row order. Invalidate a following row's exported assets only when its visual connection state actually changes; ordinary copy/photo edits must not erase unrelated later exports.
