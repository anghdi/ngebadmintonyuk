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

## Session lists are scoped and member-backed
Each play session owns an independent registration list. Admin-created or edited entries may link to a member; when linked, snapshot the member name and normalized phone. Only unpaid entries still in listed status may be deleted so attendance and payment history remain auditable.

## Play-session capacity is enforced atomically
Each play session has an admin-managed max_players value, default 12. Public and admin registration actions must lock the play-session row and reject additions when registrations count reaches capacity. Admin may not lower capacity below the existing registration count.

## Top up bootstraps a community package
Member tanpa paket aktif tetap dapat mengajukan top up. Sistem membuat atau memakai Paket Komunitas tanpa menulis saldo awal; hanya persetujuan admin yang menambahkan tepat 4 kuota. Paket Komunitas dapat dipakai pada semua sesi komunitas.

## Session registration requires an account
This supersedes guest registration and phone-backed sanctions. Every new public or admin-created play-session registration must link to a non-admin player account; WhatsApp is optional. Enforce per-session uniqueness and no-show blocking by user_id, while retaining legacy rows without an account for historical display.

## Play sessions include an ordered waiting list and linked income
Each session has max_players plus admin-managed max_waiting_players. Registrations are ordered by id: entries beyond max_players are waiting and promote automatically when an earlier unpaid registration is removed; reject only when both capacities are full. Only confirmed players may be marked paid. Admin payment confirmation must atomically create one linked Iuran Lapangan income, and reverting payment removes that linked income.
