@props(['schedule' => null, 'stale' => false, 'currentUserId' => null])

@if($stale)
    <p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900" role="status">List pemain atau jumlah lapangan berubah. Menunggu admin generate ulang.</p>
@elseif(!$schedule)
    <p class="mt-4 text-sm text-slate-500">Jadwal rotasi belum dibuat.</p>
@else
    @php
        $players = collect($schedule['roster'])->keyBy('id');
        $levelLabels = ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir'];
        $myId = $currentUserId ? $players->firstWhere('user_id', $currentUserId)['id'] ?? null : null;
    @endphp
    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500"><span>{{ count($schedule['rounds']) }} ronde</span><span>{{ $schedule['court_count'] }} lapangan</span><span>Ganda · sesuai urutan ronde</span></div>
    @if(isset($schedule['session_duration_minutes']))<p class="mt-2 text-sm">Durasi sesi {{ $schedule['session_duration_minutes'] }} menit &middot; {{ $schedule['minutes_per_set'] }} menit/set &middot; sekitar {{ $schedule['minutes_per_round'] }} menit/ronde</p>@endif
@if(isset($schedule['sets_per_match']))<p class="mt-2 text-sm">{{ $schedule['sets_per_match'] }} set × {{ $schedule['points_per_set'] }} poin · batas main {{ str_replace(':', '.', $schedule['play_until']) }}</p><p class="text-xs text-slate-500">Giliran berikutnya setelah pertandingan selesai. Waktu selesai merupakan perkiraan; durasi aktual bisa berbeda.</p>@endif
    <div class="mt-4 space-y-3">
        @foreach($schedule['rounds'] as $round)
            @php
                $myCourt = $myId ? collect($round['courts'])->first(fn ($court) => in_array($myId, [...$court['team_a'], ...$court['team_b']])) : null;
                $myTeam = $myCourt ? (in_array($myId, $myCourt['team_a']) ? $myCourt['team_a'] : $myCourt['team_b']) : [];
                $myPartner = $myCourt ? collect($myTeam)->first(fn ($id) => $id !== $myId) : null;
            @endphp
            <details class="rounded-xl border border-slate-200 bg-white" @if($loop->first) open @endif>
                <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm"><strong>Ronde {{ $round['number'] }}</strong>@if($myId)<span class="rounded-full bg-slate-100 px-2 py-1 text-xs">{{ $myCourt ? 'Kamu main · Lapangan '.($myCourt['label'] ?? ($myCourt['number'] === 1 ? 'A' : 'B')) : 'Kamu istirahat' }}</span>@else<span class="text-slate-500">Lihat pasangan</span>@endif</summary>
                @if($myPartner)<p class="px-4 pb-3 text-sm text-teal-700">Pasanganmu: <strong>{{ $players[$myPartner]['name'] }}</strong></p>@endif
                <div class="grid gap-3 px-4 pb-4 {{ $schedule['court_count'] === 2 ? 'md:grid-cols-2' : '' }}">
                    @foreach($round['courts'] as $court)
                        <div class="min-w-0 rounded-lg bg-slate-50 p-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Lapangan {{ $court['label'] ?? ($court['number'] === 1 ? 'A' : 'B') }}</p>
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 text-sm">
                                @foreach(['team_a', 'team_b'] as $team)
                                    @if($team === 'team_b')<span class="text-xs text-slate-400">vs</span>@endif
                                    <div class="min-w-0 space-y-1">@foreach($court[$team] as $id)<p @class(['break-words', 'font-semibold text-teal-700' => $id === $myId])>{{ $players[$id]['name'] }} <small class="text-[11px] font-normal text-slate-500">{{ $levelLabels[$players[$id]['playing_level'] ?? ''] ?? 'Level belum diisi' }}</small></p>@endforeach</div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($round['rest'])<p class="border-t border-slate-100 px-4 py-3 text-sm text-slate-500"><span class="font-medium">Istirahat:</span> {{ collect($round['rest'])->map(fn ($id) => $players[$id]['name'])->implode(', ') }}</p>@endif
            </details>
        @endforeach
    </div>
    <details class="mt-4 text-sm">
        <summary class="cursor-pointer font-medium">Jatah main per pemain</summary>
        <ul class="mt-2 grid gap-x-5 gap-y-2 sm:grid-cols-2">@foreach($players as $id => $player)<li class="flex min-w-0 justify-between gap-3 border-b border-slate-100 py-2"><span class="break-words">{{ $player['name'] }}</span><span class="shrink-0 text-slate-500">{{ $schedule['games'][$id] }}× main</span></li>@endforeach</ul>
        <p class="mt-2 text-xs text-slate-500">Ini rencana giliran, bukan catatan kehadiran.</p>
    </details>
@endif
