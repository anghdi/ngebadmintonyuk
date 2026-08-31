@extends('layouts.app')
@section('title', $member->name)
@section('content')
<div class="page-head"><div><a class="back-link" href="{{ route('members.index') }}">← Semua member</a><h1>{{ $member->name }}</h1><p>{{ $member->email }}{{ $member->phone ? ' · '.$member->phone : '' }}</p></div><span class="member-number">MEMBER #{{ str_pad((string) $member->id, 4, '0', STR_PAD_LEFT) }}</span></div>

<div class="admin-split">
    <section class="card package-form-card">
        <span class="eyebrow">TAMBAH PAKET</span>
        <h2>Berikan kuota main</h2>
        <p>Lapangan dan harga menjadi identitas kuota carry-over.</p>
        <form method="post" action="{{ route('memberships.store', $member) }}" class="compact-form">
            @csrf
            <label>Venue<input name="venue_name" value="{{ old('venue_name') }}" placeholder="Contoh: GOR Bulutangkis" required></label>
            <label>Lapangan<input name="court_name" value="{{ old('court_name') }}" placeholder="Contoh: Lapangan 1" required></label>
            <div class="form-grid">
                <label>Harga per main<input type="number" name="price_per_session" value="{{ old('price_per_session', 25000) }}" min="0" required></label>
                <label>Jumlah kuota<input type="number" name="initial_credits" value="{{ old('initial_credits', 4) }}" min="1" max="100" required></label>
                <label>Mulai berlaku<input type="date" name="starts_on" value="{{ old('starts_on', today()->toDateString()) }}" required></label>
                <label>Kedaluwarsa <span class="optional">Opsional</span><input type="date" name="expires_on" value="{{ old('expires_on') }}"></label>
            </div>
            <label>Catatan <span class="optional">Opsional</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
            <button class="btn primary full">Tambahkan paket</button>
        </form>
    </section>

    <div>
        <section class="card">
            <div class="card-head"><div><span class="eyebrow">PAKET MEMBER</span><h2>Sisa kuota</h2></div><strong class="credit-total">{{ (int) $member->memberships->sum('balance') }}×</strong></div>
            @forelse($member->memberships as $membership)
                <div class="package-row"><div><strong>{{ $membership->venue_name }}</strong><small>{{ $membership->court_name }} · {{ rupiah($membership->price_per_session) }}/main</small></div><div><b>{{ (int) $membership->balance }} / {{ $membership->initial_credits }}</b><small>{{ $membership->starts_on->translatedFormat('d M Y') }}</small></div></div>
            @empty
                <div class="empty">Belum ada paket.</div>
            @endforelse
        </section>

        <section class="card">
            <div class="card-head"><div><span class="eyebrow">KEHADIRAN</span><h2>Riwayat terakhir</h2></div></div>
            @forelse($member->attendances as $attendance)
                <div class="package-row"><div><strong>{{ $attendance->playSession->scheduled_at->translatedFormat('d M Y') }}</strong><small>{{ $attendance->playSession->venue_name }} · {{ $attendance->playSession->court_name }}</small></div><span class="status-pill {{ $attendance->status === 'present' ? 'active' : ($attendance->status === 'charged_absent' ? 'warning' : 'muted') }}">{{ ['present' => 'Hadir', 'absent' => 'Tidak hadir', 'charged_absent' => 'Absen dipotong'][$attendance->status] }}</span></div>
            @empty
                <div class="empty">Belum ada riwayat kehadiran.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
