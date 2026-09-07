<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2455f5">
    <title>Aktifkan notifikasi — NgeKas</title>
    <link rel="icon" href="{{ asset('pwa-icon-192.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body class="login-page" data-push-client data-push-setup="true"
    data-push-setup-url="{{ route('notifications.setup') }}"
    data-webpush-vapid-key="{{ config('services.webpush.public_key') }}"
    data-firebase-service-worker-url="{{ route('firebase.service-worker') }}"
    data-push-store-url="{{ route('push-subscriptions.store') }}"
    data-push-delete-url="{{ route('push-subscriptions.destroy') }}">
<main class="login-card">
    <img class="login-logo" src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
    <span class="eyebrow">SATU LANGKAH LAGI</span>
    <h1>Aktifkan notifikasi dulu</h1>
    <p>Notifikasi wajib aktif pada perangkat ini agar kamu menerima perubahan jadwal dan informasi komunitas sebelum memakai fitur member.</p>
    <section class="my-6 flex flex-col gap-4" data-push-opt-in aria-label="Aktivasi notifikasi">
        <p data-push-status role="status" aria-live="polite">Tekan tombol di bawah, lalu pilih Izinkan pada permintaan browser.</p>
        <button type="button" class="btn primary full" data-push-toggle>Aktifkan notifikasi</button>
    </section>
    <noscript><div class="alert">Aktifkan JavaScript pada browser untuk menyiapkan notifikasi.</div></noscript>
    <details class="mb-6 text-left text-sm">
        <summary class="cursor-pointer font-semibold">Tidak muncul permintaan izin?</summary>
        <div class="mt-3 flex flex-col gap-3">
            <p><b>Izin pernah ditolak:</b> buka pengaturan situs pada browser, ubah Notifikasi menjadi Izinkan, lalu muat ulang halaman ini.</p>
            <p><b>iPhone/iPad:</b> gunakan iOS/iPadOS 16.4 atau lebih baru. Buka situs di Safari, pilih Bagikan → Tambahkan ke Layar Utama, lalu buka aplikasi dari ikon tersebut dan masuk kembali.</p>
            <p><b>Browser tidak mendukung:</b> buka melalui browser yang mendukung Web Push, gunakan koneksi HTTPS, dan hindari mode privat.</p>
            <p>Jika layanan belum tersedia, hubungi admin komunitas lalu coba lagi setelah layanan pulih.</p>
        </div>
    </details>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn soft full">Keluar akun</button>
    </form>
</main>
<x-server-loading />
</body>
</html>
