# 02-database.md

# Modul

NgeKas — Database

## Tujuan

Menentukan struktur database MVP NgeKas yang sederhana dan mendukung pencatatan pemasukan serta pengeluaran secara detail.

Database harus mendukung:

* Admin
* Kategori transaksi
* Pemasukan
* Detail pemasukan
* Pengeluaran
* Detail pengeluaran
* Perhitungan total
* Perhitungan saldo
* Laporan berdasarkan periode

## Scope

Database MVP terdiri dari:

```text
users
categories
incomes
income_details
expenses
expense_details
```

Tidak dibuat tabel khusus untuk:

```text
balances
cash_balances
reports
journals
accounts
members
activities
payments
```

Saldo dan laporan dihitung berdasarkan data pemasukan dan pengeluaran.

## User Story

Sebagai admin, saya ingin data transaksi tersimpan secara terstruktur agar riwayat keuangan dapat dilihat kembali.

Sebagai admin, saya ingin satu transaksi memiliki beberapa detail agar sumber pemasukan atau penggunaan uang dapat dicatat secara rinci.

Sebagai admin, saya ingin total transaksi dihitung berdasarkan detail agar tidak terjadi perbedaan antara total dan rincian transaksi.

Sebagai admin, saya ingin saldo dihitung dari seluruh transaksi agar tidak perlu mengelola saldo secara manual.

## Flow

Struktur data utama:

```text
User
 │
 ├── Income
 │     │
 │     └── Income Detail
 │
 └── Expense
       │
       └── Expense Detail
```

Kategori:

```text
Category
 │
 ├── Income
 │
 └── Expense
```

Flow penyimpanan pemasukan:

```text
Income
  ↓
Income Details
  ↓
SUM Nominal Detail
  ↓
Total Income
```

Flow penyimpanan pengeluaran:

```text
Expense
  ↓
Expense Details
  ↓
SUM Nominal Detail
  ↓
Total Expense
```

Saldo:

```text
SUM Income Details
-
SUM Expense Details
=
Saldo Kas
```

## Database

### users

Menyimpan admin yang dapat mengakses aplikasi.

```text
users

id
name
email
password
remember_token
created_at
updated_at
```

Untuk MVP hanya terdapat satu admin.

Tidak diperlukan:

```text
role
permission
status
```

### categories

Menyimpan kategori transaksi.

```text
categories

id
name
type
created_at
updated_at
```

`type`:

```text
income
expense
```

Contoh:

```text
Iuran Main       → income
Donasi           → income

Lapangan         → expense
Shuttlecock      → expense
Konsumsi         → expense
```

Kategori pemasukan hanya dapat digunakan pada transaksi pemasukan.

Kategori pengeluaran hanya dapat digunakan pada transaksi pengeluaran.

### incomes

Menyimpan header transaksi pemasukan.

```text
incomes

id
user_id
category_id
date
description
created_at
updated_at
```

Contoh:

```text
date
2026-08-16

category
Iuran Main

description
Iuran badminton tanggal 16 Agustus 2026
```

Total tidak disimpan pada tabel `incomes`.

Total dihitung dari:

```text
SUM(income_details.amount)
```

### income_details

Menyimpan rincian dari satu pemasukan.

```text
income_details

id
income_id
name
amount
note
created_at
updated_at
```

Contoh:

```text
income_id : 1
name      : Angga
amount    : 50000
note      : -
```

Satu pemasukan dapat memiliki banyak detail:

```text
Income #1
│
├── Angga      Rp50.000
├── Budi       Rp50.000
├── Candra     Rp50.000
└── Dimas      Rp50.000
```

Total:

```text
Rp200.000
```

### expenses

Menyimpan header transaksi pengeluaran.

```text
expenses

id
user_id
category_id
date
description
created_at
updated_at
```

Total tidak disimpan pada tabel `expenses`.

Total dihitung dari:

```text
SUM(expense_details.amount)
```

### expense_details

Menyimpan rincian pengeluaran.

```text
expense_details

id
expense_id
name
amount
note
created_at
updated_at
```

Contoh:

```text
Expense #1
│
├── Sewa Court 1       Rp100.000
├── Sewa Court 2       Rp100.000
├── Shuttlecock         Rp75.000
└── Air Mineral         Rp25.000
```

Total:

```text
Rp300.000
```

### Relationship

Relasi database:

```text
users
 │
 ├── hasMany → incomes
 │
 └── hasMany → expenses


categories
 │
 ├── hasMany → incomes
 │
 └── hasMany → expenses


incomes
 │
 ├── belongsTo → user
 ├── belongsTo → category
 └── hasMany   → income_details


income_details
 │
 └── belongsTo → income


expenses
 │
 ├── belongsTo → user
 ├── belongsTo → category
 └── hasMany   → expense_details


expense_details
 │
 └── belongsTo → expense
```

### Foreign Key

```text
incomes.user_id
→ users.id

incomes.category_id
→ categories.id

income_details.income_id
→ incomes.id

expenses.user_id
→ users.id

expenses.category_id
→ categories.id

expense_details.expense_id
→ expenses.id
```

### Delete Rule

Ketika pemasukan dihapus:

```text
Income
  ↓
Income Details
```

Semua `income_details` terkait ikut dihapus.

Menggunakan:

```text
ON DELETE CASCADE
```

Hal yang sama berlaku pada:

```text
Expense
  ↓
Expense Details
```

### Nominal

Nominal disimpan sebagai integer.

Contoh:

```text
Rp50.000

Database:
50000
```

Tidak menyimpan:

```text
"Rp50.000"
"50.000"
```

Format Rupiah hanya dilakukan pada presentation/UI.

Untuk MVP, nominal tidak menggunakan pecahan/desimal.

### Date

Tanggal transaksi menggunakan:

```text
DATE
```

Contoh:

```text
2026-08-16
```

Waktu transaksi tidak diperlukan untuk MVP.

### Saldo

Tidak dibuat tabel:

```text
balances
```

Saldo dihitung:

```text
Total Income
-
Total Expense
=
Current Balance
```

Contoh:

```text
Income
Rp1.500.000

Expense
Rp900.000

Saldo
Rp600.000
```

### Report

Tidak dibuat tabel:

```text
reports
```

Report merupakan hasil query transaksi.

Contoh filter:

```text
01-08-2026
sampai
31-08-2026
```

Sistem menghitung:

```text
Total Pemasukan
Total Pengeluaran
Selisih
Daftar Pemasukan
Daftar Pengeluaran
```

## Validation

### Category

```text
name
required

type
required
in:income,expense
```

Nama kategori tidak boleh kosong.

### Income

```text
date
required
date

category_id
required
exists:categories,id

description
nullable
```

Kategori harus memiliki:

```text
type = income
```

### Income Detail

```text
name
required

amount
required
integer
min:1

note
nullable
```

Satu pemasukan minimal memiliki satu detail.

### Expense

```text
date
required
date

category_id
required
exists:categories,id

description
nullable
```

Kategori harus memiliki:

```text
type = expense
```

### Expense Detail

```text
name
required

amount
required
integer
min:1

note
nullable
```

Satu pengeluaran minimal memiliki satu detail.

## Business Rule

### Total Tidak Disimpan Manual

Total pemasukan:

```text
income_details
→ SUM(amount)
```

Total pengeluaran:

```text
expense_details
→ SUM(amount)
```

Tujuannya agar tidak terjadi kondisi:

```text
Header Total      Rp200.000
Detail Total      Rp150.000
```

### Detail Wajib Ada

Tidak diperbolehkan menyimpan:

```text
Income
└── Tidak memiliki detail
```

atau:

```text
Expense
└── Tidak memiliki detail
```

Minimal:

```text
1 detail
```

### Category Type

Kategori:

```text
Iuran Main
type = income
```

tidak boleh digunakan pada pengeluaran.

Kategori:

```text
Lapangan
type = expense
```

tidak boleh digunakan pada pemasukan.

### User

`user_id` otomatis menggunakan admin yang sedang login.

Admin tidak memilih user secara manual pada form transaksi.

### Database Transaction

Penyimpanan header dan detail harus dilakukan dalam satu database transaction.

Flow:

```text
BEGIN

Create Income
Create Income Details

COMMIT
```

Jika detail gagal disimpan:

```text
ROLLBACK
```

Hal yang sama berlaku pada pengeluaran.

## UI

Database tidak ditampilkan secara langsung kepada admin.

UI menggunakan istilah yang mudah dipahami.

Contoh:

```text
income
→ Pemasukan

expense
→ Pengeluaran

income_details
→ Detail Pemasukan

expense_details
→ Detail Pengeluaran
```

ID database tidak perlu ditampilkan pada UI.

Contoh daftar pemasukan:

```text
Tanggal       Kategori       Keterangan          Total

16 Agu 2026   Iuran Main     Main Mingguan       Rp500.000
20 Agu 2026   Donasi         Donasi Komunitas     Rp50.000
```

Admin dapat membuka transaksi untuk melihat detail.

## Livewire Component

Livewire berinteraksi dengan model:

```text
User
Category
Income
IncomeDetail
Expense
ExpenseDetail
```

Model relationship digunakan untuk mengambil detail.

Contoh konsep:

```text
Income
└── details
```

dan:

```text
Expense
└── details
```

Livewire tidak menghitung saldo menggunakan state manual permanen.

Data selalu berasal dari database.

## Service

Service menangani proses transaksi yang melibatkan header dan detail.

```text
IncomeService
ExpenseService
```

Contoh flow:

```text
IncomeForm
    ↓
IncomeService
    ↓
Database Transaction
    ↓
Income
    +
Income Details
```

Service juga menangani:

* Create
* Update
* Delete
* Perhitungan total transaksi

## Repository

Repository menangani akses data.

```text
CategoryRepository
IncomeRepository
ExpenseRepository
ReportRepository
```

Repository dapat menangani:

* Query list
* Filter tanggal
* Filter kategori
* Detail transaksi
* Total pemasukan
* Total pengeluaran
* Saldo
* Data laporan

Repository tidak menangani validation dari form.

## Testing

Testing database minimal:

* User dapat memiliki banyak pemasukan.
* User dapat memiliki banyak pengeluaran.
* Category income dapat digunakan pada pemasukan.
* Category expense dapat digunakan pada pengeluaran.
* Category income tidak dapat digunakan pada pengeluaran.
* Category expense tidak dapat digunakan pada pemasukan.
* Income dapat memiliki banyak detail.
* Expense dapat memiliki banyak detail.
* Total income sesuai jumlah detail.
* Total expense sesuai jumlah detail.
* Menghapus income menghapus detail terkait.
* Menghapus expense menghapus detail terkait.
* Nominal tidak dapat bernilai `0`.
* Nominal tidak dapat bernilai negatif.
* Income tanpa detail tidak dapat disimpan.
* Expense tanpa detail tidak dapat disimpan.
* Saldo sesuai total pemasukan dikurangi total pengeluaran.
* Rollback berjalan jika penyimpanan detail gagal.

## Future Improvement

Database dapat dikembangkan dengan tabel tambahan:

```text
members
activities
payments
accounts
attachments
audit_logs
```

Jika nantinya terdapat beberapa tempat penyimpanan uang:

```text
accounts

Kas Tunai
Bank BRI
E-Wallet
```

Transaksi dapat memiliki:

```text
account_id
```

Jika pembayaran member mulai dikelola:

```text
members
payments
activities
```

Jika dibutuhkan bukti transaksi:

```text
attachments
```

Perubahan tersebut tidak termasuk dalam MVP NgeKas saat ini.
