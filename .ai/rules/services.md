---
paths:
  - app/Services/RotationScheduleService.php
---

# Services

## Prioritize whole-match encounter diversity
Rotation generation must track every pair of players sharing a court, regardless of whether they are partners or opponents. Choose players and court arrangements by minimizing each pair's maximum encounter count first, then repeated partner/opponent frequencies, while keeping total games per player within one.
