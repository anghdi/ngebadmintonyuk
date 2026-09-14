---
paths:
  - 'app/Actions/**'
---

# Actions

## Profile uploads are private resized WebP avatars
New avatar uploads are re-encoded server-side with GD as WebP quality 80, longest side at most 512px without upscaling; keep aspect ratio, transparency and JPEG EXIF orientation but strip source metadata. Store UUID .webp names on private local disk, preserve old avatars until profile persistence succeeds and clean failed replacements. Existing stored avatars remain readable and are not bulk-converted.
