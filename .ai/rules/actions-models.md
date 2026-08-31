---
paths:
  - 'app/{Actions,Models}/**'
---

# Actions Models

## Membership credits use a ledger
Derive remaining play credits from membership_transactions. Attendance consumes the oldest active credit only when venue, court, price, and validity dates match the play session; never store or edit a standalone balance.

## Shuttlecock stock uses movements
Derive shuttlecock stock from signed stock_movements. Purchases and adjustments add pieces, usage subtracts pieces, and no operation may make the balance negative.
