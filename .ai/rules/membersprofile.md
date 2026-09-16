---
paths:
  - 'app/{Http,Models}/**, resources/views/{members,profile}/**'
---

# Membersprofile

## Member tenure uses joined_at
Use users.joined_at as the member's community start date; created_at is only a fallback for legacy/unsaved data. New registrations set joined_at to today, existing rows are backfilled from created_at, and only admins may correct it to a non-future date. Member-facing tenure should use User::memberSince() and membershipDuration().
