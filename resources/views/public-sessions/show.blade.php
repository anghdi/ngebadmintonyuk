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
        <div class="public-session-capacity"><strong>{{ rupiah($playSession->price_per_session) }}</strong><span>{{ $confirmedRegistrations->count() }}/{{ $playSession->max_players }} pemain · {{ $waitingRegistrations->count() }}/{{ $playSession->max_waiting_players }} waiting</span></div>
    </section>

    <div class="public-registration-grid">
        <section class="card registration-form-card">
            @guest
                <span class="eyebrow">AKUN DIPERLUKAN</span>
                <h2>Buat akun dulu</h2>
                <p>Semua pemain, termasuk non-member, perlu masuk dengan akun sebelum mengisi daftar.</p>
                <div class="actions">
                    <a class="btn primary" href="{{ route('register') }}">Buat akun</a>
                    <a class="btn soft" href="{{ route('login') }}">Sudah punya akun</a>
                </div>
            @elseif(auth()->user()->isAdmin())
                <span class="eyebrow">AKUN ADMIN</span>
                <h2>Gunakan akun pemain</h2>
                <p>Administrator dapat menambahkan pemain melalui halaman pengelolaan sesi.</p>
            @elseif($registration)
                <span class="eyebrow">{{ $registrationIsWaiting ? 'WAITING LIST' : 'TERDAFTAR' }}</span>
                <h2>{{ $registrationIsWaiting ? 'Anda masuk antrean' : 'Nama Anda sudah masuk' }}</h2>
                <p>{{ $registrationIsWaiting ? 'Slot akan diberikan mengikuti urutan pendaftaran.' : 'Silakan hadir sesuai jadwal.' }}</p>
                <div @class(['registration-confirmation', 'is-waiting' => $registrationIsWaiting])><strong>{{ $registration->name }}</strong><span>{{ $registrationIsWaiting ? 'Menunggu slot utama' : ($registration->payment_method === 'transfer' ? 'Transfer bank' : 'Bayar tunai') }}</span></div>
                @if($registration->attendance_status === 'listed' && $registration->payment_status === 'unpaid')
                    <form method="post" action="{{ route('public-sessions.cancel', [$playSession, $registration]) }}" class="registration-cancel" onsubmit="return confirm('Batalkan keikutsertaan dari sesi ini?')">
                        @csrf
                        @method('delete')
                        <button class="btn danger-bg full">Batalkan keikutsertaan</button>
                    </form>
                @else
                    <p class="registration-cancel-note">Pendaftaran yang sudah dibayar atau diproses admin tidak dapat dibatalkan sendiri.</p>
                @endif
            @elseif($isRegistrationClosed)
                <span class="eyebrow">DAFTAR PENUH</span>
                <h2>Kapasitas terpenuhi</h2>
                <p>Slot utama dan waiting list sudah penuh.</p>
            @elseif($noShowCount >= 3)
                <span class="eyebrow">PENDAFTARAN DIBLOKIR</span>
                <h2>Hubungi admin</h2>
                <p>Akun ini tercatat tiga kali tidak hadir.</p>
            @else
                @php($mainListIsFull = $confirmedRegistrations->count() >= $playSession->max_players)
                <span class="eyebrow">{{ $mainListIsFull ? 'WAITING LIST' : 'ISI DAFTAR' }}</span>
                <h2>{{ $mainListIsFull ? 'Masuk antrean' : 'Ikut bermain' }}</h2>
                @if($mainListIsFull)
                    <p>Slot utama penuh. Anda akan masuk waiting list.</p>
                @endif
                <form method="post" action="{{ route('public-sessions.register', $playSession) }}" class="compact-form">
                    @csrf
                    <label>Nama<input value="{{ auth()->user()->name }}" readonly></label>
                    <label>Nomor WhatsApp <span class="optional">Opsional</span><input name="phone" inputmode="numeric" value="{{ old('phone', auth()->user()->phone) }}" placeholder="08xxxxxxxxxx"></label>
                    <fieldset class="payment-options">
                        <legend>Pembayaran</legend>
                        <label><input type="radio" name="payment_method" value="transfer" @checked(old('payment_method') === 'transfer') required><span><b>Transfer</b><small>BCA atau BRI</small></span></label>
                        <label><input type="radio" name="payment_method" value="cash" @checked(old('payment_method') === 'cash') required><span><b>Tunai</b><small>Bayar di lokasi</small></span></label>
                    </fieldset>
                    <button class="btn primary full">{{ $mainListIsFull ? 'Masuk waiting list' : 'Masuk daftar' }}</button>
                </form>
                <div class="compact-bank-info"><span>BCA <b>6690685688</b></span><span>BRI <b>036801013857535</b></span><small>a.n. Angga Hadi Permana</small></div>
            @endif
        </section>

        <section class="card participant-list-card">
            <div class="card-head"><div><span class="eyebrow">PEMAIN</span><h2>{{ $confirmedRegistrations->count() }}/{{ $playSession->max_players }} slot utama</h2></div><span class="list-count-badge">{{ max(0, $playSession->max_players - $confirmedRegistrations->count()) }} tersedia</span></div>
            <ol class="participant-list">
                @forelse($confirmedRegistrations as $participant)
                    <li><span>{{ $loop->iteration }}</span><strong>{{ $participant->name }}</strong><small>{{ $participant->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }}</small></li>
                @empty
                    <li class="empty">Belum ada pemain.</li>
                @endforelse
            </ol>

            <div class="waiting-list-head"><div><span class="eyebrow">WAITING LIST</span><h2>{{ $waitingRegistrations->count() }}/{{ $playSession->max_waiting_players }} antrean</h2></div></div>
            <ol class="participant-list waiting-list">
                @forelse($waitingRegistrations as $participant)
                    <li><span>W{{ $loop->iteration }}</span><strong>{{ $participant->name }}</strong><small>Menunggu slot</small></li>
                @empty
                    <li class="empty">Belum ada antrean.</li>
                @endforelse
            </ol>
        </section>
    </div>
@endsection
