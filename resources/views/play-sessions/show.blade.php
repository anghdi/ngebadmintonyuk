@extends('layouts.app')
@section('title', 'Absensi '.$playSession->scheduled_at->translatedFormat('d M Y'))
@section('content')
<div class="page-head session-head"><div><a class="back-link" href="{{ route('play-sessions.index') }}">← Semua sesi</a><span class="eyebrow">ABSENSI SESI</span><h1>{{ $playSession->venue_name }}</h1><p>{{ $playSession->court_name }} · {{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} pukul {{ $playSession->scheduled_at->format('H:i') }} WITA</p></div><div class="session-head-actions"><div class="session-price"><small>HARGA SESI</small><strong>{{ rupiah($playSession->price_per_session) }}</strong></div><div class="actions"><a class="btn soft" href="{{ route('play-sessions.edit', $playSession) }}">Edit</a><form method="post" action="{{ route('play-sessions.destroy', $playSession) }}" onsubmit="return confirm('Hapus sesi ini? Absensi akan dihapus dan kuota yang terpakai akan dikembalikan.')">@csrf @method('delete')<button class="btn danger-bg">Hapus</button></form></div></div></div>

<section class="card table-card registration-admin-card">
    <div class="card-head"><div><span class="eyebrow">DAFTAR PEMAIN</span><h2>{{ $confirmedRegistrations->count() }}/{{ $playSession->max_players }} pemain · {{ $waitingRegistrations->count() }}/{{ $playSession->max_waiting_players }} waiting</h2></div><a class="btn soft" href="{{ route('public-sessions.show', $playSession) }}" target="_blank" rel="noopener">Lihat halaman publik</a></div>
    @if($registrations->count() < $playSession->max_players + $playSession->max_waiting_players)
        <details class="registration-create-panel">
            <summary>+ Tambah pemain</summary>
            <form method="post" action="{{ route('session-registrations.store', $playSession) }}" class="registration-create-form">
                @csrf
                <label>Akun pemain<select name="user_id" data-member-select required><option value="">Pilih akun</option>@foreach($members as $member)<option value="{{ $member->id }}" data-member-name="{{ $member->name }}" data-member-phone="{{ $member->phone }}">{{ $member->name }}{{ $member->phone ? ' · '.$member->phone : '' }}</option>@endforeach</select></label>
                <label>Pembayaran<select name="payment_method" required><option value="transfer">Transfer</option><option value="cash">Tunai</option></select></label>
                <button class="btn primary">Tambahkan</button>
            </form>
            <small>Pemain harus mempunyai akun. Nama dan WhatsApp (jika ada) mengikuti data akun.</small>
        </details>
    @else
        <div class="capacity-full-note">Slot utama dan waiting list sudah penuh.</div>
    @endif
    <div class="admin-list-legend"><span><i class="main"></i> Slot utama</span><span><i class="waiting"></i> Waiting list</span></div>
    <table class="registration-table"><thead><tr><th>SLOT</th><th>PEMAIN</th><th>PEMBAYARAN</th><th>STATUS</th><th>RIWAYAT</th><th>PERBARUI</th></tr></thead><tbody>
    @forelse($registrations as $registration)
        @php($noShows = (int) $noShowCounts->get($registration->user_id, 0))
        @php($isWaiting = $waitingRegistrations->contains('id', $registration->id))
        <tr @class(['waiting-registration-row' => $isWaiting])>
            <td><span @class(['queue-badge', 'waiting' => $isWaiting])>{{ $isWaiting ? 'W'.($waitingRegistrations->search(fn ($item) => $item->id === $registration->id) + 1) : '#'.($confirmedRegistrations->search(fn ($item) => $item->id === $registration->id) + 1) }}</span></td>
            <td><strong>{{ $registration->name }}</strong><small>{{ $registration->user ? 'Punya akun' : 'Data lama tanpa akun' }}{{ $registration->phone ? ' · '.$registration->phone : ' · WhatsApp tidak diisi' }}</small></td>
            <td>
                <form method="post" action="{{ route('session-registrations.payment', [$playSession, $registration]) }}" class="quick-payment-form">
                    @csrf @method('patch')
                    <select name="payment_method" aria-label="Metode pembayaran {{ $registration->name }}"><option value="transfer" @selected($registration->payment_method === 'transfer')>Transfer</option><option value="cash" @selected($registration->payment_method === 'cash')>Tunai</option></select>
                    <input type="hidden" name="is_paid" value="0">
                    <label class="payment-check"><input type="checkbox" name="is_paid" value="1" @checked($registration->payment_status === 'paid') @disabled($isWaiting)><span>{{ $registration->payment_status === 'paid' ? 'Lunas' : 'Belum' }}</span></label>
                    <button class="quick-save" @disabled($isWaiting)>Simpan</button>
                    @if($isWaiting)<small>Aktif setelah masuk slot utama</small>@endif
                </form>
            </td>
            <td><span class="status-pill {{ $registration->attendance_status === 'present' ? 'active' : ($registration->attendance_status === 'no_show' ? 'danger' : 'muted') }}">{{ ['listed' => 'Terdaftar', 'present' => 'Hadir', 'no_show' => 'Tidak hadir'][$registration->attendance_status] }}</span></td>
            <td><strong>{{ $noShows }}/3</strong><small>{{ $noShows >= 3 ? 'Diblokir' : 'Tidak hadir' }}</small></td>
            <td>
                <details class="registration-editor">
                    <summary>Edit data</summary>
                    <form method="post" action="{{ route('session-registrations.update', [$playSession, $registration]) }}" class="session-registration-form">
                        @csrf @method('put')
                        <label>Akun pemain<select name="user_id" data-member-select required><option value="">Pilih akun</option>@foreach($members as $member)<option value="{{ $member->id }}" data-member-name="{{ $member->name }}" data-member-phone="{{ $member->phone }}" @selected($registration->user_id === $member->id)>{{ $member->name }}</option>@endforeach</select></label>
                        <label>Nama<input name="name" value="{{ $registration->name }}" required></label>
                        <label>WhatsApp <span class="optional">Opsional</span><input name="phone" value="{{ $registration->phone }}" inputmode="numeric"></label>
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
        <tr><td colspan="6"><div class="empty">Belum ada pemain yang mengisi daftar.</div></td></tr>
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
