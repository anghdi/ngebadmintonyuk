# 05-category.md

# Modul

NgeKas — Category

## Tujuan

Category digunakan untuk mengelompokkan transaksi pemasukan dan pengeluaran agar pencatatan keuangan NgeKas lebih terstruktur.

Contoh kategori:

```text
Pemasukan
├── Iuran Main
├── Donasi
└── Lainnya

Pengeluaran
├── Lapangan
├── Shuttlecock
├── Konsumsi
└── Lainnya
```

Kategori akan digunakan pada:

* Pemasukan
* Pengeluaran
* Dashboard
* Laporan

## Scope

Category MVP mencakup:

```text
Daftar Kategori
Tambah Kategori
Edit Kategori
Hapus Kategori

Type
├── Income
└── Expense
```

Tidak termasuk:

```text
Sub Category
Category Icon
Category Color
Budget per Category
Multi Level Category
```

## User Story

Sebagai admin, saya ingin membuat kategori pemasukan agar setiap pemasukan dapat dikelompokkan.

Sebagai admin, saya ingin membuat kategori pengeluaran agar setiap pengeluaran dapat dikelompokkan.

Sebagai admin, saya ingin mengubah nama kategori jika terdapat kesalahan atau perubahan kebutuhan.

Sebagai admin, saya ingin menghapus kategori yang tidak digunakan agar daftar kategori tetap rapi.

Sebagai admin, saya ingin kategori pemasukan dan pengeluaran dipisahkan agar tidak salah memilih kategori saat mencatat transaksi.

## Flow

Flow category:

```text
Login
  ↓
Kategori
  ↓
Daftar Kategori
  │
  ├── Tambah
  ├── Edit
  └── Hapus
```

Tambah kategori:

```text
Tambah Kategori
      ↓
Nama Kategori
      ↓
Type
      ↓
Validasi
      ↓
Simpan
```

Contoh:

```text
Nama
Iuran Main

Type
Pemasukan
```

Hasil:

```text
Iuran Main
income
```

## Database

Menggunakan tabel:

```text
categories
```

Struktur:

```text
categories

id
name
type
created_at
updated_at
```

### name

Menyimpan nama kategori.

Contoh:

```text
Iuran Main
Donasi
Lapangan
Shuttlecock
Konsumsi
```

### type

Menentukan jenis transaksi.

Nilai yang diperbolehkan:

```text
income
expense
```

Mapping pada UI:

```text
income
→ Pemasukan

expense
→ Pengeluaran
```

### Relationship

Category memiliki relasi:

```text
Category
 │
 ├── hasMany → Income
 │
 └── hasMany → Expense
```

Namun penggunaannya mengikuti `type`.

```text
Category
type = income
    ↓
Income
```

```text
Category
type = expense
    ↓
Expense
```

Kategori `income` tidak digunakan pada `expense`.

Kategori `expense` tidak digunakan pada `income`.

## Validation

### Name

```text
required
string
max:100
```

Nama kategori tidak boleh kosong.

Contoh valid:

```text
Iuran Main
```

Contoh tidak valid:

```text
""
```

### Type

```text
required
in:income,expense
```

Nilai selain:

```text
income
expense
```

tidak diperbolehkan.

### Duplicate

Nama kategori tidak boleh duplikat pada type yang sama.

Tidak diperbolehkan:

```text
Iuran Main
income

Iuran Main
income
```

Namun secara sistem dapat diperbolehkan jika type berbeda:

```text
Lainnya
income

Lainnya
expense
```

Karena keduanya memiliki fungsi berbeda.

## Business Rule

### Category Type

Setiap kategori wajib memiliki satu type.

```text
Category
├── income
atau
└── expense
```

Kategori tidak dapat memiliki kedua type sekaligus.

### Penggunaan pada Pemasukan

Form pemasukan hanya menampilkan:

```text
categories.type = income
```

Contoh:

```text
Kategori

[ Iuran Main ▼ ]

Iuran Main
Donasi
Lainnya
```

Kategori pengeluaran tidak muncul.

### Penggunaan pada Pengeluaran

Form pengeluaran hanya menampilkan:

```text
categories.type = expense
```

Contoh:

```text
Kategori

[ Lapangan ▼ ]

Lapangan
Shuttlecock
Konsumsi
Lainnya
```

Kategori pemasukan tidak muncul.

### Edit Category

Admin dapat mengubah:

```text
name
```

Contoh:

```text
Sewa Lapangan
↓
Lapangan
```

Untuk MVP, `type` kategori yang sudah digunakan pada transaksi **tidak boleh diubah**.

Contoh:

```text
Lapangan
expense

Sudah digunakan pada Expense
```

Tidak boleh diubah menjadi:

```text
Lapangan
income
```

Tujuannya agar data transaksi lama tetap konsisten.

Jika kategori belum pernah digunakan, `type` masih dapat diubah.

### Delete Category

Kategori yang belum digunakan dapat dihapus.

```text
Category
    ↓
Tidak memiliki transaksi
    ↓
Boleh Dihapus
```

Kategori yang sudah digunakan:

```text
Category
    ↓
Memiliki Income / Expense
    ↓
Tidak Boleh Dihapus
```

Sistem menampilkan informasi:

```text
Kategori tidak dapat dihapus karena sudah digunakan pada transaksi.
```

Transaksi tidak boleh ikut terhapus ketika kategori dihapus.

### Default Category

Seeder dapat menyediakan kategori awal.

Pemasukan:

```text
Iuran Main
Donasi
Lainnya
```

Pengeluaran:

```text
Lapangan
Shuttlecock
Konsumsi
Lainnya
```

Kategori tersebut tetap dapat disesuaikan oleh admin selama mengikuti business rule.

## UI

Halaman:

```text
Kategori
```

### Header

```text
Kategori

Kelola kategori pemasukan dan pengeluaran.

[ + Tambah Kategori ]
```

### Filter Type

Admin dapat memilih:

```text
[ Semua ] [ Pemasukan ] [ Pengeluaran ]
```

Default:

```text
Semua
```

### Table

Contoh:

```text
Kategori                 Type             Action

Iuran Main               Pemasukan        Edit  Hapus
Donasi                    Pemasukan        Edit  Hapus
Lapangan                  Pengeluaran      Edit  Hapus
Shuttlecock               Pengeluaran      Edit  Hapus
Konsumsi                  Pengeluaran      Edit  Hapus
```

Type dapat menggunakan badge.

```text
Pemasukan
```

dan:

```text
Pengeluaran
```

### Form

Tambah kategori:

```text
Tambah Kategori

Nama
[ Iuran Main                 ]

Type
[ Pemasukan                ▼ ]

[ Batal ]       [ Simpan ]
```

Edit kategori:

```text
Edit Kategori

Nama
[ Iuran Main                 ]

Type
[ Pemasukan                ▼ ]

[ Batal ]       [ Simpan ]
```

Jika kategori sudah digunakan:

```text
Type
[ Pemasukan ]

Type tidak dapat diubah karena kategori
sudah digunakan pada transaksi.
```

### Delete Confirmation

Sebelum menghapus:

```text
Hapus Kategori?

Kategori "Donasi" akan dihapus.

[ Batal ]       [ Hapus ]
```

Jika sudah digunakan:

```text
Kategori tidak dapat dihapus karena
sudah digunakan pada transaksi.
```

### Empty State

Jika belum ada kategori:

```text
Belum ada kategori.

Tambahkan kategori untuk mulai
mencatat transaksi.

[ + Tambah Kategori ]
```

## Livewire Component

Component:

```text
Category
├── CategoryIndex
└── CategoryForm
```

### CategoryIndex

Bertanggung jawab untuk:

```text
Menampilkan kategori
Filter berdasarkan type
Delete kategori
Konfirmasi delete
```

State:

```text
typeFilter
```

### CategoryForm

Digunakan untuk:

```text
Create
Edit
```

State utama:

```text
categoryId
name
type
```

Flow create:

```text
CategoryForm
    ↓
Validation
    ↓
CategoryService
    ↓
Repository
    ↓
Database
```

Flow edit:

```text
Category
   ↓
Load Data
   ↓
CategoryForm
   ↓
Validation
   ↓
Update
```

## Service

Service:

```text
CategoryService
```

Tanggung jawab:

* Create category
* Update category
* Delete category
* Memastikan type tidak diubah jika sudah digunakan
* Memastikan kategori yang digunakan tidak dihapus

Contoh flow delete:

```text
Delete Category
      ↓
Check Usage
      ↓
Used?
├── Ya
│   └── Reject
│
└── Tidak
    └── Delete
```

Business rule tidak ditempatkan seluruhnya pada Livewire Component.

## Repository

Repository:

```text
CategoryRepository
```

Tanggung jawab:

```text
Get All
Get By ID
Get Income Categories
Get Expense Categories
Check Category Usage
Create
Update
Delete
```

Contoh:

```text
IncomeForm
    ↓
CategoryRepository
    ↓
Get Income Categories
    ↓
categories.type = income
```

Expense:

```text
ExpenseForm
    ↓
CategoryRepository
    ↓
Get Expense Categories
    ↓
categories.type = expense
```

## Testing

Testing Category minimal mencakup:

* Admin dapat melihat daftar kategori.
* Guest tidak dapat membuka halaman kategori.
* Admin dapat membuat kategori pemasukan.
* Admin dapat membuat kategori pengeluaran.
* Nama kategori wajib diisi.
* Type wajib diisi.
* Type hanya menerima `income` atau `expense`.
* Nama kategori tidak dapat duplikat pada type yang sama.
* Nama kategori yang sama dapat digunakan pada type berbeda.
* Admin dapat mengubah nama kategori.
* Admin dapat mengubah type kategori yang belum digunakan.
* Type kategori yang sudah digunakan tidak dapat diubah.
* Kategori yang belum digunakan dapat dihapus.
* Kategori yang sudah digunakan tidak dapat dihapus.
* Menghapus kategori tidak menghapus transaksi.
* Form pemasukan hanya mendapatkan kategori `income`.
* Form pengeluaran hanya mendapatkan kategori `expense`.
* Filter kategori bekerja sesuai type.

## Future Improvement

Category dapat dikembangkan dengan:

```text
Sub Category
Category Icon
Category Color
Category Status
Custom Ordering
Budget
```

Contoh:

```text
Operasional
├── Lapangan
├── Shuttlecock
└── Konsumsi
```

Kategori juga dapat memiliki status:

```text
active
inactive
```

Sehingga kategori lama yang sudah digunakan tidak perlu dihapus.

Untuk MVP, fitur tersebut belum diperlukan.
