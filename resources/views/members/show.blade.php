@extends('layouts.app')
@section('title', $member->name)
@section('content')
<div class="page-head"><div><a class="back-link" href="{{ route('members.index') }}">← Semua member</a><h1>{{ $member->name }}</h1><p>{{ $member->email }}{{ $member->phone ? ' · '.$member->phone : '' }}</p></div><div class="actions"><span class="member-number">MEMBER #{{ str_pad((string) $member->id, 4, '0', STR_PAD_LEFT) }}</span><form method="post" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Hapus member beserta paket dan seluruh riwayatnya?')">@csrf @method('delete')<button class="btn danger-bg">Hapus member</button></form></div></div>

<section class="card member-edit-card">
    <div><span class="eyebrow">DATA MEMBER</span><h2>Informasi akun</h2></div>
    <form method="post" action="{{ route('members.update', $member) }}" class="setting-form">
        @csrf @method('put')
        <label>Nama<input name="name" value="{{ old('name', $member->name) }}" required></label>
        <label>Email<input type="email" name="email" value="{{ old('email', $member->email) }}" required></label>
        <label>WhatsApp<input name="phone" value="{{ old('phone', $member->phone) }}"></label>
        <button class="btn primary">Simpan</button>
    </form>
</section>

<div class="admin-split">
    <section class="card package-form-card">
        <span class="eyebrow">TAMBAH PAKET</span>
        <h2>Berikan kuota main</h2>
        <p>Atur kuota bermain member.</p>
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
                <details class="membership-editor">
                    <summary class="package-row"><div><strong>{{ $membership->venue_name }}</strong><small>{{ $membership->court_name }} · {{ rupiah($membership->price_per_session) }}/main</small></div><div><b>{{ (int) $membership->balance }} / {{ $membership->initial_credits }}</b><small>{{ $membership->status === 'active' ? 'Aktif' : 'Nonaktif' }} · Edit</small></div></summary>
                    <form method="post" action="{{ route('memberships.update', [$member, $membership]) }}" class="compact-form membership-edit-form">
                        @csrf @method('put')
                        <div class="form-grid"><label>Venue<input name="venue_name" value="{{ $membership->venue_name }}" required></label><label>Lapangan<input name="court_name" value="{{ $membership->court_name }}" required></label><label>Harga per main<input type="number" name="price_per_session" value="{{ $membership->price_per_session }}" min="0" required></label><label>Status<select name="status"><option value="active" @selected($membership->status === 'active')>Aktif</option><option value="inactive" @selected($membership->status === 'inactive')>Nonaktif</option></select></label><label>Mulai berlaku<input type="date" name="starts_on" value="{{ $membership->starts_on->format('Y-m-d') }}" required></label><label>Kedaluwarsa<input type="date" name="expires_on" value="{{ $membership->expires_on?->format('Y-m-d') }}"></label></div>
                        <label>Catatan<textarea name="notes" rows="2">{{ $membership->notes }}</textarea></label>
                        <div class="actions"><button class="btn primary">Simpan paket</button></div>
                    </form>
                    @if((int) $membership->attendances_count === 0 && (int) $membership->top_up_requests_count === 0)
                        <form method="post" action="{{ route('memberships.destroy', [$member, $membership]) }}" class="membership-delete" onsubmit="return confirm('Hapus paket ini?')">@csrf @method('delete')<button class="link danger">Hapus paket</button></form>
                    @endif
                </details>
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
