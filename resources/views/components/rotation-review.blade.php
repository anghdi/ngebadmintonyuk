@props(['schedule'])
@php($players = collect($schedule['roster'])->keyBy('id'))
@if(isset($schedule['session_duration_minutes']))<p class="mt-2 text-sm">Durasi sesi {{ $schedule['session_duration_minutes'] }} menit &middot; {{ $schedule['minutes_per_set'] }} menit/set &middot; sekitar {{ $schedule['minutes_per_round'] }} menit/ronde</p>@endif
@if(isset($schedule['sets_per_match']))<p class="mt-3 text-sm">{{ $schedule['sets_per_match'] }} set × {{ $schedule['points_per_set'] }} poin · batas main {{ str_replace(':', '.', $schedule['play_until']) }}</p>@endif
<div class="mt-4 overflow-x-auto rounded-lg border border-slate-200" tabindex="0" aria-label="Tabel review rotasi">
    <table class="w-full min-w-[600px] text-left text-sm">
        <caption class="px-3 py-3 text-left font-semibold">Review {{ count($schedule['rounds']) }} ronde · {{ $schedule['court_count'] }} lapangan</caption>
        <thead class="bg-slate-50"><tr><th class="p-3">Ronde</th><th class="p-3">Lapangan</th><th class="p-3">Pasangan A</th><th class="p-3">Pasangan B</th><th class="p-3">Istirahat</th></tr></thead>
        <tbody>@foreach($schedule['rounds'] as $round)@foreach($round['courts'] as $court)<tr class="border-t border-slate-100"><td class="p-3">{{ $round['number'] }}</td><td class="p-3">{{ $court['label'] ?? ($court['number'] === 1 ? 'A' : 'B') }}</td><td class="p-3">{{ collect($court['team_a'])->map(fn ($id) => $players[$id]['name'])->implode(' + ') }}</td><td class="p-3">{{ collect($court['team_b'])->map(fn ($id) => $players[$id]['name'])->implode(' + ') }}</td><td class="p-3 text-slate-500">{{ $loop->first ? (collect($round['rest'])->map(fn ($id) => $players[$id]['name'])->implode(', ') ?: '—') : '—' }}</td></tr>@endforeach@endforeach</tbody>
    </table>
</div>
<details class="mt-3 text-sm"><summary class="cursor-pointer font-medium">Jatah main per pemain</summary><ul class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($players as $id => $player)<li class="flex justify-between gap-3 border-b border-slate-100 py-2"><span>{{ $player['name'] }}</span><span class="shrink-0 text-slate-500">{{ $schedule['games'][$id] }}× main</span></li>@endforeach</ul></details>
