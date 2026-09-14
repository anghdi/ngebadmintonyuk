@extends('layouts.app')
@section('title', 'Profil Member')
@section('content')
    <a class="back-link" href="{{ route('public-sessions.index') }}">← Jadwal main</a>
    <section class="card mx-auto max-w-md p-5 sm:p-7">
        <span class="eyebrow">Member komunitas</span>
        <div class="mt-4 flex flex-col items-center gap-4 text-center">
            @if($profile['has_avatar'])
                <img class="aspect-square w-full max-w-64 rounded-2xl object-cover" src="{{ route('community-profile.avatar', $profile['id']) }}" alt="Foto {{ $profile['name'] }}" width="256" height="256">
            @else
                <span class="flex h-28 w-28 items-center justify-center rounded-full bg-slate-100 text-3xl font-semibold text-slate-500">{{ $profile['initials'] }}</span>
            @endif
            <div class="min-w-0"><h1 class="break-words text-xl">{{ $profile['name'] }}</h1>@if($profile['nickname'])<p class="text-sm text-slate-500">{{ $profile['nickname'] }}</p>@endif</div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm">{{ ['beginner' => 'Pemula', 'intermediate' => 'Menengah', 'advanced' => 'Mahir'][$profile['playing_level']] ?? 'Level belum diisi' }}</span>
        </div>
    </section>
@endsection
