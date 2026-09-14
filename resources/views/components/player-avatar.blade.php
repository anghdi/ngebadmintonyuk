@props(['member' => null, 'name' => '', 'linked' => true])

@if($member && $member->role === 'member' && auth()->check())
    @if($linked)<a class="inline-flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-xs font-semibold text-slate-600 ring-1 ring-slate-200 focus-visible:outline-2 focus-visible:outline-blue-600" href="{{ route('community-profile.show', $member) }}" aria-label="Lihat profil {{ $member->name }}">@else<span class="inline-flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-xs font-semibold text-slate-600">@endif
        @if($member->avatar_path)<img class="h-full w-full object-cover" src="{{ route('community-profile.avatar', $member) }}" alt="Foto {{ $member->name }}" loading="lazy" width="44" height="44">@else{{ $member->initials() }}@endif
    @if($linked)</a>@else</span>@endif
@else
    <div class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-500" aria-hidden="true">{{ \Illuminate\Support\Str::substr($name, 0, 1) }}</div>
@endif
