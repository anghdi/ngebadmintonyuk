---
paths:
  - 'app/Http/**, resources/views/auth/**'
---

# Auth

## Member login persists and notification setup remains mandatory
Member authentication must use remember login automatically until explicit logout, including immediately after account registration. New members go directly to notification setup. Every browser/device still must establish its own active Web Push subscription; show explicit Android Chrome and iOS/iPadOS Home Screen activation steps and provide no skip action. Administrators keep the optional remember-me behavior.
