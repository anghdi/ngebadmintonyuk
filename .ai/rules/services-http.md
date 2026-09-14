---
paths:
  - 'app/{Services,Http}/**'
---

# Services Http

## Member reports separate period activity from current private profile data
Admin-only member reports and PDF share all filters and totals across pagination; guest statistics stay separate. Member attendance uses Attendance once per session, excluding cancelled sessions. Cash/transfer totals use linked income details and income date; approved top-ups use reviewed_at, never added twice to iuran. Usable quota is the current active, valid membership ledger balance; age is current. Birthday previews cover today through seven days ahead across year boundaries, use the actual Feb 29 date in leap years, and never send notifications automatically.
