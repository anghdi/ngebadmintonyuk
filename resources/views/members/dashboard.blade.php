@extends('layouts.app')
@section('title', 'Membership Saya')
@section('content')
<div class="member-welcome">
    <div>
        <span class="eyebrow">DASHBOARD MEMBER</span>
        <h1>Selamat datang, {{ str($member->name)->before(' ') }}.</h1>
        <p>Informasi kuota dan jadwal bermain.</p>
    </div>
    <div class="actions">
        <a class="btn primary" href="{{ route('top-ups.index') }}">Top up kuota</a>
        <span class="member-number">MEMBER #{{ str_pad((string) $member->id, 4, '0', STR_PAD_LEFT) }}</span>
    </div>
</div>

<section class="quota-hero">
    <div>
        <small>KUOTA SIAP PAKAI</small>
        <strong>{{ $remainingCredits }}</strong>
        <span>kali main</span>
    </div>
    <div class="quota-meta">
        <span><b>{{ $memberships->count() }}</b> paket aktif</span>
        <span><b>{{ $usedCredits }}</b> kali digunakan</span>
        <span>Kuota tersedia sesuai paket aktif.</span>
    </div>
</section>

<div class="section-heading"><div><span class="eyebrow">PAKET AKTIF</span><h2>Kuota bermain</h2></div></div>
<div class="membership-grid">
    @forelse($memberships as $membership)
        <article class="membership-ticket">
            <div class="ticket-main">
                <span class="status-pill {{ (int) $membership->balance > 0 ? 'active' : 'muted' }}">{{ (int) $membership->balance > 0 ? 'Bisa dipakai' : 'Kuota habis' }}</span>
                <strong>{{ $membership->venue_name }}</strong>
                <p>{{ $membership->isCommunityPackage() ? 'Berlaku untuk semua sesi komunitas' : $membership->court_name.' · '.rupiah($membership->price_per_session).'/main' }}</p>
            </div>
            <div class="ticket-balance"><strong>{{ (int) $membership->balance }}</strong><small>kuota</small></div>
            <footer>Mulai {{ $membership->starts_on->translatedFormat('d M Y') }} · {{ $membership->expires_on ? 'Sampai '.$membership->expires_on->translatedFormat('d M Y') : 'Tanpa kedaluwarsa' }}</footer>
        </article>
    @empty
        <div class="empty-state"><span>🏸</span><h2>Belum ada kuota</h2><p>Ajukan top up untuk mendapatkan kuota bermain.</p><a class="btn primary" href="{{ route('top-ups.index') }}">Ajukan top up</a></div>
    @endforelse
</div>

<div class="member-columns">
    <section class="card schedule-card">
        <div class="card-head"><div><span class="eyebrow">JADWAL</span><h2>Sesi berikutnya</h2></div></div>
        @forelse($upcomingSessions as $session)
            <div class="schedule-row"><time><b>{{ $session->scheduled_at->format('d') }}</b>{{ $session->scheduled_at->translatedFormat('M') }}</time><span><strong>{{ $session->venue_name }}</strong><small>{{ $session->court_name }} · {{ $session->scheduled_at->format('H:i') }} WITA</small></span><b>{{ rupiah($session->price_per_session) }}</b></div>
        @empty
            <div class="empty">Belum ada jadwal berikutnya.</div>
        @endforelse
    </section>

    <section class="card ledger-card">
        <div class="card-head"><div><span class="eyebrow">RIWAYAT</span><h2>Penggunaan kuota</h2></div></div>
        @forelse($transactions as $transaction)
            <div class="ledger-row"><span class="ledger-sign {{ $transaction->quantity > 0 ? 'plus' : 'minus' }}">{{ $transaction->quantity > 0 ? '+' : '−' }}</span><span><strong>{{ $transaction->notes }}</strong><small>{{ $transaction->membership->venue_name }} · {{ $transaction->created_at->translatedFormat('d M Y') }}</small></span><b>{{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}</b></div>
        @empty
            <div class="empty">Belum ada mutasi kuota.</div>
        @endforelse
    </section>
</div>
@endsection
