---
paths:
  - 'app/{Actions,Services,Http}/**'
---

# Actions Services Http

## Published session rotation follows the confirmed list
Generate the complete doubles schedule once from registrations ordered by id, limited to max_players, including guests regardless of payment or attendance. Waiting-list players are excluded. court_count supports 1–2 courts; numbered rounds are plans, not actual attendance or financial activity. Persist the roster fingerprint and optimistic version; hide stale schedules after confirmed-roster or court-count changes and require explicit regeneration. Authenticated members can view published non-cancelled rotations after session start; registration remains closed.

## Rotation generation is a draft requiring explicit admin approval
Supersedes immediate publication: generate stores a draft with published_at null. Admin reviews the full round table, then explicitly publishes the matching version under a session lock after checking the live main-roster fingerprint. Member routes and views must never expose draft rounds; regeneration withdraws the old schedule and requires approval again. Preserve registration id ordering when loading avatar relationships (use loadMissing for nested relations).
