<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#171717">
    <title>@yield('title', 'NgeKas') — NgeBadmintonYuk</title>
    <link rel="icon" href="{{ asset('icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body>
<div class="shell">
    <aside id="sidebar" aria-label="Navigasi utama">
        <div class="sidebar-head">
            <a href="{{ route('dashboard') }}" class="brand" aria-label="NgeKas — NgeBadmintonYuk">
                <img src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
            </a>
            <button type="button" class="sidebar-close" aria-label="Tutup navigasi" data-sidebar-close>×</button>
        </div>
        <div class="sidebar-intro">
            <span class="sidebar-pulse" aria-hidden="true"></span>
            <span>Pengelolaan komunitas badminton</span>
        </div>
        <nav>
            <a @class(['active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}"><span aria-hidden="true">⌂</span> Beranda</a>
            <a @class(['active' => request()->routeIs('public-sessions.*')]) href="{{ route('public-sessions.index') }}"><span aria-hidden="true">🏸</span> Jadwal Main</a>
            <a @class(['active' => request()->routeIs('top-ups.*')]) href="{{ route('top-ups.index') }}"><span aria-hidden="true">＋</span> {{ auth()->user()->isAdmin() ? 'Verifikasi Top Up' : 'Top Up Kuota' }}</a>
            @if(auth()->user()->isAdmin())
                <p>KOMUNITAS</p>
                <a @class(['active' => request()->routeIs('members.*')]) href="{{ route('members.index') }}"><span aria-hidden="true">◎</span> Member</a>
                <a @class(['active' => request()->routeIs('play-sessions.*')]) href="{{ route('play-sessions.index') }}"><span aria-hidden="true">◫</span> Sesi Bermain</a>
                <a @class(['active' => request()->routeIs('inventory.*')]) href="{{ route('inventory.index') }}"><span aria-hidden="true">◈</span> Shuttlecock</a>
                <p>KEUANGAN</p>
                <a @class(['active' => request()->routeIs('incomes.*')]) href="{{ route('incomes.index') }}"><span aria-hidden="true">↗</span> Pemasukan</a>
                <a @class(['active' => request()->routeIs('expenses.*')]) href="{{ route('expenses.index') }}"><span aria-hidden="true">↘</span> Pengeluaran</a>
                <a @class(['active' => request()->routeIs('categories.*')]) href="{{ route('categories.index') }}"><span aria-hidden="true">◇</span> Kategori</a>
                <a @class(['active' => request()->routeIs('reports.*')]) href="{{ route('reports.index') }}"><span aria-hidden="true">▤</span> Laporan</a>
            @endif
        </nav>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="logout"><span aria-hidden="true">↪</span> Keluar</button>
        </form>
    </aside>

    <button type="button" class="sidebar-backdrop" aria-label="Tutup navigasi" data-sidebar-close></button>

    <main>
        <header>
            <button type="button" class="menu" aria-label="Buka navigasi" aria-controls="sidebar" aria-expanded="false" data-sidebar-open>☰</button>
            <a href="{{ route('dashboard') }}" class="mobile-brand" aria-label="NgeKas">
                <img src="{{ asset('icon.png') }}" alt="">
                <strong>NgeBadmintonYuk</strong>
            </a>
            <div class="user-summary">
                <span class="user-avatar"><img src="{{ asset('icon.png') }}" alt=""></span>
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

@stack('scripts')
</body>
</html>
