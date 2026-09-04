---
paths:
  - 'app/Http/Controllers/*PlaySessionController.php'
  - 'app/Http/Controllers/**'
---

# Controllers

## Session indexes require month selection
Public and administrator play-session indexes must not render an unfiltered long list. Require a YYYY-MM month selection before showing sessions; public month choices include only upcoming scheduled sessions, while administrator choices include historical and future sessions.

## Member dashboard shows joined sessions only
The member dashboard upcoming-session summary must include only future scheduled sessions where that member has a listed registration. Keep the public schedule catalogue complete so members can discover and join new sessions.
