<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rotasi Bermain - {{ $playSession->venue_name }}</title>
    <style>
        @page { margin: 28px 30px 42px; }
        body { color: #172554; font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1, p { margin: 0; }
        .header { border-bottom: 3px solid #2563eb; padding-bottom: 11px; }
        .brand { color: #2563eb; font-size: 9px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { font-size: 21px; margin-top: 4px; }
        .session { color: #475569; font-size: 10px; margin-top: 5px; }
        .meta { background: #eff6ff; margin: 12px 0; padding: 9px 11px; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #172554; color: #fff; font-size: 8px; letter-spacing: .5px; padding: 8px 7px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #dbe3f0; padding: 8px 7px; vertical-align: middle; word-wrap: break-word; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        .turn { font-size: 11px; font-weight: bold; text-align: center; }
        .court { color: #1d4ed8; font-weight: bold; text-align: center; }
        .player { display: block; line-height: 1.35; }
        .partner { color: #64748b; margin-top: 2px; }
        .versus { color: #94a3b8; font-size: 8px; font-weight: bold; text-align: center; }
        .footer { border-top: 1px solid #cbd5e1; bottom: -27px; color: #64748b; font-size: 8px; left: 0; padding-top: 6px; position: fixed; right: 0; }
        .page:after { content: counter(page); }
    </style>
</head>
<body>
@php($players = collect($schedule['roster'])->keyBy('id'))
<div class="header">
    <p class="brand">NgeBadmintonYuk</p>
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
    <thead><tr><th style="width: 8%; text-align: center;">Giliran</th><th style="width: 10%; text-align: center;">Lapangan</th><th style="width: 34%;">Pasangan A</th><th style="width: 6%;"></th><th style="width: 34%;">Pasangan B</th></tr></thead>
    <tbody>
    @foreach($schedule['rounds'] as $round)
        @foreach($round['courts'] as $court)
            <tr><td class="turn">{{ $round['number'] }}</td><td class="court">{{ $court['label'] ?? ($court['number'] === 1 ? 'A' : 'B') }}</td><td>@foreach($court['team_a'] as $id)<span class="player {{ $loop->first ? '' : 'partner' }}">{{ $players[$id]['name'] }}</span>@endforeach</td><td class="versus">VS</td><td>@foreach($court['team_b'] as $id)<span class="player {{ $loop->first ? '' : 'partner' }}">{{ $players[$id]['name'] }}</span>@endforeach</td></tr>
        @endforeach
    @endforeach
    </tbody>
</table>
<div class="footer">Rotasi bermain · dicetak {{ now()->translatedFormat('d M Y H.i') }} WITA <span style="float: right;">Halaman <span class="page"></span></span></div>
</body>
</html>
