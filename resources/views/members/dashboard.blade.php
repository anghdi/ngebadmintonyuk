@extends('layouts.app')
@section('title', 'Membership Saya')
@section('content')
<div class="member-welcome">
    <div>
        <span class="eyebrow">DASHBOARD MEMBER</span>
        <h1>Selamat datang, {{ str($member->name)->before(' ') }}.</h1>
        <p>{{ now()->translatedFormat('l, d F Y') }} · Ringkasan aktivitas komunitasmu.</p>
    </div>
    <div class="actions">
        <a class="btn primary" href="{{ route('top-ups.index') }}">Top up kuota</a>
        <span class="member-number">MEMBER #{{ str_pad((string) $member->id, 4, '0', STR_PAD_LEFT) }}</span>
    </div>
</div>

<x-push-notification-opt-in />

<section class="member-summary-strip" aria-label="Ringkasan member">
    <div><small>KUOTA</small><strong>{{ $remainingCredits }}</strong><span>siap dipakai</span></div>
    <div><small>KEHADIRAN</small><strong>{{ $attendanceCount }}</strong><span>kali bermain</span></div>
    <div><small>PAKET</small><strong>{{ $memberships->count() }}</strong><span>terdaftar</span></div>
    <div><small>TERPAKAI</small><strong>{{ $usedCredits }}</strong><span>kuota</span></div>
</section>

<section class="member-cash-summary" aria-labelledby="member-cash-title">
    <div><span class="eyebrow">KAS KOMUNITAS · {{ $today->translatedFormat('F Y') }}</span><h2 id="member-cash-title">Laporan bulan berjalan</h2></div>
    <dl>
        <div><dt>Masuk</dt><dd class="income">{{ rupiah($currentCashReport['totalIncome']) }}</dd></div>
        <div><dt>Keluar</dt><dd class="expense">{{ rupiah($currentCashReport['totalExpense']) }}</dd></div>
        <div><dt>Selisih</dt><dd>{{ rupiah($currentCashReport['difference']) }}</dd></div>
        <div><dt>Saldo saat ini</dt><dd>{{ rupiah($currentCashReport['balance']) }}</dd></div>
    </dl>
</section>

<div class="member-columns">
    <section class="compact-dashboard-section schedule-card">
        <div class="card-head"><div><span class="eyebrow">JADWAL SAYA</span><h2>Sesi yang kamu ikuti</h2></div></div>
        @forelse($upcomingSessions as $session)
            <div class="schedule-row"><time><b>{{ $session->scheduled_at->format('d') }}</b>{{ $session->scheduled_at->translatedFormat('M') }}</time><span><strong>{{ $session->venue_name }}</strong><small>{{ $session->court_name }} · {{ $session->scheduled_at->format('H:i') }} WITA</small></span><b>{{ rupiah($session->price_per_session) }}</b></div>
        @empty
            <div class="empty">Kamu belum mengikuti sesi mendatang.</div>
        @endforelse
    </section>

    <section class="compact-dashboard-section ledger-card">
        <div class="card-head"><div><span class="eyebrow">RIWAYAT</span><h2>Penggunaan kuota</h2></div></div>
        @forelse($transactions as $transaction)
            <div class="ledger-row"><span class="ledger-sign {{ $transaction->quantity > 0 ? 'plus' : 'minus' }}">{{ $transaction->quantity > 0 ? '+' : '−' }}</span><span><strong>{{ $transaction->notes }}</strong><small>{{ $transaction->membership->venue_name }} · {{ $transaction->created_at->translatedFormat('d M Y') }}</small></span><b>{{ $transaction->quantity > 0 ? '+' : '' }}{{ $transaction->quantity }}</b></div>
        @empty
            <div class="empty">Belum ada mutasi kuota.</div>
        @endforelse
    </section>
</div>

<section class="compact-dashboard-section member-package-section">
    <div class="card-head"><div><span class="eyebrow">PAKET</span><h2>Kuota bermain</h2></div><a href="{{ route('top-ups.index') }}">Kelola top up →</a></div>
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
