<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2455f5">
    <title>Masuk — NgeKas</title>
    <link rel="icon" href="{{ asset('pwa-icon-192.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body class="login-page">
<main class="login-card">
    <span class="auth-shuttle" aria-hidden="true"><img src="{{ asset('icon.png') }}" alt=""></span>
    <img class="login-logo" src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
    <span class="eyebrow">NGE BADMINTON YUK</span>
    <h1>Masuk ke akun</h1>
    <p>Akses jadwal, kuota, dan informasi komunitas.</p>
    @if(session('legacy_push_reset'))
        <section class="legacy-push-reset" role="alert">
            <strong>Versi aplikasi lama sudah dinonaktifkan</strong>
            <p>Data notifikasi lama sudah kami hapus. Supaya notifikasi baru bekerja, pasang ulang aplikasi lalu masuk dari awal.</p>
            <ol>
                <li><b>Android:</b> hapus/uninstall NgeBadmintonYuk, lalu buka situs ini di Chrome dan pilih <em>Install aplikasi</em> atau <em>Tambahkan ke layar utama</em>.</li>
                <li><b>iPhone/iPad:</b> hapus NgeBadmintonYuk dari Home Screen, lalu buka situs ini di Safari dan pilih <em>Add to Home Screen</em>.</li>
                <li>Buka aplikasi yang baru dipasang, login lagi, lalu pilih <b>Aktifkan notifikasi</b>.</li>
            </ol>
        </section>
    @endif
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Kata sandi<input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember"> Tetap masuk</label>
        <button class="btn primary full">Masuk</button>
    </form>
    <div class="auth-divider"><span>atau</span></div>
    <a class="btn soft full" href="{{ route('public-sessions.index') }}">Lihat jadwal main</a>
    <a class="btn dark full" href="{{ route('register') }}">Buat akun pemain</a>
    <small>KOMUNITAS BADMINTON</small>
</main>
</body>
</html>
