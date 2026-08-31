# 00-project.md

# Modul

NgeKas — Project Overview

## Tujuan

NgeKas adalah aplikasi keuangan sederhana untuk membantu pengelolaan keuangan komunitas **NgeBadmintonYuk**.

Aplikasi difokuskan untuk mencatat:

* Pemasukan
* Detail pemasukan
* Pengeluaran
* Detail pengeluaran
* Saldo kas
* Laporan keuangan sederhana

Versi awal aplikasi digunakan oleh satu pengelola/admin.

## Scope

### Termasuk MVP

* Login admin
* Dashboard keuangan
* Kategori transaksi
* Pencatatan pemasukan
* Detail pemasukan
* Pencatatan pengeluaran
* Detail pengeluaran
* Perhitungan total otomatis
* Perhitungan saldo otomatis
* Laporan berdasarkan periode

### Tidak Termasuk MVP

* Registrasi user
* Member management
* Role dan permission kompleks
* Jadwal main
* Booking
* Payment gateway
* Jurnal akuntansi
* Chart of Account
* Neraca
* Laba rugi formal
* Ranking pemain
* Tournament
* Aplikasi mobile

## User Story

Sebagai admin, saya ingin login ke NgeKas agar hanya saya yang dapat mengelola data keuangan.

Sebagai admin, saya ingin mencatat pemasukan beserta detailnya agar sumber pemasukan dapat diketahui.

Sebagai admin, saya ingin mencatat pengeluaran beserta detailnya agar penggunaan uang dapat diketahui.

Sebagai admin, saya ingin total transaksi dihitung otomatis dari detail transaksi agar tidak perlu menghitung manual.

Sebagai admin, saya ingin melihat saldo kas agar mengetahui kondisi keuangan komunitas saat ini.

Sebagai admin, saya ingin melihat laporan berdasarkan periode agar dapat mengetahui pemasukan dan pengeluaran komunitas.

## Flow

Flow utama aplikasi:

```text
Login
  ↓
Dashboard
  ↓
Kategori
  ↓
Pemasukan / Pengeluaran
  ↓
Detail Transaksi
  ↓
Total Otomatis
  ↓
Saldo Kas
  ↓
Laporan
```

Contoh pemasukan:

```text
Iuran Main
16 Agustus 2026

Angga                 Rp50.000
Budi                  Rp50.000
Candra                Rp50.000
--------------------------------
Total                Rp150.000
```

Contoh pengeluaran:

```text
Pengeluaran
16 Agustus 2026

Sewa Court 1         Rp100.000
Sewa Court 2         Rp100.000
Shuttlecock           Rp75.000
--------------------------------
Total                Rp275.000
```

Saldo dihitung dari:

```text
Total Pemasukan
-
Total Pengeluaran
=
Saldo Kas
```

## Database

Database utama MVP terdiri dari:

```text
users
categories

incomes
income_details

expenses
expense_details
```

Relasi dasar:

```text
categories
    │
    ├── incomes
    │      └── income_details
    │
    └── expenses
           └── expense_details
```

Total pemasukan dan pengeluaran berasal dari penjumlahan nominal pada detail transaksi.

Struktur database secara lengkap akan dibahas pada:

```text
02-database.md
```

## Validation

Validasi dasar:

* Tanggal transaksi wajib diisi.
* Kategori wajib dipilih.
* Transaksi minimal memiliki satu detail.
* Nama/keterangan detail wajib diisi.
* Nominal wajib diisi.
* Nominal harus lebih besar dari `0`.
* Total tidak diinput secara manual.
* Total dihitung otomatis dari seluruh detail transaksi.

## Business Rule

### Pemasukan

Satu pemasukan dapat memiliki banyak detail.

Contoh:

```text
Iuran Main
├── Angga     Rp50.000
├── Budi      Rp50.000
└── Candra    Rp50.000
```

Total:

```text
Rp150.000
```

### Pengeluaran

Satu pengeluaran dapat memiliki banyak detail.

Contoh:

```text
Keperluan Main
├── Lapangan      Rp200.000
├── Shuttlecock    Rp75.000
└── Air Mineral    Rp25.000
```

Total:

```text
Rp300.000
```

### Saldo

Saldo tidak diinput manual.

```text
Saldo = Total Pemasukan - Total Pengeluaran
```

### Penghapusan

Jika transaksi dihapus, seluruh detail transaksi terkait ikut dihapus.

### Perubahan Detail

Jika nominal detail ditambah, diubah, atau dihapus, total transaksi harus mengikuti nilai detail terbaru.

## UI

Identitas aplikasi mengikuti branding **NgeBadmintonYuk**.

Warna utama:

```text
Cobalt Blue
#2455F5

Yellow
#FFD23F

Warm White
#FAF7F0

Black
#171717
```

Karakter UI:

* Clean
* Modern
* Friendly
* Sporty
* Tidak terlalu formal seperti aplikasi accounting

Navigasi utama:

```text
Dashboard

Transaksi
├── Pemasukan
└── Pengeluaran

Master
└── Kategori

Laporan
```

## Livewire Component

Komponen utama MVP:

```text
Dashboard

Category
├── CategoryIndex
└── CategoryForm

Income
├── IncomeIndex
├── IncomeForm
└── IncomeDetail

Expense
├── ExpenseIndex
├── ExpenseForm
└── ExpenseDetail

Report
└── ReportIndex
```

Detail implementasi komponen dibahas pada modul masing-masing.

## Service

Business logic yang tidak cocok ditempatkan langsung di Livewire Component dipindahkan ke Service.

Contoh:

```text
IncomeService
ExpenseService
ReportService
```

Service bertanggung jawab terhadap proses seperti:

* Menyimpan transaksi beserta detail
* Mengubah transaksi
* Menghapus transaksi
* Menghitung total
* Menghitung saldo
* Menghasilkan data laporan

## Repository

Repository digunakan sebagai pemisah akses data dari business logic.

Repository utama:

```text
CategoryRepository
IncomeRepository
ExpenseRepository
ReportRepository
```

Livewire Component tidak menangani query kompleks secara langsung.

Flow:

```text
Livewire
   ↓
Service
   ↓
Repository
   ↓
Database
```

## Testing

Minimal testing MVP mencakup:

* Admin dapat login.
* Admin dapat membuat kategori.
* Admin dapat membuat pemasukan.
* Pemasukan dapat memiliki beberapa detail.
* Total pemasukan dihitung dengan benar.
* Admin dapat mengubah pemasukan.
* Admin dapat menghapus pemasukan.
* Admin dapat membuat pengeluaran.
* Pengeluaran dapat memiliki beberapa detail.
* Total pengeluaran dihitung dengan benar.
* Admin dapat mengubah pengeluaran.
* Admin dapat menghapus pengeluaran.
* Saldo dihitung dengan benar.
* Laporan periode menampilkan transaksi yang sesuai.

## Future Improvement

Setelah MVP stabil, NgeKas dapat dikembangkan dengan:

* Multi user
* Role dan permission
* Member NgeBadmintonYuk
* Jadwal main
* Iuran per member
* Status pembayaran
* Payment gateway
* Rekening/kas lebih dari satu
* Export Excel
* Export PDF
* Dashboard statistik
* Audit log
* Attachment bukti transaksi

Fitur tersebut **tidak termasuk scope MVP saat ini**.
