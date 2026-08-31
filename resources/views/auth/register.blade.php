<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2455f5">
    <title>Daftar Member — NgeBadmintonYuk</title>
    <link rel="icon" href="{{ asset('icon.png') }}" type="image/png">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body class="login-page">
<main class="login-card register-card">
    <a href="{{ route('login') }}" class="auth-back">← Kembali masuk</a>
    <img class="login-logo" src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
    <span class="eyebrow">MEMBERSHIP KOMUNITAS</span>
    <h1>Masuk ke daftar pemain</h1>
    <p>Akun langsung aktif. Admin akan menambahkan paket main setelah pembayaran dikonfirmasi.</p>
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('register.store') }}">
        @csrf
        <label>Nama lengkap<input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"></label>
        <div class="form-grid">
            <label>Email<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
            <label>Nomor WhatsApp <span class="optional">Opsional</span><input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"></label>
            <label>Password<input type="password" name="password" required autocomplete="new-password"></label>
            <label>Ulangi password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
        </div>
        <button class="btn primary full">Buat akun member</button>
    </form>
    <small>KUOTA MAINMU AKAN TERCATAT RAPI</small>
</main>
</body>
</html>
