# 07-expense.md

# Modul

NgeKas — Expense

## Tujuan

Expense digunakan untuk mencatat seluruh pengeluaran keuangan komunitas **NgeBadmintonYuk**.

Setiap pengeluaran terdiri dari:

```text
Expense
├── Informasi Transaksi
└── Expense Details
```

Satu transaksi pengeluaran dapat memiliki banyak detail.

Contoh:

```text
Keperluan Badminton
16 Agustus 2026

Sewa Court 1       Rp100.000
Sewa Court 2       Rp100.000
Shuttlecock         Rp75.000
Air Mineral         Rp25.000
-----------------------------
Total               Rp300.000
```

Total pengeluaran dihitung otomatis berdasarkan seluruh detail transaksi.

## Scope

Expense MVP mencakup:

```text
Daftar Pengeluaran
Detail Pengeluaran
Tambah Pengeluaran
Edit Pengeluaran
Hapus Pengeluaran

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
Vendor
Purchase Order
Budget
Recurring Expense
Reimbursement
Attachment Nota
Multi Account
Approval
Import Excel
```

## User Story

Sebagai admin, saya ingin mencatat pengeluaran agar seluruh uang yang keluar dapat terdokumentasi.

Sebagai admin, saya ingin memilih kategori pengeluaran agar transaksi dapat dikelompokkan.

Sebagai admin, saya ingin memasukkan beberapa detail dalam satu pengeluaran agar penggunaan uang dapat diketahui secara rinci.

Sebagai admin, saya ingin total dihitung otomatis agar tidak perlu menjumlahkan nominal secara manual.

Sebagai admin, saya ingin mengubah pengeluaran jika terdapat kesalahan pencatatan.

Sebagai admin, saya ingin menghapus pengeluaran yang salah.

Sebagai admin, saya ingin melihat detail pengeluaran agar mengetahui penggunaan uang dalam transaksi tersebut.

## Flow

Flow utama:

```text
Pengeluaran
    ↓
Daftar Pengeluaran
    │
    ├── Tambah
    ├── Detail
    ├── Edit
    └── Hapus
```

Tambah pengeluaran:

```text
Tambah Pengeluaran
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
Lapangan

Keterangan
Keperluan badminton mingguan

Detail

Sewa Court 1       Rp100.000
Sewa Court 2       Rp100.000

Total
Rp200.000
```

## Database

Menggunakan tabel:

```text
expenses
expense_details
```

### expenses

```text
id
user_id
category_id
date
description
created_at
updated_at
```

### expense_details

```text
id
expense_id
name
amount
note
created_at
updated_at
```

Relationship:

```text
Expense
├── belongsTo User
├── belongsTo Category
└── hasMany ExpenseDetail
```

```text
ExpenseDetail
└── belongsTo Expense
```

### Contoh Data

`expenses`:

```text
id          : 1
user_id     : 1
category_id : 4
date        : 2026-08-16
description : Keperluan badminton mingguan
```

`expense_details`:

```text
expense_id    name                amount

1             Sewa Court 1        100000
1             Sewa Court 2        100000
1             Shuttlecock          75000
```

Total:

```text
100000
+
100000
+
75000
=
275000
```

Ditampilkan sebagai:

```text
Rp275.000
```

### Total

Tidak terdapat field:

```text
total
```

pada tabel `expenses`.

Total selalu dihitung dari:

```text
SUM(expense_details.amount)
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
type = expense
```

### Description

```text
nullable
string
max:255
```

### Details

Minimal:

```text
1 detail
```

harus tersedia.

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
-Rp100.000
```

Nominal pengeluaran tetap disimpan sebagai angka positif.

Jenis transaksi `expense` yang menentukan bahwa nominal tersebut merupakan uang keluar.

### Note

```text
nullable
string
max:255
```

## Business Rule

### Expense Harus Memiliki Detail

Tidak diperbolehkan menyimpan:

```text
Pengeluaran

Detail:
-
```

Minimal satu detail harus tersedia.

### Total Otomatis

Total tidak dapat diinput atau diubah manual oleh admin.

Contoh:

```text
Court 1        Rp100.000
Court 2        Rp100.000
Shuttlecock     Rp75.000
-------------------------
Total          Rp275.000
```

Jika ditambahkan:

```text
Air Mineral     Rp25.000
```

total otomatis menjadi:

```text
Rp300.000
```

### Detail Fleksibel

Field:

```text
name
```

merupakan teks biasa.

Contoh:

```text
Sewa Court 1
Sewa Court 2
Shuttlecock
Air Mineral
Parkir
Biaya Admin
```

Tidak diperlukan tabel item atau produk pada MVP.

### Category

Expense hanya dapat menggunakan:

```text
categories.type = expense
```

Kategori seperti:

```text
Iuran Main
type = income
```

tidak boleh digunakan pada pengeluaran.

### User

`user_id` otomatis menggunakan admin yang sedang login.

Admin tidak memilih user secara manual.

### Nominal Disimpan Positif

Contoh pengeluaran:

```text
Rp100.000
```

disimpan:

```text
100000
```

bukan:

```text
-100000
```

Pengurangan hanya dilakukan saat menghitung saldo:

```text
Income - Expense
```

### Create Transaction

Header dan detail disimpan menggunakan database transaction.

```text
BEGIN

Create Expense
Create Expense Details

COMMIT
```

Jika salah satu proses gagal:

```text
ROLLBACK
```

Tidak boleh terjadi:

```text
Expense berhasil dibuat
tetapi
Expense Details gagal dibuat
```

### Update Transaction

Admin dapat mengubah:

```text
Tanggal
Kategori
Keterangan
Detail
```

Pada detail admin dapat:

```text
Tambah
Edit
Hapus
```

Contoh sebelum edit:

```text
Court 1        Rp100.000
Court 2        Rp100.000
Shuttlecock     Rp75.000

Total          Rp275.000
```

Setelah edit:

```text
Court 1        Rp100.000
Court 2        Rp100.000
Shuttlecock     Rp80.000
Air Mineral     Rp25.000

Total          Rp305.000
```

Total mengikuti detail terbaru.

### Delete Detail

Detail dapat dihapus selama minimal satu detail tetap tersedia.

Contoh:

```text
Expense
└── Sewa Lapangan Rp200.000
```

Jika hanya terdapat satu detail, detail tersebut tidak boleh dihapus tanpa menambahkan detail lain.

Jika seluruh transaksi memang tidak diperlukan, admin dapat menghapus Expense.

### Delete Expense

Ketika Expense dihapus:

```text
Expense
   ↓
Expense Details
```

seluruh detail terkait ikut dihapus melalui cascade.

Sebelum penghapusan harus ada confirmation.

### Saldo

Expense otomatis memengaruhi saldo.

Contoh:

```text
Saldo Sebelumnya
Rp1.000.000

Expense
Rp300.000

Saldo Sekarang
Rp700.000
```

Tidak terdapat proses manual:

```text
Update Balance
```

Saldo berubah karena query saldo menghitung ulang:

```text
Total Income
-
Total Expense
```

### Saldo Negatif

Untuk MVP, sistem **tetap mengizinkan saldo menjadi negatif**.

Contoh:

```text
Saldo
Rp100.000

Pengeluaran
Rp150.000

Saldo Baru
- Rp50.000
```

Hal ini diperlukan agar NgeKas tetap merepresentasikan kondisi pencatatan sebenarnya.

Sistem tidak menolak pengeluaran hanya karena saldo tidak mencukupi.

## UI

### Expense Index

Header:

```text
Pengeluaran

Catat dan kelola seluruh uang keluar.

[ + Tambah Pengeluaran ]
```

Ringkasan:

```text
┌──────────────────────────┐
│ TOTAL PENGELUARAN        │
│                          │
│ Rp1.750.000              │
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
Tanggal       Kategori       Keterangan          Detail    Total

16 Agu 2026   Lapangan       Main Mingguan       2         Rp200.000
16 Agu 2026   Shuttlecock    Shuttlecock          1          Rp75.000
20 Agu 2026   Konsumsi       Air Mineral          1          Rp25.000
```

Action:

```text
Detail
Edit
Hapus
```

### Expense Form

```text
Tambah Pengeluaran

Tanggal
[ 16/08/2026 ]

Kategori
[ Lapangan ▼ ]

Keterangan
[ Keperluan badminton mingguan ]

Detail Pengeluaran

┌─────────────────────────────────────────────┐
│ Nama                Nominal                 │
│ [ Sewa Court 1 ]    [ Rp100.000 ]           │
│                                             │
│ Catatan                                    │
│ [ - ]                                       │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ Nama                Nominal                 │
│ [ Sewa Court 2 ]    [ Rp100.000 ]           │
│                                             │
│ Catatan                                    │
│ [ - ]                                       │
└─────────────────────────────────────────────┘

[ + Tambah Detail ]

──────────────────────────────────────────────

TOTAL
Rp200.000

[ Batal ]                         [ Simpan ]
```

### Dynamic Detail

Button:

```text
+ Tambah Detail
```

menambahkan detail baru tanpa reload halaman.

Setiap detail memiliki action:

```text
Hapus
```

selama business rule minimal satu detail terpenuhi.

### Currency Input

Admin dapat memasukkan:

```text
100000
```

UI menampilkan:

```text
Rp100.000
```

Database menyimpan:

```text
100000
```

### Detail Page

```text
Detail Pengeluaran

Lapangan
16 Agustus 2026

Keterangan
Keperluan badminton mingguan

Detail

Sewa Court 1                   Rp100.000
Sewa Court 2                   Rp100.000
Shuttlecock                     Rp75.000
Air Mineral                     Rp25.000

────────────────────────────────────────

TOTAL                          Rp300.000

[ Edit ]
```

### Delete Confirmation

```text
Hapus Pengeluaran?

Lapangan
16 Agustus 2026
Rp300.000

Seluruh detail pengeluaran juga akan dihapus.

[ Batal ]                [ Hapus ]
```

### Empty State

```text
Belum ada pengeluaran.

Pengeluaran NgeBadmintonYuk
akan muncul di sini.

[ + Tambah Pengeluaran ]
```

## Livewire Component

Component:

```text
Expense
├── ExpenseIndex
├── ExpenseForm
└── ExpenseDetail
```

### ExpenseIndex

Bertanggung jawab untuk:

```text
List pengeluaran
Filter tanggal
Filter kategori
Total pengeluaran
Delete
Pagination
```

State:

```text
startDate
endDate
categoryId
```

### ExpenseForm

Digunakan untuk:

```text
Create
Edit
```

State utama:

```text
expenseId
date
categoryId
description
details
```

Contoh `details`:

```text
[
    [
        name   => Sewa Court 1
        amount => 100000
        note   => null
    ],
    [
        name   => Sewa Court 2
        amount => 100000
        note   => null
    ]
]
```

Total:

```text
SUM(details.amount)
```

Total diperbarui secara reactive ketika nominal detail berubah.

### ExpenseDetail

Bertanggung jawab menampilkan:

```text
Tanggal
Kategori
Keterangan
Detail
Total
```

Component bersifat read-only.

## Service

Service:

```text
ExpenseService
```

Tanggung jawab:

```text
Create Expense
Update Expense
Delete Expense
Calculate Total
```

Create:

```text
ExpenseForm
    ↓
Validation
    ↓
ExpenseService
    ↓
DB Transaction
    ↓
Create Expense
    ↓
Create Expense Details
    ↓
Commit
```

Update:

```text
ExpenseForm
    ↓
ExpenseService
    ↓
DB Transaction
    ↓
Update Expense
    ↓
Synchronize Details
    ↓
Commit
```

Delete:

```text
ExpenseIndex
    ↓
ExpenseService
    ↓
Delete Expense
    ↓
Cascade Expense Details
```

## Repository

Repository:

```text
ExpenseRepository
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
Get Total Expense
```

Query detail menggunakan relationship:

```text
Expense
├── Category
└── Details
```

Repository tidak menangani validation UI atau formatting Rupiah.

## Testing

Testing Expense minimal mencakup:

* Guest tidak dapat membuka pengeluaran.
* Admin dapat melihat daftar pengeluaran.
* Admin dapat membuat pengeluaran.
* Tanggal wajib diisi.
* Category wajib diisi.
* Category harus bertipe `expense`.
* Category bertipe `income` tidak dapat digunakan.
* Expense minimal memiliki satu detail.
* Nama detail wajib diisi.
* Nominal wajib diisi.
* Nominal harus integer.
* Nominal minimal `1`.
* Nominal disimpan sebagai nilai positif.
* Admin dapat menambahkan beberapa detail.
* Total dihitung berdasarkan seluruh detail.
* Total berubah ketika nominal detail berubah.
* Admin dapat melihat detail pengeluaran.
* Admin dapat mengubah header pengeluaran.
* Admin dapat menambah detail ketika edit.
* Admin dapat mengubah detail ketika edit.
* Admin dapat menghapus detail ketika edit.
* Expense tidak dapat disimpan tanpa detail.
* Admin dapat menghapus Expense.
* Menghapus Expense menghapus seluruh detail.
* Database transaction rollback jika penyimpanan detail gagal.
* Expense baru mengurangi saldo.
* Perubahan Expense memengaruhi saldo.
* Penghapusan Expense memengaruhi saldo.
* Expense tetap dapat disimpan ketika menyebabkan saldo negatif.
* Filter tanggal bekerja.
* Filter kategori bekerja.
* Total pada index mengikuti filter aktif.

## Future Improvement

Expense dapat dikembangkan dengan:

```text
Vendor
Payment Method
Bank Account
Attachment Nota
Recurring Expense
Budget
Approval
Reimbursement
Import
Export
```

Jika terdapat beberapa tempat penyimpanan uang:

```text
Expense
└── account_id
```

Contoh:

```text
BRI
Cash
E-Wallet
```

Jika bukti transaksi diperlukan:

```text
Expense
└── Attachment
```

Contoh:

```text
Nota Lapangan.jpg
Struk Shuttlecock.jpg
```

Jika budgeting mulai diperlukan:

```text
Budget
├── Category
├── Period
└── Amount
```

Sehingga NgeKas dapat membandingkan:

```text
Budget Lapangan
Rp1.000.000

Pengeluaran Aktual
Rp750.000

Sisa Budget
Rp250.000
```

Fitur tersebut **tidak termasuk scope MVP NgeKas saat ini**.
