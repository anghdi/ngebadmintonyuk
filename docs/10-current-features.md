# Matriks fitur dan keselarasan proyek

Tanggal audit: 7 September 2026. Status **ada** berarti ditemukan di kode/route, bukan berarti semua skenario sudah diuji atau sudah berjalan di produksi. Nama tes di tabel menunjuk berkas dalam `tests/Feature`, kecuali disebut JavaScript. Semua route ada di [web.php](../routes/web.php).

## Inventaris fitur

| Fitur / akses | Implementasi dan perilaku saat ini | Bukti tes / kekurangan |
| --- | --- | --- |
| Login/logout / guest dan akun | AuthController; session, remember me, logout; Gate admin di AppServiceProvider | PushNotificationTest menguji beberapa jalur login; logout dan kredensial salah belum punya tes khusus |
| Registrasi member / guest | RegistrationController + RegisterMemberRequest; akun langsung aktif/login | MembershipRegistrationTest |
| Dashboard / admin dan member | DashboardController; keuangan dan indikator operasional admin, paket/riwayat/jadwal member | PlaySessionRegistrationTest sebagian tampilan member; agregasi lengkap belum diuji |
| Kategori / admin | CategoryController; CRUD dan filter income/expense; blok hapus/ubah tipe kategori terpakai | FinanceTest hanya sebagian validasi tipe; CRUD kategori belum diuji lengkap |
| Pemasukan / admin | TransactionController + TransactionService; header/detail, filter, pagination, total agregat | FinanceTest: create, total, kategori salah; update/delete belum lengkap |
| Pengeluaran / admin | TransactionController + TransactionService; alur bersama pemasukan | Belum ada tes khusus CRUD expense |
| Laporan dan PDF / admin | ReportController → ReportService → ReportRepository; periode, kategori, saldo sampai end; PDF DomPDF | Belum ada tes khusus agregasi/PDF |
| Pengelolaan member / admin | MemberController; daftar, detail, edit profil, hapus melalui DeleteMemberAction | MembershipRegistrationTest menguji pembatasan akses; edit/hapus belum lengkap |
| Membership / admin | MembershipController; grant/update/delete, ledger kredit dan pengurangan dengan catatan | MembershipManagementTest; urutan transaksi terbaru pada tes sudah diperbaiki dengan ID |
| Absensi kuota / admin | AttendanceController → RecordAttendanceAction; present dengan metode membership memakai satu kredit; absent/no_show tidak memakai kredit | MembershipManagementTest dan CashQuotaLifecycleTest mencakup hadir/absen, koreksi, paket komunitas dan kuota habis |
| Top-up / member dan admin | TopUpRequestController; bukti privat, harga aktif, review approved/rejected; approved membuat linked income dan +4 kredit atomik; TopUpSettingController mengubah harga | CashQuotaLifecycleTest: approval, nominal snapshot, review duplikat, penolakan, paket tidak aktif dan proteksi income; upload/ownership proof belum lengkap |
| Jadwal admin | PlaySessionController; create/show/edit/update/delete, filter bulan, kapasitas utama/waiting; UpdatePlaySessionAction memvalidasi perubahan secara atomik | PlaySessionMonthFilterTest dan CashQuotaLifecycleTest: proteksi hapus serta pembatalan setelah koreksi kuota; coverage CRUD belum lengkap |
| Jadwal publik / guest dan akun | PublicPlaySessionController; pilih bulan terlebih dahulu, detail sesi; layout menurut login | PlaySessionMonthFilterTest |
| Daftar main / member | SessionRegistrationController → RegisterForPlaySessionAction; akun wajib, WhatsApp opsional, satu akun per sesi, waiting list | PlaySessionRegistrationTest |
| Kelola peserta / admin | SessionRegistrationController; tambah dari akun member, edit identitas/status, hapus yang masih listed dan unpaid | PlaySessionRegistrationTest sebagian; seluruh edit/hapus belum lengkap |
| Pembayaran peserta / admin | RecordSessionRegistrationPaymentAction; tunai/transfer membuat linked income; membership hanya memotong kuota saat hadir, tanpa income baru | PlaySessionRegistrationTest dan CashQuotaLifecycleTest menguji income idempotent, waiting list, pergantian metode dan pemakaian kuota |
| Batal main / pemilik member | CancelSessionRegistrationRequest + DeleteSessionRegistrationAction; milik sendiri, sebelum sesi, listed dan unpaid | PlaySessionRegistrationTest: sukses, bukan pemilik, sudah bayar, sesi lewat |
| Blok no-show / member | RegisterForPlaySessionAction menolak akun dengan >=3 no_show lintas sesi | PlaySessionRegistrationTest |
| Inventori / admin | ShuttlecockInventoryController + StockMovementController; item, mutasi purchase/usage/adjustment dan batas stok | ShuttlecockInventoryTest: tambah stok, stok tidak negatif; update/delete belum lengkap |
| Push / member | RequireMemberNotifications mewajibkan aktivasi per perangkat/session sebelum akses fitur; NotificationSetupController dan member-notifications.js menangani izin, pendaftaran Web Push, serta pemeriksaan izin dicabut; reset FCM lama tetap berlaku | MemberNotificationRequirementTest, PushNotificationTest dan tests/JavaScript/member-notifications.test.js |
| Broadcast dan pelanggan push / admin | PushNotificationController; semua member/per sesi, riwayat pengiriman, daftar perangkat | PushNotificationTest; pengiriman nyata ke perangkat belum diuji pada audit ini |
| Notifikasi aktivitas / otomatis | SendSessionRegistrationNotificationAction; daftar/batal mengirim sinkron ke semua perangkat member berlangganan | PushNotificationTest |
| PWA / browser yang mendukung | Manifest, service worker dan panduan instalasi/aktivasi push | PushNotificationTest dan tests/JavaScript/pwa-install.test.js; instalasi nyata lintas perangkat belum diverifikasi |
| Papan skor / akun login | Route scoreboard, view scoreboard, resources/js/scoreboard.js; skor 21, deuce sampai 30, dua kemenangan game | ScoreboardTest dan tests/JavaScript/scoreboard.test.js; tidak ada model/route simpan pertandingan ke server |
| Indikator loading / semua peran | Komponen server-loading dan resources/js/server-loading.js; navigasi internal, submit form, pemuatan awal dan request notifikasi; reset saat kembali browser; PDF/tab baru/anchor dikecualikan | ServerLoadingTest dan tests/JavaScript/server-loading.test.js |

## Aturan operasional yang tidak boleh terlewat

### Transisi peserta

| Kondisi / tindakan | Kas | Kuota dan absensi |
| --- | --- | --- |
| Daftar atau waiting, metode membership | Tidak berubah | Belum memesan atau memotong kredit |
| Peserta utama membership → present | Tidak ada income baru | Satu usage -1, attendance present, payment paid |
| Membership present → no_show | Tidak berubah | Usage dikembalikan, attendance absent, payment unpaid |
| Membership present/no_show → listed | Tidak berubah | Absensi dihapus, usage bila ada dikembalikan, payment unpaid |
| Cash/transfer dikonfirmasi paid | Satu income peserta | Tidak memotong kredit |
| Cash/transfer hadir/no_show | Catatan pembayaran tetap | Absensi tersinkron, tanpa usage |
| Cash/transfer paid → unpaid | Linked income dihapus | Hanya koreksi pencatatan pembayaran; tidak ada refund |
| Ubah metode setelah absensi | Ditolak | Koreksi ke listed dahulu |
| Hapus peserta | Hanya listed + unpaid | Waiting berikutnya naik menurut ID; pembatalan mandiri hanya sesi scheduled yang belum mulai |
| Batalkan sesi dengan paid/usage | Ditolak | Selesaikan koreksi pembayaran/kuota dahulu |

Data top-up approved dan charged_absent lama tidak diproses ulang secara massal. Member tanpa saldo boleh memilih membership saat daftar; ketersediaan paket/kuota divalidasi saat hadir. Jalur absensi paket tanpa registrasi historis masih didukung.

### Membership dan top-up

- Saldo kuota dihitung dari `membership_transactions.quantity`, bukan `initial_credits` atau kolom saldo manual.
- Grant paket membuat transaksi credit. Penyesuaian admin pada jalur saat ini adalah pengurangan, dengan catatan dan pelaku; dilarang melebihi saldo.
- Absensi present dengan metode membership memilih paket aktif yang sesuai venue, court, harga dan rentang tanggal. Paket spesifik diprioritaskan, kemudian paket komunitas; dalam kelompok dipilih yang paling lama. Koreksi ke no_show atau listed mengembalikan kuota dalam satu transaksi database. Penyimpanan status sama mempertahankan pemakaian lama sehingga tidak mengalokasikan ulang kuota.
- Status `session_registrations` disinkronkan ke `attendances` melalui UpdateSessionRegistrationAction, termasuk dari endpoint absensi lama. Hanya metode membership + present memakai kuota; no_show tidak dipotong. Kembali ke listed menghapus absensi terkait dan mengembalikan kuota. Jalur absensi paket tanpa registrasi lama tetap tersedia.
- Top-up default Rp110.000 untuk empat kredit. Harga aktif dapat diubah admin; jumlah kredit tetap empat. Ini default kode, bukan klaim nilai setting database saat ini.
- Member memilih paket aktif miliknya; tanpa paket, action dapat menyiapkan paket komunitas. Kredit dan linked income baru ditambahkan bersama saat approved, bukan saat upload. Nilai pemasukan menggunakan nominal pengajuan, tanggalnya tanggal persetujuan. Persetujuan lama tidak dibukukan ulang otomatis.
- Bukti menerima JPG/JPEG/PNG/WebP/PDF maksimal 4 MB, disimpan di disk local privat. Akses proof dibatasi pemilik/admin. Pengajuan pending per paket dan review ulang dicegah. Kegagalan simpan pengajuan membersihkan berkas upload.

### Sesi dan keuangan

- Pendaftaran hanya untuk sesi scheduled yang belum lewat; kapasitas total = max_players + max_waiting_players. Urutan ID menentukan posisi utama/waiting, sehingga penghapusan peserta menyebabkan promosi otomatis.
- Batas no-show berdasarkan akun, tidak bergantung nomor WhatsApp.
- Pembayaran waiting list ditolak sebelum masuk utama. Pembayaran memakai harga dan tanggal sesi, bukan tanggal transfer, dan membuat kategori Iuran Lapangan bila perlu.
- Unpaid menghapus linked income; paid ulang memperbarui income yang sama. Income tersebut dilindungi dari edit/hapus manual.
- Top-up baru yang disetujui membukukan uang masuk satu kali. Grant membership manual, pemakaian kuota, dan stok tidak otomatis membukukan uang masuk/keluar. Jangan menghitung pemakaian kuota sebagai pemasukan kedua.
- Harga sesi boleh nol menurut StorePlaySessionRequest. Konsistensi pembayaran sesi gratis versus validasi nominal positif transaksi manual belum punya tes khusus.

### Inventori, push dan akses

- Stok = jumlah quantity bertanda. Purchase dan adjustment menambah; usage mengurangi. Input quantity positif; hasil akhir tidak boleh negatif. Unit cost hanya atribut mutasi, tidak otomatis menjadi expense.
- Push dikirim sinkron; belum ada jobs/scheduler notifikasi domain. Gagal mengirim notifikasi aktivitas dilaporkan tanpa membatalkan pendaftaran yang berhasil.
- Subscription baru menggunakan Web Push VAPID; kode sender FCM masih ada untuk kompatibilitas. Member wajib mengaktifkan notifikasi di perangkat/session yang dipakai sebelum membuka fitur. Subscription milik akun yang sama di perangkat lain tidak cukup. Admin dan jadwal publik untuk guest dikecualikan; logout serta endpoint aktivasi/penghapusan subscription tetap tersedia. Izin browser tetap harus disetujui sendiri oleh member.
- Member tidak boleh mengakses administrasi. Registrasi, daftar main, upload top-up, subscription dan broadcast memiliki throttle route; login belum memiliki throttle eksplisit.

## Perbedaan dengan dokumen lama yang diselesaikan

| Rancangan awal | Kondisi yang menjadi acuan sekarang |
| --- | --- |
| Satu admin, tanpa registrasi/member/jadwal | Dua peran dan operasional komunitas sudah ada |
| Semua halaman memakai komponen Livewire | Halaman domain memakai Controller + Blade; paket Livewire terpasang |
| Service dan repository per modul | Actions domain, TransactionService bersama, ReportService dan satu ReportRepository |
| PDF sebagai pengembangan berikutnya | PDF sudah tersedia; Excel belum tersedia |
| Dashboard keuangan saja | Dashboard berbeda menurut peran dan memuat indikator komunitas |
| Hanya enam tabel inti | Ada ledger membership, sesi, peserta, top-up, inventori dan push |
| Semua income dapat diedit/dihapus manual | Linked income peserta dikelola dari daftar pemain |
| Daftar testing dalam docs berarti selesai | Daftar lama adalah acceptance checklist; cakupan aktual dicatat di tabel ini |

Bagian awal docs 00–09 sekarang menjelaskan status aktual. Isi lama tetap diberi label arsip/referensi supaya requirement awal tidak hilang. Tahap lanjutan poin 1–2 menambahkan integrasi top-up→income dan absensi peserta→ledger kuota, dengan no-show tanpa potongan sesuai keputusan pengguna.

## Backlog yang tetap terbuka

1. Terapkan migration penautan income top-up setelah MySQL lokal tersedia. Rekonsiliasi persetujuan top-up dan absensi historis secara manual sebelum memilih backfill; jangan mencatat uang atau mengembalikan kuota lama secara massal tanpa pemeriksaan.
2. Tambahkan coverage keuangan: kategori CRUD, update/delete income/expense, cascade, saldo, batas periode, PDF, serta proteksi income peserta.
3. Lengkapi coverage file privat, gangguan simpan, edit/hapus paket/item dan kombinasi kompatibilitas paket. Approval top-up, review duplikat, koreksi absensi, kuota habis dan beberapa proteksi penghapusan sudah diuji.
4. Integrasi stok→expense tetap belum dikerjakan. Sesuai keputusan pengguna, tidak ada refund dan tidak ada backlog refund. Koreksi status pembayaran hanya mengoreksi salah pencatatan kas.
5. Verifikasi produksi: migration aktual, konfigurasi VAPID, HTTPS/PWA, pengiriman push nyata, layout perangkat, dan runtime frontend yang sesuai. Audit ini bukan verifikasi deployment.
6. Fitur yang belum ditemukan: reset password/email verification, payment gateway, Excel, role/permission granular, multi-kas, audit log global, jurnal/COA/neraca/laba-rugi formal, ranking, turnamen, aplikasi native. Keberadaan paket atau tabel pendukung tidak menandakan fitur ini tersedia.

Lihat [konteks final](11-final-context.md) untuk urutan kerja dan hasil verifikasi; [Graphify](../graphify-out/GRAPH_REPORT.md) untuk navigasi relasi kode.
