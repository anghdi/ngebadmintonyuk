@extends('layouts.app')
@section('title', 'Rotasi Main')
@section('content')
    <a class="back-link" href="{{ route('rotations.index') }}">← Rotasi Main</a>
    <div class="page-head"><div><span class="eyebrow">Jadwal disetujui admin</span><h1>{{ $playSession->venue_name }}</h1><p>{{ $playSession->scheduled_at->translatedFormat('l, d M Y') }} · {{ $playSession->scheduled_at->format('H:i') }} · {{ $playSession->court_count }} lapangan</p></div><a class="btn soft" href="{{ route('public-sessions.show', $playSession) }}">List pemain</a></div>
    <section class="card p-4 sm:p-5"><x-rotation-schedule :schedule="$rotationSchedule" :stale="$rotationStale" :current-user-id="auth()->id()" /></section>
@endsection
