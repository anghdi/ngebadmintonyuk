@extends('layouts.app')
@section('title', 'Laporan Member')
@section('content')
<div class="page-head"><div><h1>Laporan Member</h1><p>Kehadiran, pembayaran, dan ulang tahun komunitas.</p></div><a class="btn dark" data-no-loading href="{{ route('member-reports.pdf', \Illuminate\Support\Arr::except($filters, ['page'])) }}">Unduh PDF</a></div>
<nav class="flex flex-wrap gap-2 mb-4" aria-label="Jenis laporan">
    @foreach(['members' => 'Member', 'guests' => 'Tamu'] as $type => $label)
        <a @class(['btn', 'primary' => $filters['type'] === $type, 'soft' => $filters['type'] !== $type]) href="{{ route('member-reports.index', array_replace(\Illuminate\Support\Arr::except($filters, ['page', 'profile', 'birthday_month']), ['type' => $type])) }}">{{ $label }}</a>
    @endforeach
</nav>
<section class="card mb-4">
    <form method="get" action="{{ route('member-reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <input type="hidden" name="type" value="{{ $filters['type'] }}">
        <label>Dari<input type="date" name="start_date" value="{{ $filters['start_date'] }}" required></label>
        <label>Sampai<input type="date" name="end_date" value="{{ $filters['end_date'] }}" required></label>
        <label>Cari nama<input name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Nama {{ $filters['type'] === 'members' ? 'member' : 'tamu' }}"></label>
        @if($filters['type'] === 'members')
            <label>Profil<select name="profile"><option value="">Semua profil</option><option value="complete" @selected(($filters['profile'] ?? '') === 'complete')>Lengkap</option><option value="incomplete" @selected(($filters['profile'] ?? '') === 'incomplete')>Belum lengkap</option></select></label>
            <label>Bulan ulang tahun<select name="birthday_month"><option value="">Semua bulan</option>@for($month = 1; $month <= 12; $month++)<option value="{{ $month }}" @selected((int) ($filters['birthday_month'] ?? 0) === $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->translatedFormat('F') }}</option>@endfor</select></label>
        @endif
        <div class="actions self-end"><button class="btn primary">Tampilkan</button><a class="btn soft" href="{{ route('member-reports.index', ['type' => $filters['type']]) }}">Reset</a></div>
    </form>
</section>
<section class="card mb-4">
    <dl class="flex flex-wrap gap-x-8 gap-y-3 m-0">
        <div><dt class="text-xs text-gray-500">{{ $filters['type'] === 'members' ? 'Member' : 'Tamu' }} ditemukan</dt><dd class="m-0 font-bold text-lg">{{ $summary['total'] }}</dd></div>
        @if($filters['type'] === 'members')<div><dt class="text-xs text-gray-500">Profil lengkap / belum</dt><dd class="m-0 font-bold text-lg">{{ $summary['complete'] }} / {{ $summary['incomplete'] }}</dd></div>@endif
        <div><dt class="text-xs text-gray-500">Hadir / tidak hadir</dt><dd class="m-0 font-bold text-lg">{{ $summary['present'] }} / {{ $summary['absent'] }}</dd></div>
        <div><dt class="text-xs text-gray-500">Iuran tunai & transfer</dt><dd class="m-0 font-bold text-lg">{{ rupiah($summary['payment']) }}</dd></div>
        @if($filters['type'] === 'members')<div><dt class="text-xs text-gray-500">Top up disetujui</dt><dd class="m-0 font-bold text-lg">{{ rupiah($summary['top_up']) }}</dd></div>@endif
    </dl>
    <small>Ringkasan mengikuti filter, bukan hanya halaman ini. Kuota dan usia adalah kondisi saat ini.</small>
</section>
@if($filters['type'] === 'members')
<section class="card mb-4">
    <div class="card-head"><h2>Ulang tahun</h2><small>Seluruh member · Hari ini & 7 hari ke depan</small></div>
    <div class="flex flex-wrap gap-3">
    @forelse($birthdays as $birthday)
        <div class="flex flex-wrap items-center gap-3 border border-gray-200 rounded-xl px-3 py-2">
            <div><strong>{{ $birthday['name'] }}</strong><small>{{ $birthday['today'] ? 'Hari ini' : \Illuminate\Support\Carbon::parse($birthday['date'])->translatedFormat('d M') }} · {{ $birthday['age'] }} tahun</small></div>
            @if($birthday['today'])<a class="btn soft" href="{{ route('push-notifications.index', ['birthday' => $birthday['id']]) }}">Buat ucapan</a>@endif
        </div>
    @empty
        <span class="text-sm text-gray-500">Tidak ada ulang tahun dalam 7 hari ke depan.</span>
    @endforelse
    </div>
</section>
@endif
<section class="card table-card">
    <div class="card-head"><h2>{{ $filters['type'] === 'members' ? 'Daftar member' : 'Daftar tamu' }}</h2><small>{{ \Illuminate\Support\Carbon::parse($filters['start_date'])->translatedFormat('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->translatedFormat('d M Y') }}</small></div>
    <table><thead><tr><th>NAMA</th>@if($filters['type'] === 'members')<th>PROFIL</th><th>TANGGAL LAHIR</th><th>USIA</th>@endif<th>HADIR</th><th>TIDAK HADIR</th>@if($filters['type'] === 'members')<th>SISA KUOTA</th>@endif<th>IURAN</th>@if($filters['type'] === 'members')<th>TOP UP</th>@endif</tr></thead><tbody>
    @forelse($rows as $row)
        <tr><td><a href="{{ $filters['type'] === 'members' ? route('members.show', $row['id']) : route('guests.index') }}"><strong>{{ $row['name'] }}</strong></a><small>{{ $row['phone'] ?: 'WhatsApp belum diisi' }}</small></td>
        @if($filters['type'] === 'members')<td><span class="status-pill {{ $row['complete'] ? 'active' : 'muted' }}">{{ $row['complete'] ? 'Lengkap' : 'Belum lengkap' }}</span></td><td>{{ $row['birth_date'] ? \Illuminate\Support\Carbon::parse($row['birth_date'])->translatedFormat('d M Y') : 'Belum diisi' }}</td><td>{{ $row['age'] !== null ? $row['age'].' th' : '-' }}</td>@endif
        <td>{{ $row['present'] }}×</td><td>{{ $row['absent'] }}×</td>@if($filters['type'] === 'members')<td>{{ $row['quota'] }}×</td>@endif<td>{{ rupiah($row['payment']) }}</td>@if($filters['type'] === 'members')<td>{{ rupiah($row['top_up']) }}</td>@endif</tr>
    @empty
        <tr><td colspan="{{ $filters['type'] === 'members' ? 9 : 4 }}"><div class="empty">Tidak ada data untuk filter ini.</div></td></tr>
    @endforelse
    </tbody></table>
    {{ $rows->links() }}
    <small>Kehadiran berdasarkan tanggal sesi. Iuran berdasarkan tanggal kas; top up berdasarkan tanggal persetujuan. Sesi dibatalkan tidak dihitung dalam absensi.</small>
</section>
@endsection
