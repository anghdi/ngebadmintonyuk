<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2455f5">
    <title>Buat Akun — NgeBadmintonYuk</title>
    <link rel="icon" href="{{ asset('pwa-icon-192.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" sizes="180x180">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body class="login-page">
<a class="skip-link" href="#auth-content">Lewati ke formulir</a>
<main class="login-card register-card" id="auth-content" tabindex="-1">
    <a href="{{ route('login') }}" class="auth-back">← Kembali ke halaman masuk</a>
    <img class="login-logo" src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
    <span class="eyebrow">Pendaftaran akun</span>
    <h1>Buat akun pemain</h1>
    <p>Buat akun, aktifkan notifikasi, lalu pilih sesi.</p>
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('register.store') }}">
        @csrf
        <label>Nama<input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">@error('name')<span class="field-error">{{ $message }}</span>@enderror</label>
        <div class="form-grid">
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">@error('email')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label>Nomor WhatsApp <span class="optional">Opsional</span><input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel">@error('phone')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label>Kata sandi<input type="password" name="password" required autocomplete="new-password">@error('password')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label>Ulangi kata sandi<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        </div>
        <button class="btn primary full">Daftar dan aktifkan notifikasi</button>
    </form>
    <small>Komunitas badminton</small>
</main>
<x-server-loading />
</body>
</html>
