<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#172a63">
    <title>@yield('title', 'Jadwal Main') — NgeBadmintonYuk</title>
    <link rel="icon" href="{{ asset('icon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ route('app.css') }}">
</head>
<body class="public-page">
<header class="public-nav">
    <a href="{{ route('public-sessions.index') }}" class="public-brand"><img src="{{ asset('logo.png') }}" alt="NgeBadmintonYuk"></a>
    <div class="public-actions">
        @auth
            <span>{{ auth()->user()->name }}</span>
            <a class="btn soft" href="{{ route('dashboard') }}">Dashboard</a>
        @else
            <a class="btn soft" href="{{ route('login') }}">Masuk</a>
            <a class="btn dark" href="{{ route('register') }}">Daftar member</a>
        @endauth
    </div>
</header>
<main class="public-content">
    @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
