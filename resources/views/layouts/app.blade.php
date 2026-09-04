<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#171717">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="NgeBadmintonYuk">
    <title>@yield('title', 'NgeKas') — NgeBadmintonYuk</title>
    <link rel="icon" href="{{ asset('pwa-icon-192.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body
    @if(! auth()->user()->isAdmin())
        data-push-client
        data-webpush-vapid-key="{{ config('services.webpush.public_key') }}"
        data-firebase-service-worker-url="{{ route('firebase.service-worker') }}"
        data-push-store-url="{{ route('push-subscriptions.store') }}"
        data-push-delete-url="{{ route('push-subscriptions.destroy') }}"
        data-push-auto-prompt="{{ session()->pull('offer_push_notifications', false) ? 'true' : 'false' }}"
    @endif
>
<div class="shell">
    <aside id="sidebar" aria-label="Navigasi utama">
        <div class="sidebar-head">
            <a href="{{ route('dashboard') }}" class="brand" aria-label="NgeKas — NgeBadmintonYuk">
                <img src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
            </a>
            <button type="button" class="sidebar-close" aria-label="Tutup navigasi" data-sidebar-close><x-nav-icon name="close" /></button>
        </div>
        <div class="sidebar-intro">
            <span class="sidebar-pulse" aria-hidden="true"></span>
            <span>Pengelolaan komunitas badminton</span>
        </div>
        <nav class="sidebar-nav">
            <a @class(['active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}"><span class="nav-icon-wrap"><x-nav-icon name="home" /></span> Beranda</a>
            <a @class(['active' => request()->routeIs('public-sessions.*')]) href="{{ route('public-sessions.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="calendar" /></span> Jadwal Main</a>
            <a @class(['active' => request()->routeIs('scoreboard')]) href="{{ route('scoreboard') }}"><span class="nav-icon-wrap"><x-nav-icon name="score" /></span> Papan Skor</a>
            <a @class(['active' => request()->routeIs('top-ups.*')]) href="{{ route('top-ups.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="wallet" /></span> {{ auth()->user()->isAdmin() ? 'Verifikasi Top Up' : 'Top Up Kuota' }}</a>
            @if(auth()->user()->isAdmin())
                <p>KOMUNITAS</p>
                <a @class(['active' => request()->routeIs('members.*')]) href="{{ route('members.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="users" /></span> Member</a>
                <a @class(['active' => request()->routeIs('play-sessions.*')]) href="{{ route('play-sessions.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="session" /></span> Sesi Bermain</a>
                <a @class(['active' => request()->routeIs('inventory.*')]) href="{{ route('inventory.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="shuttlecock" /></span> Shuttlecock</a>
                <p>KEUANGAN</p>
                <a @class(['active' => request()->routeIs('incomes.*')]) href="{{ route('incomes.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="income" /></span> Pemasukan</a>
                <a @class(['active' => request()->routeIs('expenses.*')]) href="{{ route('expenses.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="expense" /></span> Pengeluaran</a>
                <a @class(['active' => request()->routeIs('categories.*')]) href="{{ route('categories.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="tag" /></span> Kategori</a>
                <a @class(['active' => request()->routeIs('reports.*')]) href="{{ route('reports.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="report" /></span> Laporan</a>
                <a @class(['active' => request()->routeIs('push-notifications.*')]) href="{{ route('push-notifications.index') }}"><span class="nav-icon-wrap"><x-nav-icon name="bell" /></span> Notifikasi</a>
            @endif
        </nav>
        <div class="sidebar-footer">
            <button type="button" class="sidebar-install" data-pwa-install hidden><span class="nav-icon-wrap"><x-nav-icon name="install" /></span><span><b>Install aplikasi</b><small>Akses lebih cepat</small></span></button>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="logout"><span class="nav-icon-wrap"><x-nav-icon name="logout" /></span> Keluar</button>
            </form>
        </div>
    </aside>

    <button type="button" class="sidebar-backdrop" aria-label="Tutup navigasi" data-sidebar-close></button>

    <main>
        <header>
            <button type="button" class="menu" aria-label="Buka navigasi" aria-controls="sidebar" aria-expanded="false" data-sidebar-open><x-nav-icon name="menu" /></button>
            <a href="{{ route('dashboard') }}" class="mobile-brand" aria-label="NgeKas">
                <img src="{{ asset('pwa-icon-192.png') }}" alt="">
                <strong>NgeBadmintonYuk</strong>
            </a>
            <div class="user-summary">
                <span class="user-avatar"><img src="{{ asset('pwa-icon-192.png') }}" alt=""></span>
                <span><b>{{ auth()->user()->name }}</b><small>{{ auth()->user()->isAdmin() ? 'Administrator' : 'Akun pemain' }}</small></span>
            </div>
        </header>
        <section class="content">
            @if(session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </section>
    </main>
</div>

<dialog class="pwa-install-dialog" data-pwa-guide aria-labelledby="pwa-install-title">
    <button type="button" class="pwa-dialog-close" aria-label="Tutup" data-pwa-guide-close><x-nav-icon name="close" /></button>
    <img src="{{ asset('pwa-icon-192.png') }}" alt="">
    <span class="eyebrow">INSTALL APLIKASI</span>
    <h2 id="pwa-install-title">Pasang NgeBadmintonYuk</h2>
    <p>Tekan tombol Share di browser, lalu pilih <strong>Add to Home Screen</strong>.</p>
    <button type="button" class="btn primary full" data-pwa-guide-close>Mengerti</button>
</dialog>

@if(! auth()->user()->isAdmin())
    <dialog class="pwa-install-dialog push-permission-dialog" data-push-permission-dialog aria-labelledby="push-permission-title">
        <span class="push-permission-icon" aria-hidden="true">◉</span>
        <span class="eyebrow">NOTIFIKASI KOMUNITAS</span>
        <h2 id="push-permission-title">Aktifkan notifikasi?</h2>
        <p>Dapatkan kabar saat pemain ikut atau batal dari sesi, serta pengumuman penting dari admin.</p>
        <div class="push-permission-actions">
            <button type="button" class="btn soft" data-push-permission-later>Nanti saja</button>
            <button type="button" class="btn primary" data-push-permission-allow>Aktifkan</button>
        </div>
        <small>Di iPhone, pasang aplikasi ke Home Screen terlebih dahulu agar notifikasi tersedia.</small>
    </dialog>
@endif

@stack('scripts')
</body>
</html>
