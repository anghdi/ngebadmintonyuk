---
paths:
  - 'app/Http/**, resources/views/profile/**'
---

# Profile

## Peer profiles expose only safe community fields
Authenticated members with device notification setup may tap session avatars to view a member's name, nickname, playing level and photo. No email, phone, birth date, quota or private storage path in peer views. Avatars remain on private local storage and are served through authenticated routes; admins are not peer profile targets and guests have no member profile.
