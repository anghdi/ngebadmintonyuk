---
paths:
  - 'app/{Actions,Models,Http}/**'
---

# Actions Models Http

## Top up uses verified bank transfer and credit ledger
Top up requests are fixed at Rp110.000 and require a private proof upload to BCA 6690685688 or BRI 036801013857535, both a.n. Angga Hadi Permana. Only admin approval may add quota, and it must create a membership_transactions credit entry rather than editing a balance.

## Top up uses admin package settings and credit ledger
Admin controls the current top-up amount and credit count. Each submission snapshots both values, requires a private proof upload to the configured BCA/BRI accounts, and only admin approval may create the membership_transactions credit entry; never edit a standalone balance.

## Admin settings supersede fixed top up amount
This supersedes the earlier Rp110.000 fixed-amount rule. Rp110.000 and 4 credits are defaults only; admin-managed top_up_settings determine the current package offered to new submissions.

## Session registration sanctions use normalized phone history
Public play-session registration is open to members and guests. Normalize Indonesian phone numbers and block new registrations after three records marked no_show; correcting an attendance status must automatically reduce the count and lift the block. Registration attendance/payment tracking stays separate from the membership credit ledger.

## Top up grants exactly four credits
This supersedes the earlier rule allowing admin-managed credit counts. Admin may change only the top-up price; every new request snapshots 4 credits and approval must atomically create one membership_transactions credit entry with quantity +4.
