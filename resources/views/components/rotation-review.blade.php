@props(['schedule'])
@php($players = collect($schedule['roster'])->keyBy('id'))
<div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
    @if(isset($schedule['sets_per_match']))<span>{{ $schedule['sets_per_match'] }} set × {{ $schedule['points_per_set'] }} poin</span>@endif
    @if(isset($schedule['session_duration_minutes']))<span>{{ $schedule['session_duration_minutes'] }} menit · ±{{ $schedule['minutes_per_round'] }} menit/giliran</span>@endif
    @if(isset($schedule['play_until']))<span>Selesai sekitar {{ str_replace(':', '.', $schedule['play_until']) }}</span>@endif
</div>
<div class="mt-4 overflow-hidden rounded-xl border border-slate-200">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
        <p class="font-semibold text-slate-900">{{ count($schedule['rounds']) }} giliran · {{ $schedule['court_count'] }} lapangan</p>
        <p class="text-xs text-slate-500">Setiap baris satu pertandingan</p>
    </div>
    <div class="overflow-x-auto" tabindex="0" aria-label="Tabel review rotasi">
        <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="bg-white text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="w-20 px-4 py-3">Giliran</th><th class="w-24 px-4 py-3">Lapangan</th><th class="px-4 py-3">Pasangan A</th><th class="w-12 px-2 py-3 text-center" aria-label="melawan"></th><th class="px-4 py-3">Pasangan B</th></tr>
            </thead>
            <tbody>
                @foreach($schedule['rounds'] as $round)
                    @foreach($round['courts'] as $court)
                        <tr class="border-t border-slate-100 align-top hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $round['number'] }}</td>
                            <td class="px-4 py-3"><span class="inline-flex min-w-8 justify-center rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">{{ $court['label'] ?? ($court['number'] === 1 ? 'A' : 'B') }}</span></td>
                            <td class="px-4 py-3">@foreach($court['team_a'] as $id)<span class="block {{ $loop->first ? 'font-medium text-slate-900' : 'mt-1 text-slate-600' }}">{{ $players[$id]['name'] }}</span>@endforeach</td>
                            <td class="px-2 py-3 text-center text-xs font-semibold text-slate-400">VS</td>
                            <td class="px-4 py-3">@foreach($court['team_b'] as $id)<span class="block {{ $loop->first ? 'font-medium text-slate-900' : 'mt-1 text-slate-600' }}">{{ $players[$id]['name'] }}</span>@endforeach</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<details class="mt-3 text-sm"><summary class="cursor-pointer font-medium">Jatah main per pemain</summary><ul class="mt-2 grid gap-2 sm:grid-cols-2">@foreach($players as $id => $player)<li class="flex justify-between gap-3 border-b border-slate-100 py-2"><span>{{ $player['name'] }}</span><span class="shrink-0 text-slate-500">{{ $schedule['games'][$id] }}× main</span></li>@endforeach</ul></details>
