<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2455f5">
    <title>Masuk — NgeKas</title>
    <link rel="icon" href="{{ asset('icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body class="login-page">
<main class="login-card">
    <img class="login-logo" src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk">
    <h1>Selamat datang</h1>
    <p>Kelola kas komunitas dengan mudah dan rapi.</p>
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password<input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember"> Ingat saya</label>
        <button class="btn primary full">Masuk</button>
    </form>
    <div class="auth-divider"><span>atau</span></div>
    <a class="btn dark full" href="{{ route('register') }}">Daftar jadi member</a>
    <small>MAIN BARENG, SEHAT &amp; SERU!</small>
</main>
</body>
</html>
