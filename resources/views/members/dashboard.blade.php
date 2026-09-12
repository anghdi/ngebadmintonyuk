@extends('layouts.app')
@section('title', 'Membership Saya')
@section('content')
<div class="member-welcome">
    <div>
        <span class="eyebrow">Aktivitas saya</span>
        <h1>Selamat datang, {{ str($member->name)->before(' ') }}.</h1>
        <p>{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="actions">
        <a class="btn primary" href="{{ route('top-ups.index') }}">Top up kuota</a>
        <span class="member-number">Member #{{ str_pad((string) $member->id, 4, '0', STR_PAD_LEFT) }}</span>
    </div>
</div>

<x-push-notification-opt-in />

<section class="member-summary-strip" aria-label="Ringkasan member">
    <div><small>Kuota</small><strong>{{ $remainingCredits }}</strong><span>tersedia</span></div>
    <div><small>Kehadiran</small><strong>{{ $attendanceCount }}</strong><span>kali</span></div>
    <div><small>Paket</small><strong>{{ $memberships->count() }}</strong><span>aktif</span></div>
    <div><small>Terpakai</small><strong>{{ $usedCredits }}</strong><span>kuota</span></div>
</section>

<section class="member-cash-summary" aria-labelledby="member-cash-title">
    <div><span class="eyebrow">Kas · {{ $today->translatedFormat('F Y') }}</span><h2 id="member-cash-title">Bulan berjalan</h2></div>
    <dl>
        <div><dt>Masuk</dt><dd class="income">{{ rupiah($currentCashReport['totalIncome']) }}</dd></div>
        <div><dt>Keluar</dt><dd class="expense">{{ rupiah($currentCashReport['totalExpense']) }}</dd></div>
        <div><dt>Selisih</dt><dd>{{ rupiah($currentCashReport['difference']) }}</dd></div>
        <div><dt>Saldo saat ini</dt><dd>{{ rupiah($currentCashReport['balance']) }}</dd></div>
    </dl>
</section>

<section class="member-moments" aria-labelledby="member-moments-title">
    <div class="section-heading">
        <div><span class="eyebrow">Komunitas</span><h2 id="member-moments-title">Momen di lapangan</h2></div>
        <small>Geser untuk melihat</small>
    </div>
    <div class="member-moments-track">
        @foreach([
            ['member-action-01.webp', 'Pemain menjangkau kok di depan net'],
            ['member-action-02.webp', 'Pemain mengejar kok saat pertandingan'],
            ['member-action-03.webp', 'Pemain bersiap menerima servis'],
            ['member-action-04.webp', 'Pemain melakukan pukulan di lapangan'],
            ['member-action-05.webp', 'Pasangan pemain bersiap melanjutkan rally'],
            ['member-action-06.webp', 'Dua pemain bersiap menerima kok'],
        ] as [$image, $description])
            <figure>
                <img src="{{ asset('images/'.$image) }}" alt="{{ $description }}" width="720" height="960" loading="lazy" decoding="async">
            </figure>
        @endforeach
    </div>
</section>

<div class="member-columns">
    <section class="compact-dashboard-section schedule-card">
        <div class="card-head"><div><span class="eyebrow">Jadwal</span><h2>Sesi yang kamu ikuti</h2></div></div>
        @forelse($upcomingSessions as $session)
            <div class="schedule-row"><time><b>{{ $session->scheduled_at->format('d') }}</b>{{ $session->scheduled_at->translatedFormat('M') }}</time><span><strong>{{ $session->venue_name }}</strong><small>{{ $session->court_name }} · {{ $session->scheduled_at->format('H:i') }} WITA</small></span><b>{{ rupiah($session->price_per_session) }}</b></div>
        @empty
            <div class="empty">Kamu belum mengikuti sesi mendatang.</div>
        @endforelse
    </section>

    <section class="compact-dashboard-section ledger-card">
        <div class="card-head"><div><span class="eyebrow">Riwayat</span><h2>Penggunaan kuota</h2></div></div>
        @forelse($transactions as $transaction)
            <div class="ledger-row"><span class="ledger-sign {{ $transaction->quantity > 0 ? 'plus' : 'minus' }}">{{ $transaction->quantity > 0 ? '+' : '−' }}</span><span><strong>{{ $transaction->notes }}</strong><small>{{ $transaction->membership->venue_name }} · {{ $transaction->created_at->translatedFormat('d M Y') }}</small></span><b>{{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}</b></div>
        @empty
            <div class="empty">Belum ada mutasi kuota.</div>
        @endforelse
    </section>
</div>

<section class="compact-dashboard-section member-package-section">
    <div class="card-head"><div><span class="eyebrow">Paket</span><h2>Kuota bermain</h2></div><a href="{{ route('top-ups.index') }}">Kelola top up</a></div>
    @forelse($memberships as $membership)
        <div class="member-package-row">
            <span><strong>{{ $membership->venue_name }}</strong><small>{{ $membership->isCommunityPackage() ? 'Semua sesi komunitas' : $membership->court_name }}</small></span>
            <span class="status-pill {{ (int) $membership->balance > 0 ? 'active' : 'muted' }}">{{ (int) $membership->balance }} kuota</span>
        </div>
    @empty
        <div class="empty">Belum ada paket. Ajukan top up untuk mulai bermain.</div>
    @endforelse
</section>
@endsection
