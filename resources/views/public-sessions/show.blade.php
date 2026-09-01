@extends('layouts.public')

@section('title', $playSession->venue_name)

@section('content')
    <a class="back-link" href="{{ route('public-sessions.index') }}">← Semua jadwal</a>
    <section class="public-session-detail">
        <div>
            <span class="eyebrow">SESI BERMAIN</span>
            <h1>{{ $playSession->venue_name }}</h1>
            <p>{{ $playSession->court_name }} · {{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} · {{ $playSession->scheduled_at->format('H:i') }} WITA</p>
        </div>
        <div class="public-session-capacity"><strong>{{ rupiah($playSession->price_per_session) }}</strong><span>{{ $playSession->registrations->count() }}/{{ $playSession->max_players }} pemain</span></div>
    </section>

    <div class="public-registration-grid">
        <section class="card registration-form-card">
            @if($registration)
                <span class="eyebrow">TERDAFTAR</span>
                <h2>Nama Anda sudah masuk</h2>
                <p>Silakan hadir sesuai jadwal.</p>
                <div class="registration-confirmation"><strong>{{ $registration->name }}</strong><span>{{ $registration->payment_method === 'transfer' ? 'Transfer bank' : 'Bayar tunai' }}</span></div>
            @elseif($isFull)
                <span class="eyebrow">DAFTAR PENUH</span>
                <h2>Kapasitas terpenuhi</h2>
                <p>Sesi ini sudah diisi {{ $playSession->max_players }} pemain.</p>
            @elseif($noShowCount >= 3)
                <span class="eyebrow">PENDAFTARAN DIBLOKIR</span>
                <h2>Hubungi admin</h2>
                <p>Akun ini tercatat tiga kali tidak hadir.</p>
            @else
                <span class="eyebrow">ISI DAFTAR</span>
                <h2>Ikut bermain</h2>
                <form method="post" action="{{ route('public-sessions.register', $playSession) }}" class="compact-form">
                    @csrf
                    <label>Nama<input name="name" value="{{ old('name', auth()->user()?->name) }}" required @readonly(auth()->check() && ! auth()->user()->isAdmin())></label>
                    <label>Nomor WhatsApp<input name="phone" inputmode="numeric" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="08xxxxxxxxxx" required></label>
                    <fieldset class="payment-options">
                        <legend>Pembayaran</legend>
                        <label><input type="radio" name="payment_method" value="transfer" @checked(old('payment_method') === 'transfer') required><span><b>Transfer</b><small>BCA atau BRI</small></span></label>
                        <label><input type="radio" name="payment_method" value="cash" @checked(old('payment_method') === 'cash') required><span><b>Tunai</b><small>Bayar di lokasi</small></span></label>
                    </fieldset>
                    <button class="btn primary full">Masuk daftar</button>
                </form>
                <div class="compact-bank-info"><span>BCA <b>6690685688</b></span><span>BRI <b>036801013857535</b></span><small>a.n. Angga Hadi Permana</small></div>
            @endif
        </section>

        <section class="card participant-list-card">
            <div class="card-head"><div><span class="eyebrow">PEMAIN</span><h2>{{ $playSession->registrations->count() }}/{{ $playSession->max_players }} nama terdaftar</h2></div></div>
            <ol class="participant-list">
                @forelse($playSession->registrations as $participant)
                    <li><span>{{ $loop->iteration }}</span><strong>{{ $participant->name }}</strong></li>
                @empty
                    <li class="empty">Belum ada pemain.</li>
                @endforelse
            </ol>
        </section>
    </div>
@endsection
