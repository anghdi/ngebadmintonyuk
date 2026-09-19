@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
<div class="profile-page">
    <div class="page-head"><div><span class="eyebrow">AKUN MEMBER</span><h1>Profil Saya</h1></div>
        @if($member->hasCompleteProfile())<a class="btn soft" href="{{ route('public-sessions.index') }}">Lihat jadwal</a>@endif
    </div>
    <section class="profile-summary">
        <span class="profile-avatar">@if($member->avatar_path)<img src="{{ route('profile.avatar') }}" alt="Foto {{ $member->name }}">@else{{ $member->initials() }}@endif</span>
        <div class="profile-summary-name"><strong>{{ $member->nickname ?: $member->name }}</strong><span>Member sejak {{ $member->memberSince()->translatedFormat('M Y') }} · {{ $member->membershipDuration() }}</span></div>
        <div class="profile-stat"><strong>{{ $attendanceCount }}</strong><span>Kali hadir</span></div>
        <div class="profile-stat"><strong>{{ $remainingCredits }}</strong><span>Kuota main</span></div>
    </section>
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <section class="card profile-form-card">
            <h2>Data diri</h2>
            @if(! $member->hasCompleteProfile())<p class="profile-required-note">Lengkapi nama, tanggal lahir, dan level bermain.</p>@endif
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="compact-form" data-loading-message="Menyimpan profil…" data-upload-loading-message="Mengunggah dan menyimpan foto…">
                @csrf @method('put')
                <label>Nama lengkap<input name="name" value="{{ old('name', $member->name) }}" maxlength="255" autocomplete="name" required>@error('name')<span class="field-error">{{ $message }}</span>@enderror</label>
                <div class="form-grid">
                    <label>Tanggal lahir<input type="date" name="date_of_birth" value="{{ old('date_of_birth', $member->date_of_birth?->toDateString()) }}" min="{{ today()->subYears(120)->toDateString() }}" max="{{ today()->toDateString() }}" autocomplete="bday" required>@error('date_of_birth')<span class="field-error">{{ $message }}</span>@enderror</label>
                    <label>Nama panggilan <span class="optional">Opsional</span><input name="nickname" value="{{ old('nickname', $member->nickname) }}" maxlength="50" autocomplete="nickname">@error('nickname')<span class="field-error">{{ $message }}</span>@enderror</label>
                </div>
                <small class="profile-private-note">Tanggal lahir hanya bisa dilihat kamu dan admin.</small>
                <div class="form-grid">
                    <label>WhatsApp <span class="optional">Opsional</span><input type="tel" name="phone" value="{{ old('phone', $member->phone) }}" maxlength="30" autocomplete="tel">@error('phone')<span class="field-error">{{ $message }}</span>@enderror</label>
                    <label>Level bermain<select name="playing_level" required><option value="" disabled @selected(old('playing_level', $member->playing_level) === null)>Pilih level</option><option value="beginner" @selected(old('playing_level', $member->playing_level) === 'beginner')>Pemula</option><option value="intermediate" @selected(old('playing_level', $member->playing_level) === 'intermediate')>Menengah</option><option value="advanced" @selected(old('playing_level', $member->playing_level) === 'advanced')>Mahir</option></select>@error('playing_level')<span class="field-error">{{ $message }}</span>@enderror</label>
                </div>
                <label>Foto profil <span class="optional">Opsional</span><input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">@error('avatar')<span class="field-error">{{ $message }}</span>@enderror<small>JPG, PNG, WebP · maksimal 10 MB · otomatis dikompres</small></label>
                <label>Email<input type="email" value="{{ $member->email }}" readonly></label>
                <button type="submit" class="btn primary">Simpan profil</button>
            </form>
        </section>
        <section class="card profile-password-card">
            <h2>Ubah kata sandi</h2>
            <form method="post" action="{{ route('profile.password') }}" class="compact-form">
                @csrf @method('put')
                <label>Kata sandi sekarang<input type="password" name="current_password" autocomplete="current-password" required>@error('current_password')<span class="field-error">{{ $message }}</span>@enderror</label>
                <label>Kata sandi baru<input type="password" name="password" autocomplete="new-password" required>@error('password')<span class="field-error">{{ $message }}</span>@enderror</label>
                <label>Ulangi kata sandi baru<input type="password" name="password_confirmation" autocomplete="new-password" required></label>
                <button type="submit" class="btn soft full">Ubah kata sandi</button>
            </form>
        </section>
    </div>
</div>
@endsection
