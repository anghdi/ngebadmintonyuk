@extends('layouts.app')
@section('title', 'Absensi '.$playSession->scheduled_at->translatedFormat('d M Y'))
@section('content')
<div class="page-head session-head"><div><a class="back-link" href="{{ route('play-sessions.index') }}">← Semua sesi</a><span class="eyebrow">ABSENSI SESI</span><h1>{{ $playSession->venue_name }}</h1><p>{{ $playSession->court_name }} · {{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} pukul {{ $playSession->scheduled_at->format('H:i') }} WITA</p></div><div class="session-head-actions"><div class="session-price"><small>HARGA SESI</small><strong>{{ rupiah($playSession->price_per_session) }}</strong></div><div class="actions"><a class="btn soft" href="{{ route('play-sessions.edit', $playSession) }}">Edit</a><form method="post" action="{{ route('play-sessions.destroy', $playSession) }}" onsubmit="return confirm('Hapus sesi ini? Absensi akan dihapus dan kuota yang terpakai akan dikembalikan.')">@csrf @method('delete')<button class="btn danger-bg">Hapus</button></form></div></div></div>

<section class="card table-card registration-admin-card">
    <div class="card-head"><div><span class="eyebrow">DAFTAR PEMAIN</span><h2>Kehadiran dan pembayaran</h2></div><a class="btn soft" href="{{ route('public-sessions.show', $playSession) }}" target="_blank" rel="noopener">Lihat halaman publik</a></div>
    <details class="registration-create-panel">
        <summary>+ Tambah pemain</summary>
        <form method="post" action="{{ route('session-registrations.store', $playSession) }}" class="registration-create-form">
            @csrf
            <label>Akun member<select name="user_id" data-member-select><option value="">Nonmember</option>@foreach($members as $member)<option value="{{ $member->id }}" data-member-name="{{ $member->name }}" data-member-phone="{{ $member->phone }}">{{ $member->name }}{{ $member->phone ? ' · '.$member->phone : '' }}</option>@endforeach</select></label>
            <label>Nama pemain<input name="name" placeholder="Otomatis jika member"></label>
            <label>WhatsApp<input name="phone" inputmode="numeric" placeholder="Otomatis jika member"></label>
            <label>Pembayaran<select name="payment_method" required><option value="transfer">Transfer</option><option value="cash">Tunai</option></select></label>
            <button class="btn primary">Tambahkan</button>
        </form>
        <small>Pilih akun member agar nama mengikuti data member.</small>
    </details>
    <table class="registration-table"><thead><tr><th>PEMAIN</th><th>PEMBAYARAN</th><th>STATUS</th><th>RIWAYAT</th><th>PERBARUI</th></tr></thead><tbody>
    @forelse($registrations as $registration)
        @php($noShows = (int) $noShowCounts->get($registration->phone, 0))
        <tr>
            <td><strong>{{ $registration->name }}</strong><small>{{ $registration->user ? 'Member' : 'Nonmember' }} · {{ $registration->phone }}</small></td>
            <td><span class="status-pill {{ $registration->payment_status === 'paid' ? 'active' : 'warning' }}">{{ $registration->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }} · {{ $registration->payment_status === 'paid' ? 'Lunas' : 'Belum bayar' }}</span></td>
            <td><span class="status-pill {{ $registration->attendance_status === 'present' ? 'active' : ($registration->attendance_status === 'no_show' ? 'danger' : 'muted') }}">{{ ['listed' => 'Terdaftar', 'present' => 'Hadir', 'no_show' => 'Tidak hadir'][$registration->attendance_status] }}</span></td>
            <td><strong>{{ $noShows }}/3</strong><small>{{ $noShows >= 3 ? 'Diblokir' : 'Tidak hadir' }}</small></td>
            <td>
                <details class="registration-editor">
                    <summary>Edit data</summary>
                    <form method="post" action="{{ route('session-registrations.update', [$playSession, $registration]) }}" class="session-registration-form">
                        @csrf @method('put')
                        <label>Member<select name="user_id" data-member-select><option value="">Nonmember</option>@foreach($members as $member)<option value="{{ $member->id }}" data-member-name="{{ $member->name }}" data-member-phone="{{ $member->phone }}" @selected($registration->user_id === $member->id)>{{ $member->name }}</option>@endforeach</select></label>
                        <label>Nama<input name="name" value="{{ $registration->name }}" required></label>
                        <label>WhatsApp<input name="phone" value="{{ $registration->phone }}" inputmode="numeric" required></label>
                        <label>Metode<select name="payment_method"><option value="transfer" @selected($registration->payment_method === 'transfer')>Transfer</option><option value="cash" @selected($registration->payment_method === 'cash')>Tunai</option></select></label>
                        <label>Pembayaran<select name="payment_status"><option value="unpaid" @selected($registration->payment_status === 'unpaid')>Belum bayar</option><option value="paid" @selected($registration->payment_status === 'paid')>Lunas</option></select></label>
                        <label>Kehadiran<select name="attendance_status"><option value="listed" @selected($registration->attendance_status === 'listed')>Terdaftar</option><option value="present" @selected($registration->attendance_status === 'present')>Hadir</option><option value="no_show" @selected($registration->attendance_status === 'no_show')>Tidak hadir</option></select></label>
                        <label class="registration-notes">Catatan<input name="admin_notes" value="{{ $registration->admin_notes }}"></label>
                        <button class="btn primary">Simpan</button>
                    </form>
                    @if($registration->attendance_status === 'listed' && $registration->payment_status === 'unpaid')
                        <form method="post" action="{{ route('session-registrations.destroy', [$playSession, $registration]) }}" class="registration-delete" onsubmit="return confirm('Hapus pemain ini dari daftar?')">@csrf @method('delete')<button class="link danger">Hapus dari daftar</button></form>
                    @endif
                </details>
            </td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="empty">Belum ada pemain yang mengisi daftar.</div></td></tr>
    @endforelse
    </tbody></table>
</section>

<div class="attendance-guide"><strong>KUOTA MEMBER</strong><span><i class="guide-dot present"></i> Hadir: potong 1 kuota</span><span><i class="guide-dot absent"></i> Tidak hadir: kuota tetap</span><span><i class="guide-dot charged"></i> Absen dipotong: potong 1 kuota</span></div>

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
