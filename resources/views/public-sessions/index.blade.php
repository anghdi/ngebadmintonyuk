@extends('layouts.public')

@section('title', 'Jadwal Main')

@section('content')
    <section class="public-hero">
        <span class="eyebrow">JADWAL KOMUNITAS</span>
        <h1>Pilih jadwal bermain</h1>
        <p>Terbuka untuk member dan pemain umum.</p>
    </section>

    <section class="public-session-grid">
        @forelse($playSessions as $playSession)
            <article class="public-session-card">
                <div class="public-session-date"><strong>{{ $playSession->scheduled_at->format('d') }}</strong><span>{{ $playSession->scheduled_at->translatedFormat('M') }}</span></div>
                <div class="public-session-main">
                    <small>{{ $playSession->scheduled_at->translatedFormat('l') }} · {{ $playSession->scheduled_at->format('H:i') }} WITA</small>
                    <h2>{{ $playSession->venue_name }}</h2>
                    <p>{{ $playSession->court_name }}</p>
                    <div class="public-session-meta"><span>{{ rupiah($playSession->price_per_session) }}</span><span>{{ $playSession->registrations_count }}/{{ $playSession->max_players }} pemain</span></div>
                    <a class="btn {{ $playSession->registrations_count >= $playSession->max_players ? 'soft' : 'primary' }} full" href="{{ route('public-sessions.show', $playSession) }}">{{ $playSession->registrations_count >= $playSession->max_players ? 'Daftar penuh' : 'Lihat dan ikut' }}</a>
                </div>
            </article>
        @empty
            <div class="empty-state"><span>🏸</span><h2>Belum ada jadwal</h2><p>Jadwal baru akan ditampilkan di sini.</p></div>
        @endforelse
    </section>

    {{ $playSessions->links() }}
@endsection
