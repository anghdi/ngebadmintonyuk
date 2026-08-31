# 06-income.md

# Modul

NgeKas — Income

## Tujuan

Income digunakan untuk mencatat seluruh pemasukan keuangan komunitas **NgeBadmintonYuk**.

Setiap pemasukan terdiri dari:

```text
Income
├── Informasi Transaksi
└── Income Details
```

Satu transaksi dapat memiliki banyak detail.

Contoh:

```text
Iuran Main
16 Agustus 2026

Angga       Rp50.000
Budi        Rp50.000
Candra      Rp50.000
Dimas       Rp50.000
---------------------
Total      Rp200.000
```

Total pemasukan dihitung otomatis berdasarkan seluruh detail transaksi.

## Scope

Income MVP mencakup:

```text
Daftar Pemasukan
Detail Pemasukan
Tambah Pemasukan
Edit Pemasukan
Hapus Pemasukan

Detail
├── Tambah Detail
├── Edit Detail
└── Hapus Detail

Total Otomatis
Filter Tanggal
Filter Kategori
```

Tidak termasuk:

```text
Payment Gateway
Member
Status Pembayaran
Invoice
Receipt
Recurring Income
Import Excel
Attachment Bukti Transfer
```

## User Story

Sebagai admin, saya ingin mencatat pemasukan agar seluruh uang yang masuk dapat terdokumentasi.

Sebagai admin, saya ingin memilih kategori pemasukan agar transaksi dapat dikelompokkan.

Sebagai admin, saya ingin memasukkan beberapa detail dalam satu pemasukan agar sumber uang dapat diketahui secara rinci.

Sebagai admin, saya ingin total dihitung otomatis agar tidak perlu menjumlahkan nominal secara manual.

Sebagai admin, saya ingin mengubah transaksi jika terjadi kesalahan pencatatan.

Sebagai admin, saya ingin menghapus transaksi yang salah.

Sebagai admin, saya ingin melihat detail pemasukan agar mengetahui siapa atau apa saja sumber pemasukan tersebut.

## Flow

Flow utama:

```text
Pemasukan
    ↓
Daftar Pemasukan
    │
    ├── Tambah
    ├── Detail
    ├── Edit
    └── Hapus
```

Tambah pemasukan:

```text
Tambah Pemasukan
       ↓
Tanggal
       ↓
Kategori
       ↓
Keterangan
       ↓
Tambah Detail
       ↓
Nama + Nominal
       ↓
Total Otomatis
       ↓
Validasi
       ↓
Simpan
```

Contoh:

```text
Tanggal
16 Agustus 2026

Kategori
Iuran Main

Keterangan
Main badminton mingguan

Detail

Angga       Rp50.000
Budi        Rp50.000
Candra      Rp50.000

Total
Rp150.000
```

## Database

Menggunakan tabel:

```text
incomes
income_details
```

### incomes

```text
id
user_id
category_id
date
description
created_at
updated_at
```

### income_details

```text
id
income_id
name
amount
note
created_at
updated_at
```

Relationship:

```text
Income
├── belongsTo User
├── belongsTo Category
└── hasMany IncomeDetail
```

```text
IncomeDetail
└── belongsTo Income
```

### Contoh Data

`incomes`:

```text
id          : 1
user_id     : 1
category_id : 1
date        : 2026-08-16
description : Main badminton mingguan
```

`income_details`:

```text
income_id   name       amount

1           Angga      50000
1           Budi       50000
1           Candra     50000
```

Total:

```text
50000
+
50000
+
50000
=
150000
```

Ditampilkan sebagai:

```text
Rp150.000
```

### Total

Tidak terdapat field:

```text
total
```

pada tabel `incomes`.

Total selalu berasal dari:

```text
SUM(income_details.amount)
```

## Validation

### Date

```text
required
date
```

### Category

```text
required
exists:categories,id
```

Category wajib memiliki:

```text
type = income
```

### Description

```text
nullable
string
max:255
```

### Details

Minimal terdapat:

```text
1 detail
```

### Detail Name

```text
required
string
max:100
```

### Amount

```text
required
integer
min:1
```

Tidak diperbolehkan:

```text
Rp0
```

atau:

```text
-Rp50.000
```

### Note

```text
nullable
string
max:255
```

## Business Rule

### Income Harus Memiliki Detail

Tidak boleh menyimpan:

```text
Iuran Main

Detail:
-
```

Minimal satu detail wajib tersedia.

### Total Otomatis

Total tidak dapat diedit admin.

Contoh:

```text
Angga       Rp30.000
Budi        Rp30.000
Candra      Rp30.000
---------------------
Total       Rp90.000
```

Jika ditambahkan:

```text
Dimas       Rp30.000
```

maka total otomatis menjadi:

```text
Rp120.000
```

### Detail Tidak Harus Member

Field:

```text
name
```

merupakan teks biasa.

Artinya admin dapat mencatat:

```text
Angga
Budi
Donasi Sponsor
Tambahan Kas
Anonymous
```

Tidak terdapat hubungan ke tabel member pada MVP.

### Category

Income hanya dapat menggunakan:

```text
categories.type = income
```

Jika terdapat:

```text
Lapangan
type = expense
```

kategori tersebut tidak boleh digunakan.

### User

`user_id` berasal dari admin yang sedang login.

Admin tidak memilih user pada form.

### Create Transaction

Header dan detail harus disimpan menggunakan database transaction.

```text
BEGIN

Create Income
Create Income Details

COMMIT
```

Jika salah satu detail gagal:

```text
ROLLBACK
```

Income tidak boleh tersimpan sebagian.

### Update Transaction

Ketika transaksi diedit:

```text
Income
   ↓
Update Header
   ↓
Synchronize Details
   ↓
Recalculate Total
```

Admin dapat:

```text
Tambah Detail
Edit Detail
Hapus Detail
```

Contoh sebelum:

```text
Angga       Rp50.000
Budi        Rp50.000
Candra      Rp50.000

Total      Rp150.000
```

Setelah edit:

```text
Angga       Rp50.000
Budi        Rp50.000
Candra      Rp40.000
Dimas       Rp50.000

Total      Rp190.000
```

### Delete Detail

Detail dapat dihapus selama transaksi masih memiliki minimal satu detail.

Jika hanya tersisa satu detail:

```text
Income
└── Angga Rp50.000
```

detail tersebut tidak boleh dihapus dari form edit tanpa menambahkan detail lain.

Alternatifnya admin dapat menghapus seluruh transaksi.

### Delete Income

Ketika income dihapus:

```text
Income
   ↓
Income Details
```

seluruh detail ikut dihapus melalui cascade.

Sebelum delete harus ada konfirmasi.

### Saldo

Setelah income dibuat:

```text
Saldo Kas
+
Total Income
```

Namun saldo tidak disimpan manual.

Saldo akan otomatis berubah karena query saldo memperhitungkan income yang baru.

Contoh:

```text
Saldo Sebelumnya
Rp500.000

Income Baru
Rp150.000

Saldo Sekarang
Rp650.000
```

Jika income dihapus:

```text
Rp650.000
-
Rp150.000
=
Rp500.000
```

## UI

### Income Index

Header:

```text
Pemasukan

Catat dan kelola seluruh uang masuk.

[ + Tambah Pemasukan ]
```

Ringkasan:

```text
┌──────────────────────────┐
│ TOTAL PEMASUKAN          │
│                          │
│ Rp2.500.000              │
└──────────────────────────┘
```

### Filter

```text
Tanggal

[ 01/08/2026 ] - [ 31/08/2026 ]

Kategori

[ Semua Kategori ▼ ]

[ Filter ]
```

### Table

```text
Tanggal       Kategori       Keterangan        Detail       Total

16 Agu 2026   Iuran Main     Main Mingguan     4            Rp200.000
20 Agu 2026   Donasi         Donasi            1             Rp50.000
```

Action:

```text
Detail
Edit
Hapus
```

### Income Form

```text
Tambah Pemasukan

Tanggal
[ 16/08/2026 ]

Kategori
[ Iuran Main ▼ ]

Keterangan
[ Main badminton mingguan ]

Detail Pemasukan

┌─────────────────────────────────────────────┐
│ Nama                Nominal                 │
│ [ Angga ]           [ Rp50.000 ]            │
│                                             │
│ Catatan                                    │
│ [ - ]                                       │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ Nama                Nominal                 │
│ [ Budi ]            [ Rp50.000 ]            │
│                                             │
│ Catatan                                    │
│ [ - ]                                       │
└─────────────────────────────────────────────┘

[ + Tambah Detail ]

──────────────────────────────────────────────

TOTAL
Rp100.000

[ Batal ]                         [ Simpan ]
```

### Dynamic Detail

Ketika:

```text
+ Tambah Detail
```

ditekan, form detail baru muncul tanpa reload halaman.

Admin dapat menghapus baris:

```text
[ Hapus Detail ]
```

### Currency Input

Admin dapat mengetik:

```text
50000
```

UI dapat menampilkan:

```text
Rp50.000
```

Nilai yang disimpan:

```text
50000
```

### Detail Page

```text
Detail Pemasukan

Iuran Main
16 Agustus 2026

Keterangan
Main badminton mingguan

Detail

Angga                           Rp50.000
Budi                            Rp50.000
Candra                          Rp50.000
Dimas                           Rp50.000

────────────────────────────────────────

TOTAL                          Rp200.000

[ Edit ]
```

### Delete Confirmation

```text
Hapus Pemasukan?

Iuran Main
16 Agustus 2026
Rp200.000

Seluruh detail pemasukan juga akan dihapus.

[ Batal ]                [ Hapus ]
```

### Empty State

```text
Belum ada pemasukan.

Mulai catat uang yang masuk ke
NgeBadmintonYuk.

[ + Tambah Pemasukan ]
```

## Livewire Component

Component:

```text
Income
├── IncomeIndex
├── IncomeForm
└── IncomeDetail
```

### IncomeIndex

Bertanggung jawab untuk:

```text
List pemasukan
Filter tanggal
Filter kategori
Total pemasukan
Delete
Pagination
```

State:

```text
startDate
endDate
categoryId
```

### IncomeForm

Digunakan untuk:

```text
Create
Edit
```

State utama:

```text
incomeId
date
categoryId
description
details
```

Contoh `details`:

```text
[
    [
        name   => Angga
        amount => 50000
        note   => null
    ],
    [
        name   => Budi
        amount => 50000
        note   => null
    ]
]
```

Total pada form:

```text
SUM(details.amount)
```

### IncomeDetail

Bertanggung jawab menampilkan satu transaksi lengkap beserta seluruh detailnya.

Tidak melakukan perubahan data kecuali menyediakan action menuju edit.

## Service

Service:

```text
IncomeService
```

Tanggung jawab:

```text
Create Income
Update Income
Delete Income
Calculate Total
```

Create flow:

```text
IncomeForm
    ↓
Validation
    ↓
IncomeService
    ↓
DB Transaction
    ↓
Create Income
    ↓
Create Details
    ↓
Commit
```

Update flow:

```text
IncomeForm
    ↓
IncomeService
    ↓
DB Transaction
    ↓
Update Income
    ↓
Synchronize Details
    ↓
Commit
```

Delete:

```text
IncomeIndex
    ↓
IncomeService
    ↓
Delete Income
    ↓
Cascade Details
```

## Repository

Repository:

```text
IncomeRepository
```

Tanggung jawab:

```text
Get All
Get By ID
Get With Details
Filter By Date
Filter By Category
Create
Update
Delete
Get Total Income
```

Query detail harus menggunakan relationship agar data dapat diambil secara efisien.

Contoh:

```text
Income
├── Category
└── Details
```

Repository tidak menangani validation UI.

## Testing

Testing Income minimal mencakup:

* Guest tidak dapat membuka pemasukan.
* Admin dapat melihat daftar pemasukan.
* Admin dapat membuat pemasukan.
* Tanggal wajib diisi.
* Category wajib diisi.
* Category harus bertipe `income`.
* Income minimal memiliki satu detail.
* Nama detail wajib diisi.
* Nominal wajib diisi.
* Nominal harus integer.
* Nominal minimal `1`.
* Admin dapat menambah beberapa detail.
* Total dihitung dari seluruh detail.
* Total berubah ketika nominal detail berubah.
* Admin dapat menambah detail saat edit.
* Admin dapat mengubah detail saat edit.
* Admin dapat menghapus detail saat edit.
* Income tidak boleh tersimpan tanpa detail.
* Admin dapat melihat detail pemasukan.
* Admin dapat menghapus pemasukan.
* Menghapus pemasukan menghapus seluruh detail.
* Database transaction rollback jika penyimpanan detail gagal.
* Income baru memengaruhi saldo kas.
* Perubahan income memengaruhi saldo kas.
* Penghapusan income memengaruhi saldo kas.
* Filter tanggal bekerja.
* Filter kategori bekerja.
* Total pada index mengikuti filter aktif.

## Future Improvement

Income dapat dikembangkan dengan:

```text
Member
Payment
Payment Status
Payment Method
Bank Account
Proof of Payment
Recurring Income
Import
Export
Receipt
```

Jika member sudah tersedia:

```text
Income Detail
└── member_id
```

Sehingga:

```text
Angga
Rp50.000
Paid
```

dapat terhubung langsung ke member.

Jika terdapat beberapa tempat penyimpanan uang:

```text
Income
└── account_id
```

Contoh:

```text
BRI
Cash
E-Wallet
```

Jika bukti transfer diperlukan:

```text
Income
└── Attachment
```

Fitur tersebut **tidak termasuk scope MVP NgeKas saat ini**.
