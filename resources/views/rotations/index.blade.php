@extends('layouts.app')
@section('title', 'Rotasi Main')
@section('content')
    <div class="page-head"><div><span class="eyebrow">Giliran bermain</span><h1>Rotasi Main</h1><p>Jadwal sesi yang kamu ikuti.</p></div></div>
    <div class="space-y-3">
        @forelse($sessions as $session)
            <a class="card flex flex-wrap items-center justify-between gap-3 p-4" href="{{ route('rotations.show', $session) }}">
                <div class="min-w-0"><strong class="break-words">{{ $session->venue_name }}</strong><p class="text-sm text-slate-500">{{ $session->scheduled_at->translatedFormat('d M Y') }} · {{ $session->scheduled_at->format('H:i') }} · {{ $session->court_count }} lapangan</p></div>
                <span class="text-sm text-blue-700">{{ $rotationStates[$session->id]['rotationStale'] ? 'Menunggu pembaruan' : 'Lihat giliran →' }}</span>
            </a>
        @empty
            <div class="card p-5"><h2>Belum ada rotasi</h2><p class="text-sm">Jadwal muncul setelah admin mempublikasikan rotasi sesi yang kamu ikuti.</p><a class="btn soft mt-4" href="{{ route('public-sessions.index') }}">Lihat jadwal main</a></div>
        @endforelse
    </div>
    {{ $sessions->links() }}
@endsection
