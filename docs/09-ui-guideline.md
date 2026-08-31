# 09-ui-guideline.md

# NgeKas — UI Guideline

## Tujuan

Dokumen ini menjadi acuan utama desain UI NgeKas agar seluruh halaman memiliki karakter visual yang konsisten dengan **NgeBadmintonYuk**.

Target desain:

> **Simple, sporty, friendly, editorial, dan terasa dibuat khusus untuk komunitas.**

NgeKas **tidak boleh terlihat seperti template dashboard SaaS, admin panel generik, atau desain AI-generated yang dipenuhi card.**

---

# Design Direction

Arah utama:

```text
Editorial
+
Sports
+
Community
+
Finance
```

Bukan:

```text
Generic SaaS Dashboard
Generic Fintech
Corporate Accounting
Admin Template
```

NgeKas harus terasa seperti bagian dari komunitas badminton, bukan software accounting formal.

---

# Brand Personality

NgeBadmintonYuk memiliki karakter:

```text
Friendly
Energetic
Casual
Modern
Community-first
Sporty
```

NgeKas membawa karakter tersebut dengan pendekatan yang sedikit lebih tenang agar informasi keuangan tetap mudah dibaca.

Prinsip:

> **Sporty pada identitas, tenang pada data.**

---

# Color System

## Primary

```text
Cobalt Blue
#2455F5
```

Digunakan untuk:

* Brand area
* Primary action
* Navigation active
* Link
* Highlight penting
* Hero section tertentu

Primary blue tidak harus muncul pada setiap komponen.

---

## Accent

```text
Yellow
#FFD23F
```

Yellow merupakan **accent**, bukan warna utama UI.

Digunakan untuk:

* Underline
* Small badge
* Dot
* Decorative stroke
* Active indicator
* Highlight kecil
* Elemen shuttlecock/sport

Hindari penggunaan yellow pada area yang terlalu luas.

---

## Background

```text
Warm White
#FAF7F0
```

Background utama tidak menggunakan pure white secara keseluruhan.

Tujuannya memberikan karakter yang lebih hangat dan tidak terasa seperti admin template.

---

## Surface

```text
White
#FFFFFF
```

Digunakan jika dibutuhkan untuk:

* Form
* Dropdown
* Modal
* Data surface tertentu

Tidak semua section harus menggunakan white card.

---

## Text

Primary:

```text
#171717
```

Secondary:

```text
#626262
```

Muted:

```text
#929292
```

---

# Semantic Color

Semantic color hanya digunakan jika memiliki arti.

## Income

```text
Green
```

Untuk:

```text
+ Rp150.000
```

## Expense

```text
Red
```

Untuk:

```text
- Rp200.000
```

## Warning

Yellow dapat digunakan untuk warning ringan.

Semantic color **tidak boleh menjadi warna dekoratif**.

---

# Typography

Font utama:

```text
Plus Jakarta Sans
```

Weight:

```text
Regular     400
Medium      500
SemiBold    600
Bold        700
```

Hindari:

```text
800
900
```

untuk mayoritas UI.

Logo dapat memiliki typography sendiri.

---

# Typography Hierarchy

## Hero Number

Digunakan untuk saldo.

Desktop:

```text
48px – 64px
600
```

Mobile:

```text
36px – 44px
600
```

Contoh:

```text
Saldo Kas

Rp1.250.000
```

---

## Page Title

```text
32px
600
```

Contoh:

```text
Pemasukan
```

---

## Section Title

```text
18px – 20px
600
```

---

## Body

```text
14px – 16px
400
```

---

## Label

```text
12px – 14px
500
```

---

# Number Formatting

Nominal harus memiliki hierarchy yang jelas.

Gunakan:

```text
Rp1.250.000
```

Bukan:

```text
Rp 1,250,000.00
```

Untuk transaksi:

```text
+Rp150.000
-Rp200.000
```

Untuk saldo:

```text
Rp1.250.000
```

Gunakan tabular number jika tersedia:

```css
font-variant-numeric: tabular-nums;
```

agar nominal pada daftar terlihat rapi.

---

# Layout Philosophy

NgeKas **tidak menggunakan card untuk semua hal**.

Gunakan kombinasi:

```text
Large Hero
Editorial Section
Simple List
Data Table
Flat Surface
Accent Block
Occasional Card
```

Bukan:

```text
Card
Card
Card
Card
Card
Card
```

---

# Dashboard Layout

Dashboard bukan kumpulan statistik card.

Struktur yang disarankan:

```text
┌─────────────────────────────────────────────────────┐
│                                                     │
│  NGEBADMINTONYUK / NgeKas                          │
│                                                     │
│  Saldo Kas                                          │
│                                                     │
│  Rp1.250.000                                        │
│                                                     │
│  + Rp350.000 bulan ini                              │
│                                                     │
└─────────────────────────────────────────────────────┘


PEMASUKAN BULAN INI             PENGELUARAN BULAN INI

Rp1.500.000                     Rp1.150.000


───────────────────────────────────────────────────────


Transaksi Terbaru                         + Catat Transaksi


16 AUG

Iuran Main
Angga, Budi, Candra

                                         +Rp150.000


16 AUG

Sewa Lapangan
Court 1 & Court 2

                                         -Rp200.000


15 AUG

Shuttlecock
1 tube

                                          -Rp95.000
```

Saldo menjadi **hero**, bukan salah satu dari empat card yang ukurannya sama.

---

# Hero Balance

Saldo adalah elemen paling penting pada dashboard.

Contoh:

```text
Saldo Kas

Rp1.250.000

↑ Rp350.000 bulan ini
```

Hero dapat menggunakan background:

```text
#2455F5
```

Text:

```text
White
```

Accent:

```text
#FFD23F
```

Tambahkan elemen grafis ringan seperti:

```text
swoosh
shuttlecock line
court line
```

tetapi jangan sampai mengganggu angka.

---

# Sports Visual Language

Identitas badminton tidak harus selalu menggunakan gambar raket atau shuttlecock besar.

Gunakan elemen abstrak seperti:

```text
Court line
Shuttlecock trajectory
Motion line
Diagonal cut
Yellow brush stroke
Small shuttlecock icon
```

Contoh:

```text
──────────────╲
               ╲
                ●
```

Motion line dapat menjadi motif kecil pada:

* Hero
* Empty state
* Login
* Section divider

Gunakan dengan hemat.

---

# Transaction List

Transaksi lebih baik menggunakan **editorial list** dibanding card individual.

Gunakan:

```text
16 AUG

Iuran Main
Angga, Budi, Candra

+Rp150.000
```

Kemudian divider:

```text
────────────────────────────────────────
```

Transaksi berikutnya:

```text
16 AUG

Sewa Lapangan
Court 1 & Court 2

-Rp200.000
```

Hindari:

```text
┌───────────────────────┐
│ Transaction           │
│ Rp150.000             │
└───────────────────────┘

┌───────────────────────┐
│ Transaction           │
│ Rp200.000             │
└───────────────────────┘
```

jika tidak ada alasan kuat menggunakan card.

---

# Transaction Detail

Detail pemasukan:

```text
Iuran Main

16 Agustus 2026
Main badminton mingguan


DETAIL

Angga                             Rp50.000
Budi                              Rp50.000
Candra                            Rp50.000

──────────────────────────────────────────

TOTAL                            Rp150.000
```

Total harus lebih dominan dibanding detail.

---

# Form Design

Form harus terasa cepat digunakan.

Contoh:

```text
Tambah Pemasukan


Tanggal

16 Agustus 2026


Kategori

Iuran Main


Keterangan

Main badminton mingguan


DETAIL PEMASUKAN


Angga                              Rp50.000
Catatan opsional

──────────────────────────────────────────

Budi                               Rp50.000
Catatan opsional


+ Tambah Detail


TOTAL

Rp100.000


                         Simpan Pemasukan
```

Jangan memasukkan setiap field ke card terpisah.

---

# Dynamic Detail

Saat menambahkan detail:

```text
+ Tambah Detail
```

baris baru muncul secara natural.

Gunakan divider daripada nested card jika memungkinkan.

Contoh:

```text
Angga

Rp50.000

────────────────────

Budi

Rp50.000

────────────────────

+ Tambah Detail
```

---

# Button

## Primary

Gunakan cobalt:

```text
#2455F5
```

Contoh:

```text
+ Tambah Pemasukan
```

atau:

```text
Simpan
```

Radius:

```text
10px – 12px
```

Tidak perlu full pill.

---

# Secondary Button

Gunakan:

```text
transparent
```

atau surface neutral.

Contoh:

```text
Batal
```

---

# Destructive Action

Hapus tidak perlu menjadi tombol merah besar.

Gunakan text/button kecil:

```text
Hapus
```

Warna merah hanya untuk menandakan destructive action.

---

# Border Radius

Jangan gunakan radius yang sama untuk semua komponen.

Gunakan hierarchy:

```text
Hero
20px – 24px

Card
16px – 20px

Input
10px – 12px

Button
10px – 12px

Badge
999px
```

---

# Shadow

Hindari shadow besar.

Default:

```text
No Shadow
```

Gunakan border ringan atau perbedaan background.

Jika shadow diperlukan:

```text
Very subtle
```

Tujuannya agar UI terasa flat dan modern.

---

# Border

Gunakan border:

```text
#E7E5DF
```

atau neutral yang sesuai.

Border dapat menggantikan shadow.

---

# Spacing

Gunakan sistem spacing konsisten.

Base:

```text
4px
```

Scale:

```text
4
8
12
16
24
32
48
64
```

Hindari nilai random seperti:

```text
13px
27px
39px
```

kecuali memang dibutuhkan.

---

# Page Width

Content utama:

```text
max-width: 1280px
```

Jangan memaksakan seluruh data memenuhi layar desktop besar.

Whitespace adalah bagian dari desain.

---

# Navigation

Desktop menggunakan sidebar sederhana.

```text
NgeBadmintonYuk
NgeKas


Dashboard


TRANSAKSI

Pemasukan
Pengeluaran


MASTER

Kategori


Laporan
```

Active navigation dapat menggunakan:

```text
Blue text
+
Yellow indicator
```

Tidak semua menu membutuhkan icon.

---

# Icon Usage

Icon hanya digunakan jika membantu memahami fungsi.

Boleh:

```text
+ Tambah
← Kembali
Filter
Search
Calendar
Delete
Edit
```

Tidak perlu:

```text
Dashboard icon
Income icon
Expense icon
Category icon
Report icon
```

jika label sudah jelas.

Hindari sidebar penuh icon tanpa alasan.

---

# Microcopy

Gunakan bahasa manusia.

Baik:

```text
Catat uang masuk komunitas.
```

```text
Belum ada pemasukan.
```

```text
Mulai dari transaksi pertama NgeBadmintonYuk.
```

Hindari:

```text
Manage your financial transactions efficiently.
```

```text
Optimize your financial management experience.
```

```text
Seamlessly manage your finances.
```

Copy seperti itu terlalu generic dan terasa AI-generated.

---

# Realistic Data

Mockup dan development harus menggunakan data yang masuk akal.

Gunakan:

```text
Angga
Budi
Widia
Kevin

Iuran Main
Lapangan
Shuttlecock
Air Mineral

Rp35.000
Rp70.000
Rp200.000
```

Hindari:

```text
John Doe
Jane Smith
Transaction #001
Product ABC
Lorem Ipsum
$1,234
```

Tujuannya agar desain diuji menggunakan kondisi penggunaan sebenarnya.

---

# Empty State

Empty state harus sederhana.

Contoh:

```text
Belum ada pemasukan.

Kalau ada uang masuk, catat di sini.

+ Tambah Pemasukan
```

Boleh menggunakan ilustrasi shuttlecock kecil.

Jangan menggunakan ilustrasi 3D generic.

---

# Loading

Gunakan skeleton sederhana.

Hindari spinner besar di tengah halaman jika data hanya sedang diperbarui.

Untuk button:

```text
Simpan
```

menjadi:

```text
Menyimpan...
```

---

# Mobile

NgeKas harus nyaman digunakan dari HP karena pencatatan kemungkinan dilakukan setelah atau saat kegiatan berlangsung.

Prioritas mobile:

```text
Fast Input
Readable Number
Large Touch Target
Simple Navigation
Minimal Modal
```

Transaction table berubah menjadi list.

Desktop:

```text
Tanggal | Kategori | Detail | Total
```

Mobile:

```text
16 AUG

Iuran Main
3 detail

+Rp150.000
```

---

# Responsive Priority

Urutan informasi mobile:

```text
Amount
↓
Transaction Name
↓
Date
↓
Detail
↓
Secondary Information
```

Jangan mengecilkan desktop table sampai muat di mobile.

---

# Anti AI-Slop Rules

## Dilarang

Jangan otomatis menggunakan:

```text
4 statistic cards di bagian atas
```

Jangan membuat semua section:

```text
white card
rounded-xl
shadow
```

Jangan menggunakan gradient tanpa alasan.

Jangan menggunakan glassmorphism.

Jangan menggunakan neon glow.

Jangan menggunakan shadow besar.

Jangan menggunakan icon pada setiap label.

Jangan menggunakan badge untuk setiap informasi.

Jangan menggunakan progress bar jika tidak ada progress nyata.

Jangan menggunakan chart hanya untuk mengisi ruang kosong.

Jangan menggunakan decorative blob generic.

Jangan menggunakan ilustrasi corporate generic.

Jangan membuat seluruh heading ExtraBold.

Jangan memenuhi setiap area kosong.

Jangan menggunakan copywriting SaaS generic.

---

# Preferred Patterns

Prioritaskan:

```text
Whitespace
Typography
Hierarchy
Editorial Lists
Flat Surfaces
Strong Hero Number
Real Data
Subtle Sports Elements
Intentional Color
Simple Interaction
```

Jika sebuah elemen tidak membantu:

```text
Read
Understand
Navigate
Act
```

maka pertimbangkan untuk menghapusnya.

---

# Design Test

Sebelum sebuah halaman dianggap selesai, tanyakan:

```text
Apakah halaman ini terlihat seperti template admin?

Apakah terlalu banyak card?

Apakah terlalu banyak rounded rectangle?

Apakah semua bagian memiliki visual weight yang sama?

Apakah blue dan yellow digunakan dengan sengaja?

Apakah angka penting langsung terlihat?

Apakah ada whitespace yang cukup?

Apakah desain tetap masuk akal tanpa icon?

Apakah data yang digunakan realistis?

Apakah ini terasa seperti NgeBadmintonYuk?
```

Jika halaman masih bisa diganti logonya menjadi brand lain tanpa terasa berbeda, berarti identitas desain belum cukup kuat.

---

# Visual Signature

NgeKas harus memiliki minimal tiga signature visual yang konsisten.

### 1. Cobalt Hero

Saldo atau informasi utama menggunakan cobalt blue.

### 2. Yellow Motion Accent

Gunakan yellow stroke/swoosh secara terbatas untuk membawa karakter badminton.

### 3. Editorial Transaction List

Transaksi menggunakan typography + whitespace + divider, bukan kumpulan card.

Kombinasi tersebut menjadi karakter utama UI NgeKas.

---

# Final Direction

Arah akhir NgeKas:

```text
NgeBadmintonYuk

        SPORT
          ×
      COMMUNITY
          ×
        MONEY

          ↓

        NgeKas
```

Secara visual:

```text
Warm White Background
+
Cobalt Brand Blocks
+
Yellow Sports Accent
+
Strong Typography
+
Large Financial Numbers
+
Editorial Transaction Lists
+
Minimal Cards
```

Tujuan akhirnya bukan membuat NgeKas terlihat seperti aplikasi keuangan yang kompleks.

Tujuannya adalah:

> **membuat pencatatan uang komunitas terasa sederhana, cepat, dan tetap punya karakter NgeBadmintonYuk.**
