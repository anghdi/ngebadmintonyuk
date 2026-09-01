@extends('layouts.app')
@section('title', 'Absensi '.$playSession->scheduled_at->translatedFormat('d M Y'))
@section('content')
<div class="page-head session-head"><div><a class="back-link" href="{{ route('play-sessions.index') }}">← Semua sesi</a><span class="eyebrow">ABSENSI SESI</span><h1>{{ $playSession->venue_name }}</h1><p>{{ $playSession->court_name }} · {{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} pukul {{ $playSession->scheduled_at->format('H:i') }} WITA</p></div><div class="session-head-actions"><div class="session-price"><small>HARGA SESI</small><strong>{{ rupiah($playSession->price_per_session) }}</strong></div><div class="actions"><a class="btn soft" href="{{ route('play-sessions.edit', $playSession) }}">Edit</a><form method="post" action="{{ route('play-sessions.destroy', $playSession) }}" onsubmit="return confirm('Hapus sesi ini? Absensi akan dihapus dan kuota yang terpakai akan dikembalikan.')">@csrf @method('delete')<button class="btn danger-bg">Hapus</button></form></div></div></div>

<section class="card table-card registration-admin-card">
    <div class="card-head"><div><span class="eyebrow">DAFTAR PEMAIN</span><h2>Kehadiran dan pembayaran</h2></div><a class="btn soft" href="{{ route('public-sessions.show', $playSession) }}" target="_blank" rel="noopener">Lihat halaman publik</a></div>
    <table class="registration-table"><thead><tr><th>PEMAIN</th><th>PEMBAYARAN</th><th>STATUS</th><th>RIWAYAT</th><th>PERBARUI</th></tr></thead><tbody>
    @forelse($registrations as $registration)
        @php($noShows = (int) $noShowCounts->get($registration->phone, 0))
        <tr>
            <td><strong>{{ $registration->name }}</strong><small>{{ $registration->user ? 'Member' : 'Nonmember' }} · {{ $registration->phone }}</small></td>
            <td><span class="status-pill {{ $registration->payment_status === 'paid' ? 'active' : 'warning' }}">{{ $registration->payment_method === 'transfer' ? 'Transfer' : 'Tunai' }} · {{ $registration->payment_status === 'paid' ? 'Lunas' : 'Belum bayar' }}</span></td>
            <td><span class="status-pill {{ $registration->attendance_status === 'present' ? 'active' : ($registration->attendance_status === 'no_show' ? 'danger' : 'muted') }}">{{ ['listed' => 'Terdaftar', 'present' => 'Hadir', 'no_show' => 'Tidak hadir'][$registration->attendance_status] }}</span></td>
            <td><strong>{{ $noShows }}/3</strong><small>{{ $noShows >= 3 ? 'Diblokir' : 'Tidak hadir' }}</small></td>
            <td>
                <form method="post" action="{{ route('session-registrations.update', [$playSession, $registration]) }}" class="session-registration-form">
                    @csrf @method('put')
                    <select name="payment_status" aria-label="Status pembayaran {{ $registration->name }}"><option value="unpaid" @selected($registration->payment_status === 'unpaid')>Belum bayar</option><option value="paid" @selected($registration->payment_status === 'paid')>Lunas</option></select>
                    <select name="attendance_status" aria-label="Status kehadiran {{ $registration->name }}"><option value="listed" @selected($registration->attendance_status === 'listed')>Terdaftar</option><option value="present" @selected($registration->attendance_status === 'present')>Hadir</option><option value="no_show" @selected($registration->attendance_status === 'no_show')>Tidak hadir</option></select>
                    <input name="admin_notes" value="{{ $registration->admin_notes }}" placeholder="Catatan" aria-label="Catatan admin untuk {{ $registration->name }}">
                    <button class="btn primary">Simpan</button>
                </form>
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
