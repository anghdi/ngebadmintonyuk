<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Laporan {{ $filters['type'] === 'members' ? 'Member' : 'Tamu' }}</title>
<style>
@page { margin: 28px 30px 40px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #172a63; }
h1 { font-size: 20px; margin: 0 0 5px; } h2 { font-size: 12px; margin: 18px 0 6px; }
p { margin: 4px 0; } .muted { color: #666; } .header { border-bottom: 3px solid #2455f5; padding-bottom: 10px; }
.summary { margin: 12px 0; padding: 10px; background: #f0f3fb; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
thead { display: table-header-group; } tr { page-break-inside: avoid; }
th { background: #172a63; color: white; text-align: left; padding: 7px 5px; font-size: 8px; }
td { padding: 7px 5px; border-bottom: 1px solid #ddd; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
tr:nth-child(even) td { background: #fafafa; } .number { text-align: right; }
.footer { position: fixed; bottom: -24px; color: #777; font-size: 8px; left: 0; right: 0; border-top: 1px solid #ddd; padding-top: 5px; }
.page:after { content: counter(page); }
</style></head><body>
<div class="header"><h1>NgeBadmintonYuk - Laporan {{ $filters['type'] === 'members' ? 'Member' : 'Tamu' }}</h1>
<p>{{ \Illuminate\Support\Carbon::parse($filters['start_date'])->translatedFormat('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->translatedFormat('d M Y') }}</p>
<p class="muted">Pencarian: {{ $filters['q'] ?? 'Semua nama' }}@if($filters['type'] === 'members') | Profil: {{ ['complete' => 'Lengkap', 'incomplete' => 'Belum lengkap'][$filters['profile'] ?? ''] ?? 'Semua' }} | Bulan ulang tahun: {{ ! empty($filters['birthday_month']) ? \Illuminate\Support\Carbon::create(2000, (int) $filters['birthday_month'], 1)->translatedFormat('F') : 'Semua' }}@endif</p>
</div>
<div class="summary">{{ $summary['total'] }} {{ $filters['type'] === 'members' ? 'member' : 'tamu' }}@if($filters['type'] === 'members') | Profil lengkap {{ $summary['complete'] }} / belum {{ $summary['incomplete'] }}@endif | Hadir {{ $summary['present'] }} / tidak hadir {{ $summary['absent'] }} | Iuran {{ rupiah($summary['payment']) }}@if($filters['type'] === 'members') | Top up {{ rupiah($summary['top_up']) }}@endif</div>
<table><thead><tr><th style="width:22%">Nama</th>@if($filters['type'] === 'members')<th style="width:11%">Profil</th><th style="width:12%">Tanggal lahir</th><th style="width:5%">Usia</th>@endif<th style="width:6%">Hadir</th><th style="width:7%">Tidak hadir</th>@if($filters['type'] === 'members')<th style="width:6%">Kuota</th>@endif<th class="number" style="width:12%">Iuran</th>@if($filters['type'] === 'members')<th class="number" style="width:13%">Top up</th>@endif</tr></thead><tbody>
@forelse($rows as $row)
<tr><td>{{ $row['name'] }}<br><span class="muted">{{ $row['phone'] ?: '-' }}</span></td>@if($filters['type'] === 'members')<td>{{ $row['complete'] ? 'Lengkap' : 'Belum lengkap' }}</td><td>{{ $row['birth_date'] ? \Illuminate\Support\Carbon::parse($row['birth_date'])->translatedFormat('d M Y') : '-' }}</td><td>{{ $row['age'] ?? '-' }}</td>@endif<td>{{ $row['present'] }}</td><td>{{ $row['absent'] }}</td>@if($filters['type'] === 'members')<td>{{ $row['quota'] }}</td>@endif<td class="number">{{ rupiah($row['payment']) }}</td>@if($filters['type'] === 'members')<td class="number">{{ rupiah($row['top_up']) }}</td>@endif</tr>
@empty<tr><td colspan="{{ $filters['type'] === 'members' ? 9 : 4 }}">Tidak ada data untuk filter ini.</td></tr>@endforelse
</tbody></table>
<p class="muted">Kehadiran: tanggal sesi, tidak termasuk sesi dibatalkan. Iuran: tanggal kas. Top up: tanggal persetujuan. Kuota dan usia: kondisi saat cetak.</p>
@if($filters['type'] === 'members')
<h2>Ulang tahun hari ini & 7 hari ke depan - seluruh member</h2>
@forelse($birthdays as $birthday)<p>{{ $birthday['name'] }} - {{ $birthday['today'] ? 'Hari ini' : \Illuminate\Support\Carbon::parse($birthday['date'])->translatedFormat('d M Y') }} - {{ $birthday['age'] }} tahun</p>@empty<p class="muted">Tidak ada ulang tahun.</p>@endforelse
@endif
<div class="footer">Khusus admin - data pribadi member. Dicetak {{ now()->translatedFormat('d M Y H:i') }} WITA <span style="float:right">Halaman <span class="page"></span></span></div>
</body></html>
