@extends('layouts.app')
@section('title', 'Absensi '.$playSession->scheduled_at->translatedFormat('d M Y'))
@section('content')
<div class="page-head session-head"><div><a class="back-link" href="{{ route('play-sessions.index') }}">← Semua sesi</a><span class="eyebrow">ABSENSI SESI</span><h1>{{ $playSession->venue_name }}</h1><p>{{ $playSession->court_name }} · {{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} pukul {{ $playSession->scheduled_at->format('H:i') }} WITA</p></div><div class="session-price"><small>HARGA SESI</small><strong>{{ rupiah($playSession->price_per_session) }}</strong></div></div>

<div class="attendance-guide"><span><i class="guide-dot present"></i> Hadir: potong 1 kuota</span><span><i class="guide-dot absent"></i> Tidak hadir: kuota tetap</span><span><i class="guide-dot charged"></i> Absen dipotong: potong 1 kuota</span></div>

<section class="attendance-list">
    @forelse($members as $member)
        @php($attendance = $attendances->get($member->id))
        <article class="attendance-card">
            <div class="attendance-person"><span>{{ $member->initials() }}</span><div><strong>{{ $member->name }}</strong><small>Kuota cocok: {{ $compatibleBalances->get($member->id, 0) }}×</small></div></div>
            <form method="post" action="{{ route('attendances.update', [$playSession, $member]) }}">
                @csrf @method('put')
                <input type="text" name="notes" value="{{ $attendance?->notes }}" placeholder="Catatan opsional" aria-label="Catatan untuk {{ $member->name }}">
                <div class="attendance-actions">
                    <button name="status" value="present" @class(['selected' => $attendance?->status === 'present'])>Hadir</button>
                    <button name="status" value="absent" @class(['selected' => $attendance?->status === 'absent'])>Tidak hadir</button>
                    <button name="status" value="charged_absent" @class(['selected' => $attendance?->status === 'charged_absent'])>Absen dipotong</button>
                </div>
            </form>
        </article>
    @empty
        <div class="empty-state"><span>◎</span><h2>Belum ada member</h2><p>Member yang mendaftar akan muncul otomatis di sini.</p></div>
    @endforelse
</section>
@endsection
