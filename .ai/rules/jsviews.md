---
paths:
  - 'resources/{js,views}/**'
---

# Jsviews

## Scoreboard remains device-local
Papan skor badminton adalah alat umum tanpa model atau tabel database. Pertandingan mengikuti rally point 21, menang selisih 2, batas 30, best of 3; state hanya disimpan pada localStorage perangkat.

## Keep mobile navigation actions reachable
Use dynamic viewport height for the sidebar, keep its navigation independently scrollable, and place install/logout actions in a non-scrolling footer. PWA installation uses beforeinstallprompt where supported, an Add to Home Screen guide on iOS, and hides install UI in standalone mode.
