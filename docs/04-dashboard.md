# 04-dashboard.md

# Modul

NgeKas — Dashboard

## Tujuan

Dashboard menjadi halaman utama setelah admin login ke NgeKas.

Dashboard memberikan ringkasan kondisi keuangan komunitas **NgeBadmintonYuk** tanpa admin harus membuka seluruh transaksi satu per satu.

Informasi utama yang ditampilkan:

* Saldo kas saat ini
* Total pemasukan
* Total pengeluaran
* Ringkasan bulan berjalan
* Transaksi terbaru
* Akses cepat untuk mencatat transaksi

## Scope

Dashboard MVP mencakup:

```text
Saldo Kas

Bulan Ini
├── Total Pemasukan
├── Total Pengeluaran
└── Selisih

Transaksi Terbaru

Quick Action
├── Tambah Pemasukan
└── Tambah Pengeluaran
```

Dashboard belum mencakup:

```text
Chart kompleks
Cash flow prediction
Budget
Target keuangan
Perbandingan tahunan
Analytics
Ranking
Activity
Member
Payment status
```

## User Story

Sebagai admin, saya ingin melihat saldo kas saat ini agar langsung mengetahui jumlah uang komunitas yang tersedia.

Sebagai admin, saya ingin melihat total pemasukan dan pengeluaran bulan berjalan agar dapat mengetahui kondisi keuangan terbaru.

Sebagai admin, saya ingin melihat transaksi terbaru agar dapat mengecek pencatatan terakhir dengan cepat.

Sebagai admin, saya ingin dapat langsung menambah pemasukan atau pengeluaran dari dashboard agar proses pencatatan lebih cepat.

## Flow

Setelah login:

```text
Login
  ↓
Dashboard
  ↓
Ringkasan Keuangan
```

Dashboard mengambil:

```text
Seluruh Transaksi
        ↓
   Saldo Kas

Transaksi Bulan Ini
        ↓
Pemasukan + Pengeluaran

Transaksi Terbaru
        ↓
   Recent List
```

Admin dapat melakukan:

```text
Dashboard
   │
   ├── Tambah Pemasukan
   │
   ├── Tambah Pengeluaran
   │
   ├── Lihat Pemasukan
   │
   ├── Lihat Pengeluaran
   │
   └── Lihat Laporan
```

## Database

Dashboard tidak memiliki tabel khusus.

Data berasal dari:

```text
incomes
income_details

expenses
expense_details
```

### Saldo Kas

Saldo dihitung dari seluruh transaksi:

```text
SUM(income_details.amount)
-
SUM(expense_details.amount)
=
Saldo Kas
```

Contoh:

```text
Total Pemasukan
Rp2.500.000

Total Pengeluaran
Rp1.750.000

Saldo Kas
Rp750.000
```

### Pemasukan Bulan Ini

Data menggunakan tanggal pada:

```text
incomes.date
```

Filter:

```text
Awal Bulan
sampai
Akhir Bulan
```

Total berasal dari:

```text
SUM(income_details.amount)
```

yang terkait dengan transaksi pada periode tersebut.

### Pengeluaran Bulan Ini

Data menggunakan:

```text
expenses.date
```

Total berasal dari:

```text
SUM(expense_details.amount)
```

pada bulan berjalan.

### Selisih Bulan Ini

```text
Pemasukan Bulan Ini
-
Pengeluaran Bulan Ini
=
Selisih
```

Contoh:

```text
Pemasukan
Rp1.000.000

Pengeluaran
Rp750.000

Selisih
+ Rp250.000
```

Jika pengeluaran lebih besar:

```text
Pemasukan
Rp500.000

Pengeluaran
Rp700.000

Selisih
- Rp200.000
```

### Transaksi Terbaru

Mengambil transaksi pemasukan dan pengeluaran terbaru.

Contoh:

```text
16 Agu 2026
Iuran Main
+ Rp500.000

16 Agu 2026
Sewa Lapangan
- Rp300.000

14 Agu 2026
Shuttlecock
- Rp100.000
```

Jumlah transaksi terbaru MVP:

```text
5 transaksi
```

## Validation

Dashboard tidak memiliki form utama sehingga tidak membutuhkan validation input.

Data yang ditampilkan harus:

* Berasal dari transaksi valid.
* Menggunakan tanggal transaksi.
* Menggunakan total detail transaksi.
* Menggunakan format Rupiah.
* Tidak menggunakan total manual.
* Tidak menghitung transaksi di luar periode untuk ringkasan bulan berjalan.

Jika belum terdapat transaksi:

```text
Pemasukan
Rp0

Pengeluaran
Rp0

Saldo
Rp0
```

Dashboard tidak boleh menghasilkan error ketika database transaksi masih kosong.

## Business Rule

### Saldo Bersifat Global

Saldo kas merupakan seluruh pemasukan dikurangi seluruh pengeluaran.

Saldo tidak dibatasi bulan berjalan.

Contoh:

```text
Juli
Saldo akhir Rp300.000

Agustus
Pemasukan Rp500.000
Pengeluaran Rp200.000

Saldo sekarang
Rp600.000
```

Dashboard harus menampilkan:

```text
Rp600.000
```

bukan hanya:

```text
Rp300.000
```

dari selisih transaksi bulan Agustus.

### Ringkasan Bulanan

Pemasukan dan pengeluaran pada card bulan berjalan hanya menghitung transaksi pada bulan tersebut.

Contoh:

```text
Agustus 2026

Pemasukan
Rp500.000

Pengeluaran
Rp200.000

Selisih
+ Rp300.000
```

### Total Berdasarkan Detail

Dashboard tidak mengambil total manual dari header transaksi.

Pemasukan:

```text
Income
   ↓
Income Details
   ↓
SUM(amount)
```

Pengeluaran:

```text
Expense
   ↓
Expense Details
   ↓
SUM(amount)
```

### Transaksi Terbaru

Pemasukan dan pengeluaran digabungkan secara kronologis.

Urutan:

```text
Tanggal terbaru
↓
Tanggal terlama
```

Jika transaksi memiliki tanggal yang sama, transaksi terbaru yang dibuat dapat ditampilkan lebih dahulu.

### Nilai Positif dan Negatif

Pemasukan ditampilkan:

```text
+ Rp500.000
```

Pengeluaran:

```text
- Rp300.000
```

Saldo tidak perlu menggunakan simbol `+`.

```text
Rp750.000
```

Jika saldo negatif:

```text
- Rp100.000
```

### Dashboard Read Only

Dashboard hanya menampilkan ringkasan.

Perubahan transaksi tetap dilakukan melalui modul:

```text
Pemasukan
Pengeluaran
```

Quick Action hanya mengarahkan admin ke form transaksi.

## UI

Dashboard mengikuti branding NgeBadmintonYuk.

### Header

```text
Dashboard

Ringkasan keuangan NgeBadmintonYuk
Agustus 2026
```

### Saldo Utama

Saldo menjadi informasi paling dominan.

```text
┌──────────────────────────────────────┐
│ SALDO KAS                            │
│                                      │
│ Rp1.250.000                          │
│                                      │
│ Total kas NgeBadmintonYuk saat ini   │
└──────────────────────────────────────┘
```

Card saldo dapat menggunakan warna utama:

```text
#2455F5
```

dengan teks:

```text
White
```

Accent kecil dapat menggunakan:

```text
#FFD23F
```

### Ringkasan Bulan Ini

```text
BULAN INI

┌─────────────────┐
│ PEMASUKAN       │
│                 │
│ Rp1.500.000     │
└─────────────────┘

┌─────────────────┐
│ PENGELUARAN     │
│                 │
│ Rp750.000       │
└─────────────────┘

┌─────────────────┐
│ SELISIH         │
│                 │
│ + Rp750.000     │
└─────────────────┘
```

### Quick Action

```text
[ + Pemasukan ]    [ + Pengeluaran ]
```

`Tambah Pemasukan` menggunakan primary action.

`Tambah Pengeluaran` tetap mudah ditemukan tetapi tidak perlu menggunakan warna merah sebagai warna button utama.

### Transaksi Terbaru

```text
Transaksi Terbaru

16 Agu 2026
Iuran Main
3 detail
                         + Rp150.000

15 Agu 2026
Sewa Lapangan
2 detail
                         - Rp200.000

14 Agu 2026
Shuttlecock
1 detail
                          - Rp75.000

                    Lihat Semua →
```

Setiap transaksi menampilkan minimal:

```text
Tanggal
Kategori
Jumlah Detail
Total
Type
```

Keterangan dapat ditampilkan jika ruang tersedia.

### Empty State

Jika belum ada transaksi:

```text
Belum ada transaksi.

Mulai catat keuangan NgeBadmintonYuk.

[ + Tambah Pemasukan ]
```

### Mobile

Pada mobile, card ditampilkan vertikal.

```text
SALDO KAS
Rp1.250.000

PEMASUKAN
Rp1.500.000

PENGELUARAN
Rp750.000

SELISIH
Rp750.000
```

Quick action tetap mudah dijangkau.

## Livewire Component

Component utama:

```text
Dashboard
```

Dashboard bertanggung jawab untuk menampilkan:

```text
balance
monthlyIncome
monthlyExpense
monthlyDifference
recentTransactions
```

Component tidak menyimpan data transaksi.

Flow:

```text
Dashboard Component
        ↓
Dashboard Service
        ↓
Repository
        ↓
Database
```

Ketika halaman dibuka:

```text
mount()
  ↓
Load Summary
  ↓
Load Recent Transactions
```

Dashboard dapat menggunakan computed property jika sesuai dengan implementasi Livewire.

## Service

Service:

```text
DashboardService
```

Tanggung jawab:

* Mengambil saldo kas.
* Mengambil pemasukan bulan berjalan.
* Mengambil pengeluaran bulan berjalan.
* Menghitung selisih bulan berjalan.
* Mengambil transaksi terbaru.

Contoh hasil:

```text
DashboardSummary

balance
1250000

monthly_income
1500000

monthly_expense
750000

monthly_difference
750000
```

Service tidak melakukan perubahan data.

## Repository

Dashboard dapat menggunakan:

```text
IncomeRepository
ExpenseRepository
```

untuk query dasar.

Jika query dashboard mulai banyak, dapat menggunakan:

```text
DashboardRepository
```

Repository menangani:

```text
Total Income
Total Expense
Monthly Income
Monthly Expense
Recent Income
Recent Expense
```

Penggabungan data menjadi informasi dashboard dilakukan pada Service.

Flow:

```text
Dashboard
    ↓
DashboardService
    ↓
IncomeRepository
ExpenseRepository
    ↓
Database
```

## Testing

Testing dashboard minimal mencakup:

* Guest tidak dapat membuka dashboard.
* Admin dapat membuka dashboard setelah login.
* Dashboard menampilkan saldo kas.
* Saldo sesuai total pemasukan dikurangi total pengeluaran.
* Dashboard menampilkan pemasukan bulan berjalan.
* Dashboard menampilkan pengeluaran bulan berjalan.
* Selisih bulan berjalan dihitung dengan benar.
* Saldo tetap memperhitungkan transaksi bulan sebelumnya.
* Transaksi terbaru ditampilkan.
* Transaksi terbaru menggabungkan pemasukan dan pengeluaran.
* Transaksi diurutkan dari yang terbaru.
* Total transaksi berasal dari detail.
* Pemasukan ditampilkan sebagai nilai positif.
* Pengeluaran ditampilkan sebagai nilai negatif.
* Dashboard tetap dapat dibuka ketika belum ada transaksi.
* Nilai kosong ditampilkan sebagai `Rp0`.
* Quick Action mengarah ke form yang benar.

## Future Improvement

Dashboard dapat dikembangkan dengan:

```text
Cash Flow Chart
Monthly Comparison
Category Breakdown
Income vs Expense Chart
Yearly Summary
Budget
Financial Target
Account Balance
Activity Summary
Member Payment Summary
```

Contoh visualisasi masa depan:

```text
Pemasukan vs Pengeluaran

Jan  ████████
Feb  ██████████
Mar  ███████
Apr  ███████████
```

Dashboard juga dapat menyediakan filter:

```text
Bulan
Tahun
Custom Period
```

Fitur tersebut **tidak termasuk scope MVP NgeKas saat ini**.
