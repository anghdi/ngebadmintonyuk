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
    <span class="eyebrow">Langkah terakhir</span>
    <h1>Aktifkan notifikasi</h1>
    <p>Aktifkan pada perangkat ini untuk melanjutkan.</p>
    @if(session('success'))
        <div class="alert success" role="status">{{ session('success') }}</div>
    @endif
    <section class="my-6 flex flex-col gap-4" data-push-opt-in aria-label="Aktivasi notifikasi">
        <p data-push-status role="status" aria-live="polite">Tekan tombol lalu pilih Izinkan.</p>
        <button type="button" class="btn primary full" data-push-toggle>Aktifkan notifikasi</button>
    </section>
    <noscript><div class="alert">Aktifkan JavaScript pada browser untuk menyiapkan notifikasi.</div></noscript>
    <section class="mb-6 grid gap-4 text-left md:grid-cols-2" aria-label="Panduan aktivasi notifikasi">
        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <span class="eyebrow">Android · Chrome</span>
            <h2 class="mt-2 text-base">Aktifkan di Android</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm">
                <li>Buka situs ini di <b>Google Chrome</b>.</li>
                <li>Tekan <b>Aktifkan notifikasi</b>.</li>
                <li>Pilih <b>Izinkan</b> saat diminta.</li>
                <li>Tunggu sampai aplikasi terbuka.</li>
            </ol>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <span class="eyebrow">iPhone/iPad · Safari</span>
            <h2 class="mt-2 text-base">Aktifkan di iOS</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm">
                <li>Buka situs ini di Safari pada iOS/iPadOS 16.4+.</li>
                <li>Tekan <b>Bagikan</b> lalu <b>Tambahkan ke Layar Utama</b>.</li>
                <li>Buka aplikasi dari Layar Utama.</li>
                <li>Tekan <b>Aktifkan notifikasi</b> lalu <b>Izinkan</b>.</li>
            </ol>
        </article>
    </section>
    <details class="mb-6 text-left text-sm">
        <summary class="cursor-pointer font-semibold">Tidak muncul permintaan izin?</summary>
        <div class="mt-3 flex flex-col gap-3">
            <p><b>Android:</b> buka ikon gembok/info situs di Chrome → Izin → Notifikasi → Izinkan, lalu muat ulang.</p>
            <p><b>iPhone/iPad:</b> buka Pengaturan → Notifikasi → NgeBadmintonYuk → aktifkan Izinkan Notifikasi. Jika aplikasinya belum ada, ulangi langkah Tambahkan ke Layar Utama.</p>
            <p><b>Browser tidak mendukung:</b> buka melalui browser yang mendukung Web Push, gunakan koneksi HTTPS, dan hindari mode privat.</p>
            <p>Jika layanan belum tersedia, hubungi admin komunitas lalu coba lagi setelah layanan pulih.</p>
        </div>
    </details>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="btn soft full">Keluar akun</button>
    </form>
</main>
<x-app-update />
<x-server-loading />
</body>
</html>
