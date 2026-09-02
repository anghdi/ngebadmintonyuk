@extends('layouts.app')
@section('title', 'Sesi Main')
@section('content')
<div class="page-head"><div><span class="eyebrow">SESI BERMAIN</span><h1>Jadwal komunitas</h1><p>Kelola jadwal dan kehadiran member.</p></div></div>

<div class="admin-split session-layout">
    <section class="card package-form-card">
        <span class="eyebrow">SESI BARU</span><h2>Buat jadwal</h2>
        <form method="post" action="{{ route('play-sessions.store') }}" class="compact-form">
            @csrf
            <label>Tanggal dan jam<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required></label>
            <label>Venue<input name="venue_name" value="{{ old('venue_name') }}" required></label>
            <label>Lapangan<input name="court_name" value="{{ old('court_name') }}" required></label>
            <label>Harga per pemain<input type="number" name="price_per_session" value="{{ old('price_per_session', 25000) }}" min="0" required></label>
            <label>Maksimal pemain<input type="number" name="max_players" value="{{ old('max_players', 12) }}" min="1" max="200" required></label>
            <label>Catatan <span class="optional">Opsional</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
            <button class="btn primary full">Buat jadwal</button>
        </form>
    </section>

    <section class="card session-list-card">
        <div class="card-head"><div><span class="eyebrow">AGENDA</span><h2>Daftar sesi</h2></div></div>
        <form method="get" action="{{ route('play-sessions.index') }}" class="schedule-month-form admin-month-form">
            <label for="admin-month">Pilih bulan</label>
            <select id="admin-month" name="month" required>
                <option value="">Pilih bulan</option>
                @foreach($availableMonths as $month)
                    <option value="{{ $month['value'] }}" @selected($selectedMonth === $month['value'])>{{ $month['label'] }}</option>
                @endforeach
            </select>
            <button class="btn primary">Tampilkan</button>
        </form>
        @if($selectedMonth)
            <p class="selected-month-label">Menampilkan {{ $selectedMonthLabel }}</p>
        @endif
        @forelse($playSessions as $session)
            <article class="session-row"><time><b>{{ $session->scheduled_at->format('d') }}</b>{{ $session->scheduled_at->translatedFormat('M') }}</time><span><a href="{{ route('play-sessions.show', $session) }}"><strong>{{ $session->venue_name }}</strong></a><small>{{ $session->court_name }} · {{ $session->scheduled_at->format('H:i') }} WITA</small></span><span><b>{{ $session->registrations_count }}/{{ $session->max_players }}</b><small>pemain</small></span><div class="row-actions"><a class="link" href="{{ route('play-sessions.edit', $session) }}">Edit</a><form method="post" action="{{ route('play-sessions.destroy', $session) }}" onsubmit="return confirm('Hapus sesi ini?')">@csrf @method('delete')<button class="link danger">Hapus</button></form></div></article>
        @empty
            <div class="empty-state"><span>◫</span><h2>{{ $selectedMonth ? 'Belum ada jadwal' : 'Pilih bulan terlebih dahulu' }}</h2><p>{{ $selectedMonth ? 'Tidak ada sesi pada bulan ini.' : 'Pilih bulan untuk melihat dan mengelola sesi.' }}</p></div>
        @endforelse
        {{ $playSessions->links() }}
    </section>
</div>
@endsection
