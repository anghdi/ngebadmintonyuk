<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rotasi Bermain - NgeBadminton YUK!</title>
    <style>
        @page { margin: 32px 34px 44px; }
        body { color: #14244b; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1, p { margin: 0; }
        .header { border-bottom: 2px solid #2457eb; padding-bottom: 15px; }
        .brand { color: #2457eb; font-size: 9px; font-weight: bold; letter-spacing: 1px; }
        h1 { font-size: 22px; margin-top: 6px; }
        .session { color: #56647f; font-size: 10px; margin-top: 7px; }
        .meta { background: #eef3ff; border-left: 3px solid #2457eb; margin: 15px 0 16px; padding: 9px 12px; line-height: 1.55; }
        .meta strong { color: #14244b; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #14244b; color: #fff; font-size: 8px; letter-spacing: .4px; padding: 9px 10px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #dce3f1; padding: 6px 10px; vertical-align: middle; word-wrap: break-word; }
        tbody tr:nth-child(even) td { background: #f7f9fd; }
        .turn { font-size: 12px; font-weight: bold; text-align: center; }
        .court { color: #2457eb; font-size: 11px; font-weight: bold; text-align: center; }
        .player { display: block; line-height: 1.35; }
        .partner { margin-top: 2px; }
        .level { color: #71809b; font-size: 7px; margin-left: 5px; }
        .versus { color: #8a97ad; font-size: 8px; font-weight: bold; text-align: center; }
        .note { color: #71809b; font-size: 8px; margin-top: 11px; }
        .footer { border-top: 1px solid #dce3f1; bottom: -28px; color: #71809b; font-size: 8px; left: 0; padding-top: 7px; position: fixed; right: 0; }
        .page:after { content: counter(page); }
    </style>
</head>
<body>
@php($players = collect($schedule['roster'])->keyBy('id'))
@php($levelLabels = ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir'])
<div class="header">
    <p class="brand">NGE BADMINTON YUK!</p>
    <h1>Rotasi Bermain</h1>
    <p class="session">{{ $playSession->venue_name }} · {{ $playSession->court_name }} · {{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} · {{ $playSession->scheduled_at->format('H.i') }} WITA</p>
</div>
<div class="meta">
    <strong>{{ count($schedule['rounds']) }} giliran · {{ $schedule['court_count'] }} lapangan</strong>
    @if(isset($schedule['sets_per_match'])) · {{ $schedule['sets_per_match'] }} set × {{ $schedule['points_per_set'] }} poin @endif
    @if(isset($schedule['minutes_per_round'])) · ±{{ $schedule['minutes_per_round'] }} menit/giliran @endif
    @if(isset($schedule['play_until'])) · selesai sekitar {{ str_replace(':', '.', $schedule['play_until']) }} @endif
    · {{ $published ? 'Dipublikasikan' : 'Draf admin' }}
</div>
<table>
    <thead><tr><th style="width: 8%; text-align: center;">Giliran</th><th style="width: 10%; text-align: center;">Lapangan</th><th style="width: 36%;">Pasangan A</th><th style="width: 5%;"></th><th style="width: 36%;">Pasangan B</th></tr></thead>
    <tbody>
    @foreach($schedule['rounds'] as $round)
        @foreach($round['courts'] as $court)
            <tr><td class="turn">{{ $round['number'] }}</td><td class="court">{{ $court['label'] ?? ($court['number'] === 1 ? 'A' : 'B') }}</td><td>@foreach($court['team_a'] as $id)<span class="player {{ $loop->first ? '' : 'partner' }}">{{ $players[$id]['name'] }} <span class="level">{{ $levelLabels[$players[$id]['playing_level'] ?? ''] ?? 'Level belum diisi' }}</span></span>@endforeach</td><td class="versus">VS</td><td>@foreach($court['team_b'] as $id)<span class="player {{ $loop->first ? '' : 'partner' }}">{{ $players[$id]['name'] }} <span class="level">{{ $levelLabels[$players[$id]['playing_level'] ?? ''] ?? 'Level belum diisi' }}</span></span>@endforeach</td></tr>
        @endforeach
    @endforeach
    </tbody>
</table>
<p class="note">Urutan pertandingan adalah rencana. Giliran berikutnya dimulai setelah lapangan siap.</p>
<div class="footer">Rotasi bermain · dicetak {{ now()->translatedFormat('d M Y H.i') }} WITA <span style="float: right;">Halaman <span class="page"></span></span></div>
</body>
</html>
