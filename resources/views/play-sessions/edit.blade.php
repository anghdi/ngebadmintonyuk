@extends('layouts.app')

@section('title', 'Edit Sesi Bermain')

@section('content')
    <div class="page-head">
        <div>
            <a class="back-link" href="{{ route('play-sessions.show', $playSession) }}">← Kembali ke sesi</a>
            <span class="eyebrow">SESI BERMAIN</span>
            <h1>Edit sesi</h1>
            <p>Perbarui jadwal dan status sesi.</p>
        </div>
    </div>

    <form method="post" action="{{ route('play-sessions.update', $playSession) }}" class="card detail-card compact-form">
        @csrf @method('put')
        <label>Tanggal dan jam<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $playSession->scheduled_at->format('Y-m-d\TH:i')) }}" required></label>
        <div class="form-grid">
            <label>Venue<input name="venue_name" value="{{ old('venue_name', $playSession->venue_name) }}" required></label>
            <label>Lapangan<input name="court_name" value="{{ old('court_name', $playSession->court_name) }}" required></label>
            <label>Harga per pemain<input type="number" name="price_per_session" value="{{ old('price_per_session', $playSession->price_per_session) }}" min="0" required></label>
            <label>Maksimal pemain<input type="number" name="max_players" value="{{ old('max_players', $playSession->max_players) }}" min="1" max="200" required></label>
            <label>Status<select name="status" required><option value="scheduled" @selected(old('status', $playSession->status) === 'scheduled')>Terjadwal</option><option value="completed" @selected(old('status', $playSession->status) === 'completed')>Selesai</option><option value="cancelled" @selected(old('status', $playSession->status) === 'cancelled')>Dibatalkan</option></select></label>
        </div>
        <label>Catatan <span class="optional">Opsional</span><textarea name="notes" rows="3">{{ old('notes', $playSession->notes) }}</textarea></label>
        <div class="actions"><a class="btn soft" href="{{ route('play-sessions.show', $playSession) }}">Batal</a><button class="btn primary">Simpan perubahan</button></div>
    </form>
@endsection
