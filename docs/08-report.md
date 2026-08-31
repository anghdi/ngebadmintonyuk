# 08-report.md

# Modul

NgeKas — Report

## Tujuan

Report digunakan untuk melihat ringkasan keuangan komunitas **NgeBadmintonYuk** berdasarkan periode tertentu.

Report membantu admin mengetahui:

* Total pemasukan
* Total pengeluaran
* Selisih pemasukan dan pengeluaran
* Saldo kas
* Detail transaksi dalam periode
* Ringkasan berdasarkan kategori

Report tidak menyimpan data baru.

Seluruh informasi berasal dari transaksi pemasukan dan pengeluaran yang sudah tercatat.

## Scope

Report MVP mencakup:

```text id="rpt001"
Filter Periode

Ringkasan
├── Total Pemasukan
├── Total Pengeluaran
├── Selisih
└── Saldo Kas

Detail
├── Daftar Pemasukan
└── Daftar Pengeluaran

Ringkasan Kategori
├── Pemasukan per Kategori
└── Pengeluaran per Kategori
```

Tidak termasuk:

```text id="rpt002"
Export PDF
Export Excel
Chart Kompleks
Budget Comparison
Yearly Analytics
Cash Flow Projection
Laporan Akuntansi Formal
Neraca
Laba Rugi
Jurnal
```

## User Story

Sebagai admin, saya ingin memilih periode laporan agar dapat melihat kondisi keuangan pada rentang tanggal tertentu.

Sebagai admin, saya ingin melihat total pemasukan agar mengetahui jumlah uang yang masuk pada periode tersebut.

Sebagai admin, saya ingin melihat total pengeluaran agar mengetahui jumlah uang yang digunakan pada periode tersebut.

Sebagai admin, saya ingin melihat selisih pemasukan dan pengeluaran agar mengetahui apakah periode tersebut mengalami surplus atau defisit.

Sebagai admin, saya ingin melihat saldo kas agar mengetahui jumlah kas komunitas saat ini.

Sebagai admin, saya ingin melihat transaksi yang membentuk laporan agar angka ringkasan dapat diperiksa kembali.

Sebagai admin, saya ingin melihat ringkasan berdasarkan kategori agar mengetahui sumber pemasukan dan pengeluaran terbesar.

## Flow

Flow utama:

```text id="rpt003"
Laporan
   ↓
Pilih Periode
   ↓
Filter
   ↓
Ambil Pemasukan
   +
Ambil Pengeluaran
   ↓
Hitung Ringkasan
   ↓
Tampilkan Laporan
```

Contoh:

```text id="rpt004"
Periode

01 Agustus 2026
sampai
31 Agustus 2026
```

Hasil:

```text id="rpt005"
Pemasukan
Rp2.500.000

Pengeluaran
Rp1.750.000

Selisih
+ Rp750.000

Saldo Kas
Rp1.250.000
```

## Database

Report tidak memiliki tabel khusus.

Tidak dibuat:

```text id="rpt006"
reports
report_details
report_summaries
```

Data berasal dari:

```text id="rpt007"
incomes
income_details

expenses
expense_details

categories
```

### Total Pemasukan Periode

Menggunakan:

```text id="rpt008"
incomes.date
```

dengan filter:

```text id="rpt009"
start_date
≤
incomes.date
≤
end_date
```

Nominal berasal dari:

```text id="rpt010"
SUM(income_details.amount)
```

### Total Pengeluaran Periode

Menggunakan:

```text id="rpt011"
expenses.date
```

dengan filter periode yang sama.

Nominal berasal dari:

```text id="rpt012"
SUM(expense_details.amount)
```

### Selisih Periode

```text id="rpt013"
Total Pemasukan Periode
-
Total Pengeluaran Periode
=
Selisih Periode
```

Contoh:

```text id="rpt014"
Pemasukan
Rp1.500.000

Pengeluaran
Rp1.000.000

Selisih
+ Rp500.000
```

Jika pengeluaran lebih besar:

```text id="rpt015"
Pemasukan
Rp800.000

Pengeluaran
Rp1.000.000

Selisih
- Rp200.000
```

### Saldo Kas

Saldo kas berbeda dengan selisih periode.

Saldo kas menghitung seluruh transaksi sampai tanggal akhir laporan.

```text id="rpt016"
Seluruh Pemasukan
sampai end_date

-

Seluruh Pengeluaran
sampai end_date

=

Saldo Kas
```

Contoh:

```text id="rpt017"
Saldo sebelum Agustus
Rp500.000

Pemasukan Agustus
Rp1.500.000

Pengeluaran Agustus
Rp1.000.000

Saldo per 31 Agustus
Rp1.000.000
```

Sedangkan selisih Agustus:

```text id="rpt018"
Rp500.000
```

Keduanya tidak boleh dianggap sebagai nilai yang sama.

### Ringkasan Kategori

Pemasukan dikelompokkan berdasarkan:

```text id="rpt019"
categories.id
categories.name
```

Contoh:

```text id="rpt020"
Iuran Main       Rp2.000.000
Donasi             Rp400.000
Lainnya             Rp100.000
-----------------------------
Total             Rp2.500.000
```

Pengeluaran:

```text id="rpt021"
Lapangan          Rp1.200.000
Shuttlecock         Rp400.000
Konsumsi             Rp100.000
Lainnya               Rp50.000
------------------------------
Total              Rp1.750.000
```

## Validation

### Start Date

```text id="rpt022"
required
date
```

### End Date

```text id="rpt023"
required
date
after_or_equal:start_date
```

Tidak diperbolehkan:

```text id="rpt024"
Start
31 Agustus 2026

End
01 Agustus 2026
```

### Empty Result

Periode tanpa transaksi tetap valid.

Contoh:

```text id="rpt025"
Pemasukan
Rp0

Pengeluaran
Rp0

Selisih
Rp0
```

Saldo tetap dihitung sampai `end_date`.

## Business Rule

### Default Periode

Ketika halaman laporan pertama kali dibuka, periode default adalah:

```text id="rpt026"
Awal bulan berjalan
sampai
Hari ini
```

Contoh jika hari ini:

```text id="rpt027"
14 Agustus 2026
```

maka:

```text id="rpt028"
01 Agustus 2026
-
14 Agustus 2026
```

### Filter Berdasarkan Tanggal Transaksi

Report menggunakan:

```text id="rpt029"
income.date
expense.date
```

bukan:

```text id="rpt030"
created_at
```

Contoh:

```text id="rpt031"
Transaksi dibuat:
20 Agustus

Tanggal transaksi:
16 Agustus
```

Transaksi dianggap terjadi pada:

```text id="rpt032"
16 Agustus
```

### Total Berdasarkan Detail

Report tidak menggunakan total manual.

Pemasukan:

```text id="rpt033"
Income
   ↓
Income Details
   ↓
SUM(amount)
```

Pengeluaran:

```text id="rpt034"
Expense
   ↓
Expense Details
   ↓
SUM(amount)
```

### Selisih

Selisih hanya menghitung transaksi dalam periode yang dipilih.

```text id="rpt035"
Income Periode
-
Expense Periode
```

### Saldo

Saldo menghitung seluruh transaksi sampai tanggal akhir laporan.

```text id="rpt036"
Income <= end_date
-
Expense <= end_date
```

Dengan demikian admin dapat melihat saldo historis.

Contoh laporan:

```text id="rpt037"
Per 31 Juli
Saldo Rp500.000

Per 31 Agustus
Saldo Rp1.000.000
```

### Transaksi Setelah End Date

Transaksi setelah `end_date` tidak boleh memengaruhi saldo laporan.

Contoh:

```text id="rpt038"
End Date
31 Agustus

Income
5 September
Rp500.000
```

Income tersebut tidak dihitung pada laporan Agustus.

### Kategori

Ringkasan kategori hanya menghitung transaksi dalam periode.

Kategori tanpa transaksi pada periode tidak perlu ditampilkan.

### Saldo Negatif

Report tetap dapat menampilkan saldo negatif.

Contoh:

```text id="rpt039"
Saldo Kas

- Rp150.000
```

Tidak terdapat manipulasi nilai agar saldo menjadi `Rp0`.

## UI

### Header

```text id="rpt040"
Laporan

Lihat ringkasan keuangan NgeBadmintonYuk
berdasarkan periode.
```

### Filter

```text id="rpt041"
Periode

Dari
[ 01/08/2026 ]

Sampai
[ 31/08/2026 ]

[ Tampilkan ]
```

### Summary

```text id="rpt042"
┌─────────────────────┐
│ PEMASUKAN           │
│                     │
│ Rp2.500.000         │
└─────────────────────┘

┌─────────────────────┐
│ PENGELUARAN         │
│                     │
│ Rp1.750.000         │
└─────────────────────┘

┌─────────────────────┐
│ SELISIH             │
│                     │
│ + Rp750.000         │
└─────────────────────┘

┌─────────────────────┐
│ SALDO KAS           │
│                     │
│ Rp1.250.000         │
└─────────────────────┘
```

### Ringkasan Kategori

```text id="rpt043"
Pemasukan per Kategori

Iuran Main                 Rp2.000.000
Donasi                       Rp400.000
Lainnya                      Rp100.000

──────────────────────────────────────
Total                      Rp2.500.000
```

Pengeluaran:

```text id="rpt044"
Pengeluaran per Kategori

Lapangan                   Rp1.200.000
Shuttlecock                  Rp400.000
Konsumsi                     Rp100.000
Lainnya                       Rp50.000

──────────────────────────────────────
Total                      Rp1.750.000
```

### Daftar Pemasukan

```text id="rpt045"
Pemasukan

Tanggal       Kategori       Keterangan       Total

05 Agu 2026   Iuran Main     Main Mingguan    Rp500.000
12 Agu 2026   Iuran Main     Main Mingguan    Rp600.000
20 Agu 2026   Donasi         Donasi           Rp400.000
```

Admin dapat membuka transaksi untuk melihat detail.

### Daftar Pengeluaran

```text id="rpt046"
Pengeluaran

Tanggal       Kategori       Keterangan       Total

05 Agu 2026   Lapangan       Sewa Court       Rp300.000
05 Agu 2026   Shuttlecock    Shuttlecock      Rp100.000
12 Agu 2026   Lapangan       Sewa Court       Rp300.000
```

### Empty State

Jika tidak terdapat transaksi:

```text id="rpt047"
Tidak ada transaksi pada periode ini.
```

Summary tetap ditampilkan:

```text id="rpt048"
Pemasukan       Rp0
Pengeluaran     Rp0
Selisih         Rp0
Saldo Kas       Rp500.000
```

Saldo dapat tetap memiliki nilai karena transaksi periode sebelumnya.

### Mobile

Pada mobile:

```text id="rpt049"
Periode
01 Agu - 31 Agu

SALDO
Rp1.250.000

PEMASUKAN
Rp2.500.000

PENGELUARAN
Rp1.750.000

SELISIH
+ Rp750.000
```

Daftar transaksi dapat menggunakan card agar lebih mudah dibaca daripada tabel lebar.

## Livewire Component

Component utama:

```text id="rpt050"
Report
└── ReportIndex
```

State:

```text id="rpt051"
startDate
endDate
```

Data:

```text id="rpt052"
totalIncome
totalExpense
difference
balance

incomeByCategory
expenseByCategory

incomes
expenses
```

Flow:

```text id="rpt053"
ReportIndex
    ↓
Set Period
    ↓
Validation
    ↓
ReportService
    ↓
ReportRepository
    ↓
Database
```

Ketika filter berubah dan admin menekan:

```text id="rpt054"
Tampilkan
```

report dimuat ulang sesuai periode.

## Service

Service:

```text id="rpt055"
ReportService
```

Tanggung jawab:

* Menentukan periode laporan.
* Mengambil total pemasukan.
* Mengambil total pengeluaran.
* Menghitung selisih.
* Mengambil saldo sampai `end_date`.
* Mengambil ringkasan kategori.
* Mengambil daftar transaksi.

Contoh hasil:

```text id="rpt056"
ReportSummary

start_date
2026-08-01

end_date
2026-08-31

income
2500000

expense
1750000

difference
750000

balance
1250000
```

Service tidak menyimpan report ke database.

## Repository

Repository:

```text id="rpt057"
ReportRepository
```

Tanggung jawab:

```text id="rpt058"
Get Income By Period
Get Expense By Period

Get Total Income By Period
Get Total Expense By Period

Get Balance Until Date

Get Income By Category
Get Expense By Category
```

Repository menangani query dan aggregation.

Service menangani interpretasi hasil menjadi laporan.

Flow:

```text id="rpt059"
ReportIndex
    ↓
ReportService
    ↓
ReportRepository
    ↓
Income / Expense / Details / Category
```

## Testing

Testing Report minimal mencakup:

* Guest tidak dapat membuka laporan.
* Admin dapat membuka laporan.
* Default periode menggunakan awal bulan sampai hari ini.
* Start date wajib diisi.
* End date wajib diisi.
* End date tidak boleh sebelum start date.
* Pemasukan hanya menghitung transaksi dalam periode.
* Pengeluaran hanya menghitung transaksi dalam periode.
* Filter menggunakan tanggal transaksi, bukan `created_at`.
* Total pemasukan berasal dari detail.
* Total pengeluaran berasal dari detail.
* Selisih dihitung dengan benar.
* Selisih dapat bernilai positif.
* Selisih dapat bernilai negatif.
* Saldo menghitung transaksi sebelum periode.
* Saldo hanya menghitung transaksi sampai `end_date`.
* Transaksi setelah `end_date` tidak memengaruhi saldo laporan.
* Ringkasan pemasukan per kategori dihitung dengan benar.
* Ringkasan pengeluaran per kategori dihitung dengan benar.
* Kategori tanpa transaksi tidak perlu ditampilkan.
* Daftar pemasukan sesuai periode.
* Daftar pengeluaran sesuai periode.
* Report tetap dapat ditampilkan ketika periode tidak memiliki transaksi.
* Saldo dapat bernilai negatif.
* Nominal ditampilkan dalam format Rupiah.

## Future Improvement

Report dapat dikembangkan dengan:

```text id="rpt060"
Export PDF
Export Excel
Print
Chart
Monthly Comparison
Yearly Report
Budget Comparison
Account Report
Category Detail Report
```

Filter dapat dikembangkan menjadi:

```text id="rpt061"
Hari Ini
Minggu Ini
Bulan Ini
Bulan Lalu
Tahun Ini
Custom
```

Visualisasi dapat ditambahkan:

```text id="rpt062"
Pemasukan vs Pengeluaran

Kategori Pengeluaran

Perkembangan Saldo

Cash Flow Bulanan
```

Jika NgeKas memiliki beberapa rekening/kas:

```text id="rpt063"
Laporan Semua Akun

BRI
Cash
E-Wallet
```

Jika member sudah tersedia:

```text id="rpt064"
Laporan Iuran Member
Laporan Member Belum Bayar
Laporan Pembayaran per Kegiatan
```

Fitur tersebut **tidak termasuk scope MVP NgeKas saat ini**.
