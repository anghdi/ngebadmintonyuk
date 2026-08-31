@extends('layouts.app')
@section('title', 'Sesi Main')
@section('content')
<div class="page-head"><div><span class="eyebrow">JADWAL &amp; ABSENSI</span><h1>Sesi bermain</h1><p>Buat jadwal, lalu tandai kehadiran dan pemakaian kuota.</p></div></div>

<div class="admin-split session-layout">
    <section class="card package-form-card">
        <span class="eyebrow">SESI BARU</span><h2>Jadwalkan main</h2>
        <form method="post" action="{{ route('play-sessions.store') }}" class="compact-form">
            @csrf
            <label>Tanggal dan jam<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required></label>
            <label>Venue<input name="venue_name" value="{{ old('venue_name') }}" required></label>
            <label>Lapangan<input name="court_name" value="{{ old('court_name') }}" required></label>
            <label>Harga per pemain<input type="number" name="price_per_session" value="{{ old('price_per_session', 25000) }}" min="0" required></label>
            <label>Catatan <span class="optional">Opsional</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
            <button class="btn primary full">Buat sesi &amp; buka absensi</button>
        </form>
    </section>

    <section class="card session-list-card">
        <div class="card-head"><div><span class="eyebrow">SEMUA SESI</span><h2>Jadwal komunitas</h2></div></div>
        @forelse($playSessions as $session)
            <a href="{{ route('play-sessions.show', $session) }}" class="session-row"><time><b>{{ $session->scheduled_at->format('d') }}</b>{{ $session->scheduled_at->translatedFormat('M') }}</time><span><strong>{{ $session->venue_name }}</strong><small>{{ $session->court_name }} · {{ $session->scheduled_at->format('H:i') }} WITA</small></span><span><b>{{ $session->attendances_count }}</b><small>tercatat</small></span><span aria-hidden="true">→</span></a>
        @empty
            <div class="empty-state"><span>◫</span><h2>Belum ada sesi</h2><p>Buat jadwal pertama dari formulir di samping.</p></div>
        @endforelse
        {{ $playSessions->links() }}
    </section>
</div>
@endsection
