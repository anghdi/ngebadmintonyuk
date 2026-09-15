# PRD --- NgeBadminton YUK! Feed Studio

## 1. Tujuan

Membuat tool internal berbasis web untuk menghasilkan konten Instagram
**NgeBadminton YUK!** secara cepat, konsisten, dan tetap terasa
connected dari satu batch ke batch berikutnya.

Workflow utama:

**Upload foto → pilih tipe konten → isi informasi → generate → preview
grid → export.**

Tool bukan AI image generator dan bukan Canva clone. Sistem menggunakan
**design rules yang sudah dikunci** agar output selalu sesuai identitas
NgeBadminton YUK!.

------------------------------------------------------------------------

## 2. Prinsip Produk

**Content first, grid second.**

Feed tidak wajib selalu 3 post sekaligus.

Sistem mendukung:

-   **Single Post** --- 1 post standalone
-   **Connected 2×1** --- 2 post yang membentuk satu komposisi
-   **Connected 3×1** --- 3 post yang membentuk satu horizontal row

Connected 3×1 digunakan untuk hero content, campaign, event penting,
atau saat memang ada kebutuhan visual. Konten tidak boleh dipaksakan
menjadi 3 post hanya demi grid.

Setiap post tetap harus terlihat bagus ketika muncul sendirian di
timeline.

------------------------------------------------------------------------

## 3. Seed Row --- Feed Pertama

Feed pertama NgeBadminton YUK! adalah **Seed Row** dari seluruh sistem.

Copy yang dikunci:

  -----------------------------------------------------------------------
  Left                    Center                  Right
  ----------------------- ----------------------- -----------------------
  **Lagi nyari temen      **NgeBadminton YUK!**   **Ramean lebih seru.**
  main?**                                         

  -----------------------------------------------------------------------

Seed Row menggunakan visual language utama brand:

-   Off-white / white background
-   Royal/electric blue
-   Navy typography
-   Yellow sebagai accent
-   Badminton court geometry
-   Banyak negative space
-   Minimal decoration
-   Clean sports-editorial look

Seed Row bukan artwork terpisah. Seed Row harus tersimpan di sistem
sebagai **Row 0** dan menjadi titik awal continuity feed berikutnya.

------------------------------------------------------------------------

## 4. Brand System

### Warna

-   Royal/electric blue --- primary
-   Navy --- typography
-   Yellow --- accent
-   White/off-white --- background

Gunakan exact color token dari brand/logo saat implementasi.

### Visual Language

-   Badminton court lines / geometry
-   Shuttlecock atau racket hanya jika punya fungsi visual
-   Yellow trajectory/accent secukupnya
-   Bold clean typography
-   Strong hierarchy
-   Banyak negative space
-   Foto asli komunitas sebagai visual utama jika tersedia

### Hindari

-   Generic AI people
-   Gradient berlebihan
-   Glow
-   Fake 3D
-   Terlalu banyak icon
-   Brush/decorative element berlebihan
-   Random sports effects
-   Layout poster yang terlalu ramai
-   Canva-template look

Target visual: **modern sports editorial + community**, bukan generic
sports poster.

------------------------------------------------------------------------

## 5. Persistent Connection State

Ini adalah salah satu fitur utama.

Setiap row/batch yang dibuat harus menghasilkan dan menyimpan sebuah
**connection state**.

Connection state mendeskripsikan bagaimana row berikutnya dapat
melanjutkan visual sebelumnya.

Minimal menyimpan:

-   Dominant background/color
-   Court-line exit points
-   Court-line direction
-   Accent/trajectory exit points
-   Visual density
-   Alignment/grid rhythm
-   Photo presence
-   Connection mode

Contoh sederhana:

``` json
{
  "background": "off-white",
  "courtLineExit": [
    { "x": 0.34, "edge": "top", "direction": "vertical" },
    { "x": 0.78, "edge": "top", "direction": "diagonal" }
  ],
  "accent": "yellow",
  "density": "low"
}
```

Nilai implementasi sebenarnya boleh berbeda. Yang penting sistem
memiliki state yang dapat dibaca oleh generator berikutnya.

------------------------------------------------------------------------

## 6. Connected Feed Engine

Saat membuat konten baru, generator membaca:

1.  Brand System
2.  Feed History
3.  Current Instagram grid position
4.  Previous connection state
5.  Content type
6.  Jumlah post yang akan dibuat
7.  Foto/content yang diberikan user

Generator kemudian membuat komposisi baru yang terasa sebagai kelanjutan
feed sebelumnya.

**Connected tidak berarti semua garis harus selalu tersambung secara
literal.**

Continuity bisa dibuat melalui:

-   Court geometry
-   Alignment
-   Background transition
-   Typography
-   Yellow accent
-   Photo treatment
-   Negative space
-   Direction/movement

Tujuannya adalah membuat profile terasa sebagai **satu visual system
yang berkembang**, bukan satu poster raksasa.

Setelah row baru dibuat, generator menghasilkan **connection state
baru** untuk konten berikutnya.

Flow:

``` text
Seed Row
   ↓
Connection State
   ↓
Next Content
   ↓
Generate Layout
   ↓
New Connection State
   ↓
Next Content
   ↓
...
```

------------------------------------------------------------------------

## 7. Grid Shift Awareness

Instagram grid berubah setiap kali post baru di-upload.

Sistem wajib memperhitungkan posisi aktual grid.

Jika user upload 1 post, feed lama akan bergeser satu posisi. Sistem
**tidak boleh memaksakan puzzle lama tetap tersambung secara literal**.

Dalam kondisi tersebut, continuity dipertahankan melalui:

-   Color system
-   Typography
-   Court geometry
-   Spacing
-   Photo treatment
-   Brand accents

Jika user kembali membuat Connected 3×1, sistem dapat membuat hero row
baru yang kembali memiliki strong horizontal connection.

------------------------------------------------------------------------

## 8. Content Types

### Mabar

Fields:

-   Judul
-   Tanggal
-   Jam
-   Venue
-   Harga
-   CTA

### Announcement

Fields:

-   Headline
-   Supporting text
-   CTA optional

### Community / Moment

Fields:

-   Foto
-   Headline/caption pendek
-   Optional date/event

### Editorial

Fields:

-   Headline
-   Supporting copy
-   Foto optional

### Brand / Hero

Untuk campaign, identity content, atau connected hero row.

Content type menentukan hierarchy informasi, bukan mengganti identitas
visual.

------------------------------------------------------------------------

## 9. Layout Generator

Generator otomatis menentukan:

-   Typography scale
-   Headline placement
-   Image crop
-   Safe area
-   Spacing
-   Logo placement
-   Background
-   Court geometry
-   Yellow accents
-   Connection antar-post
-   Connection terhadap previous feed

User tidak perlu melakukan full drag-and-drop editing.

User hanya perlu kontrol dasar:

-   Crop image
-   Reposition image
-   Zoom image
-   Edit copy
-   **Regenerate Layout**

`Regenerate Layout` memilih variasi komposisi lain dari design system
yang sama, bukan membuat AI image baru.

------------------------------------------------------------------------

## 10. Master Canvas

Untuk Connected 2×1 dan 3×1, desain dibuat terlebih dahulu sebagai
**satu master canvas**.

Kemudian sistem memotong canvas menjadi individual Instagram assets.

Contoh:

``` text
┌──────────┬──────────┬──────────┐
│ POST 01  │ POST 02  │ POST 03  │
│          │          │          │
│   ───────────────→  │          │
└──────────┴──────────┴──────────┘
```

Graphic elements boleh melewati batas post.

Namun:

-   Headline penting tidak boleh terpotong
-   Informasi event tidak boleh berada di gutter
-   Logo tidak boleh terpotong
-   Setiap post tetap harus memiliki komposisi yang layak ketika dilihat
    sendiri

------------------------------------------------------------------------

## 11. Instagram Grid Preview

Sebelum export, tampilkan simulasi Instagram profile.

Preview harus menggabungkan:

-   Existing feed
-   New generated post(s)
-   Urutan upload
-   Grid shifting

Contoh:

``` text
┌───────┬───────┬───────┐
│ NEW   │ NEW   │ NEW   │
├───────┼───────┼───────┤
│ OLD   │ OLD   │ OLD   │
├───────┼───────┼───────┤
│ OLD   │ OLD   │ OLD   │
└───────┴───────┴───────┘
```

User harus bisa mengetahui sebelum export:

**"Kalau konten ini saya upload, profile saya akan terlihat seperti
apa?"**

------------------------------------------------------------------------

## 12. Feed History

Simpan setiap konten yang dibuat.

Minimal simpan:

-   Thumbnail
-   Tanggal dibuat
-   Content type
-   Jumlah post
-   Assets
-   Copy
-   Layout configuration
-   Grid position
-   Connection state

Seed Row harus otomatis menjadi entry pertama:

`Row 0 — Brand Introduction`

Feed History digunakan sebagai sumber Grid Preview dan continuity
generator.

------------------------------------------------------------------------

## 13. Export

Primary action:

**Export for Instagram**

Untuk connected content, sistem export menjadi individual assets.

Contoh:

``` text
01-upload-first.png
02-upload-second.png
03-upload-last.png
```

Sistem wajib menunjukkan **urutan upload yang benar**.

Export harus mempertahankan:

-   Resolution
-   Aspect ratio
-   Image quality
-   Safe area
-   Exact crop

------------------------------------------------------------------------

## 14. MVP Screens

### Dashboard

-   Recent designs
-   Current grid preview
-   Create Content

### Create

-   Pilih 1 / 2 / 3 post
-   Pilih content type
-   Upload foto
-   Isi content

### Editor / Preview

-   Generated layout
-   Edit copy
-   Crop/reposition/zoom
-   Regenerate Layout

### Grid Preview & Export

-   Preview Instagram profile
-   Previous feed + new content
-   Upload order
-   Export assets

------------------------------------------------------------------------

## 15. Non-Goals MVP

Belum perlu:

-   AI image generation
-   Auto posting Instagram
-   Scheduling
-   Analytics
-   Caption generator
-   Hashtag generator
-   Full Canva-like editor
-   Puluhan template
-   Collaboration/team permissions

Jangan overbuild.

------------------------------------------------------------------------

## 16. Definition of Done

MVP dianggap berhasil jika user dapat melakukan:

> Upload foto mabar → masukkan informasi → pilih 1 post atau connected
> feed → mendapatkan desain sesuai brand → desain secara visual aware
> terhadap feed sebelumnya → preview profile → export file siap
> Instagram.

Target workflow: **\<2 menit** untuk konten rutin.

### Core Value

> **Foto dan informasi masuk → feed branded keluar.**

### Core Visual Principle

> **Setiap post bisa berdiri sendiri. Setiap batch terasa berasal dari
> dunia yang sama. Connected feed berkembang terus tanpa menjadi satu
> puzzle raksasa.**
