---
paths:
  - 'app/Http/Controllers/*PlaySessionController.php'
---

# Controllers

## Session indexes require month selection
Public and administrator play-session indexes must not render an unfiltered long list. Require a YYYY-MM month selection before showing sessions; public month choices include only upcoming scheduled sessions, while administrator choices include historical and future sessions.
