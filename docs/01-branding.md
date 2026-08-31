# 01-branding.md

# Modul

NgeKas — Branding

## Tujuan

Menentukan identitas visual NgeKas agar konsisten dengan brand utama **NgeBadmintonYuk**.

Branding digunakan sebagai acuan untuk:

* Website
* Dashboard
* Login
* Komponen UI
* Laporan
* Media sosial
* Pengembangan fitur berikutnya

NgeKas tidak memiliki identitas visual terpisah dari NgeBadmintonYuk.

## Scope

Branding MVP mencakup:

* Logo
* Warna
* Typography
* Style UI
* Button
* Card
* Form
* Status
* Icon
* Layout dasar

Branding tidak mencakup:

* Brand guideline lengkap
* Merchandise
* Jersey
* Poster
* Social media template
* Animation guideline

## User Story

Sebagai admin, saya ingin tampilan NgeKas konsisten agar aplikasi mudah dikenali sebagai bagian dari NgeBadmintonYuk.

Sebagai admin, saya ingin interface yang sederhana agar pencatatan keuangan dapat dilakukan dengan cepat.

Sebagai admin, saya ingin informasi keuangan mudah dibaca agar kondisi kas dapat diketahui tanpa melihat data secara detail.

## Flow

Branding diterapkan secara konsisten:

```text
NgeBadmintonYuk
      ↓
    NgeKas
      ↓
Design System
      ↓
UI Components
      ↓
Seluruh Halaman
```

Prioritas desain:

```text
Simple
  ↓
Readable
  ↓
Friendly
  ↓
Sporty
  ↓
Consistent
```

## Database

Branding tidak membutuhkan tabel database pada MVP.

Konfigurasi branding disimpan pada aplikasi melalui:

```text
CSS / Tailwind
Theme Configuration
Reusable UI Components
```

Tidak dibuat tabel khusus untuk menyimpan:

```text
colors
fonts
themes
branding
```

## Validation

Tidak terdapat validasi database.

Validasi visual:

* Warna harus mengikuti palette utama.
* Typography harus konsisten.
* Button dengan fungsi sama menggunakan style yang sama.
* Status transaksi harus mudah dibedakan.
* Nominal uang harus mudah dibaca.
* Tampilan mobile dan desktop harus tetap usable.
* Logo tidak boleh berubah proporsi.

## Business Rule

### Brand Utama

Nama brand:

```text
NgeBadmintonYuk
```

Nama modul keuangan:

```text
NgeKas
```

Hubungan brand:

```text
NgeBadmintonYuk
└── NgeKas
```

NgeKas adalah bagian dari NgeBadmintonYuk, bukan brand yang berdiri sendiri.

### Logo

Logo utama menggunakan identitas **NgeBadmintonYuk**.

Karakter logo:

* Sporty
* Friendly
* Modern
* Tidak terlalu formal
* Mudah dikenali dalam ukuran kecil

Logo dapat digunakan pada:

* Login
* Sidebar
* Header
* Favicon
* Profile komunitas

### Tagline

Tagline:

```text
MAIN BARENG, SEHAT & SERU!
```

Tagline tidak wajib ditampilkan pada seluruh halaman.

Untuk area kecil seperti sidebar, cukup gunakan logo atau nama brand.

## UI

### Color Palette

#### Primary

```text
Cobalt Blue
#2455F5
```

Digunakan untuk:

* Primary button
* Active navigation
* Link
* Focus state
* Highlight utama

#### Accent

```text
Yellow
#FFD23F
```

Digunakan untuk:

* Accent
* Badge
* Highlight
* Decorative element

Yellow tidak digunakan sebagai warna utama untuk teks panjang.

#### Background

```text
Warm White
#FAF7F0
```

Digunakan sebagai background utama aplikasi.

#### Text

```text
Black
#171717
```

Digunakan untuk:

* Heading
* Body
* Nominal
* Informasi utama

### Supporting Color

Untuk kebutuhan status aplikasi dapat menggunakan warna semantik:

```text
Green  → Success / Pemasukan
Red    → Danger / Pengeluaran
Gray   → Neutral
```

Warna tersebut hanya berfungsi sebagai status dan tidak menggantikan warna brand.

### Typography

Font utama:

```text
Plus Jakarta Sans
```

Penggunaan:

```text
Heading     → Bold
Subheading  → SemiBold
Body        → Regular
Label       → Medium
Nominal     → SemiBold / Bold
```

Font tidak dibuat terlalu tebal secara keseluruhan.

Bold digunakan terutama untuk:

* Heading
* Total
* Saldo
* Informasi penting

### Currency

Format nominal:

```text
Rp50.000
Rp150.000
Rp1.250.000
```

Untuk nilai besar:

```text
Saldo Kas

Rp1.250.000
```

Nominal menggunakan alignment yang konsisten pada tabel.

### Button

Primary:

```text
[ + Tambah Pemasukan ]
```

Style:

```text
Background : #2455F5
Text       : White
```

Secondary:

```text
[ Batal ]
```

Danger:

```text
[ Hapus ]
```

### Card

Dashboard menggunakan card sederhana.

Contoh:

```text
┌─────────────────────────┐
│ SALDO KAS               │
│                         │
│ Rp1.250.000             │
└─────────────────────────┘
```

Ringkasan:

```text
┌──────────────┐
│ PEMASUKAN    │
│ Rp2.500.000  │
└──────────────┘

┌──────────────┐
│ PENGELUARAN  │
│ Rp1.250.000  │
└──────────────┘
```

### Transaction Detail

Detail transaksi harus mudah dibaca.

```text
Iuran Main
16 Agustus 2026

Angga                Rp50.000
Budi                 Rp50.000
Candra               Rp50.000

────────────────────────────
TOTAL               Rp150.000
```

Total harus memiliki visual hierarchy lebih tinggi dibanding detail.

### Form

Form dibuat sederhana.

```text
Tanggal
[ 16/08/2026             ]

Kategori
[ Iuran Main           ▼ ]

Keterangan
[ Iuran badminton        ]

Detail

Nama              Nominal
[ Angga ]         [ 50.000 ]

[ + Tambah Detail ]

Total
Rp50.000

[ Simpan ]
```

Total berubah otomatis ketika detail berubah.

### Layout

Desktop:

```text
┌─────────────┬─────────────────────────────┐
│             │                             │
│   Sidebar   │          Content            │
│             │                             │
│             │                             │
└─────────────┴─────────────────────────────┘
```

Sidebar:

```text
NgeBadmintonYuk

Dashboard

Transaksi
├── Pemasukan
└── Pengeluaran

Master
└── Kategori

Laporan
```

Mobile menggunakan navigation yang disesuaikan agar tetap sederhana.

## Livewire Component

Branding diterapkan melalui reusable component.

Contoh:

```text
Button
Card
Input
Select
Modal
Badge
Table
Currency
PageHeader
EmptyState
```

Component digunakan kembali agar tampilan konsisten.

Contoh:

```text
<x-button>
<x-card>
<x-input>
<x-modal>
<x-badge>
```

Livewire Component tidak menentukan warna secara individual jika style sudah tersedia melalui design system.

## Service

Branding tidak membutuhkan Service khusus.

Service aplikasi hanya menangani business logic.

```text
Branding
    ↓
UI Component

Business Logic
    ↓
Service
```

Keduanya dipisahkan.

## Repository

Branding tidak membutuhkan Repository.

Repository hanya digunakan untuk akses data aplikasi.

## Testing

Testing dasar UI:

* Logo tampil dengan benar.
* Warna primary konsisten.
* Button memiliki state yang jelas.
* Form dapat digunakan pada desktop.
* Form dapat digunakan pada mobile.
* Tabel transaksi dapat dibaca dengan baik.
* Nominal menggunakan format Rupiah.
* Total transaksi memiliki visual hierarchy yang jelas.
* Sidebar menunjukkan menu aktif.
* Error validation mudah terlihat.

## Future Improvement

Branding dapat dikembangkan menjadi design system yang lebih lengkap:

* Dark mode
* Complete component library
* Loading state
* Skeleton
* Animation
* Toast notification
* Chart style
* Empty state illustration
* Responsive navigation lanjutan
* PWA icon
* Social media guideline
* Merchandise guideline

Pengembangan tersebut dilakukan setelah MVP NgeKas stabil.
