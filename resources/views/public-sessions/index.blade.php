@extends(auth()->check() ? 'layouts.app' : 'layouts.public')

@section('title', 'Jadwal Main')

@section('content')
    <section class="public-hero">
        <span class="eyebrow">JADWAL KOMUNITAS</span>
        <h1>Pilih jadwal bermain</h1>
        <p>Terbuka untuk member dan pemain umum.</p>
    </section>

    <x-usage-guide />

    <section class="schedule-month-picker" aria-labelledby="public-month-title">
        <div>
            <span class="eyebrow">PILIH BULAN</span>
            <h2 id="public-month-title">Jadwal per bulan</h2>
            <p>Daftar jadwal baru ditampilkan setelah kamu memilih bulan.</p>
        </div>
        <form method="get" action="{{ route('public-sessions.index') }}" class="schedule-month-form">
            <label for="public-month">Bulan</label>
            <select id="public-month" name="month" required>
                <option value="">Pilih bulan</option>
                @foreach($availableMonths as $month)
                    <option value="{{ $month['value'] }}" @selected($selectedMonth === $month['value'])>{{ $month['label'] }}</option>
                @endforeach
            </select>
            <button class="btn primary">Lihat jadwal</button>
        </form>
    </section>

    @if($selectedMonth)
        <div class="section-heading schedule-month-heading">
            <div><span class="eyebrow">JADWAL TERPILIH</span><h2>{{ $selectedMonthLabel }}</h2></div>
        </div>
    @endif

    <section class="public-session-grid" aria-live="polite">
        @forelse($playSessions as $playSession)
            @php($confirmedCount = min($playSession->registrations_count, $playSession->max_players))
            @php($waitingCount = max(0, $playSession->registrations_count - $playSession->max_players))
            @php($isClosed = $playSession->registrations_count >= $playSession->max_players + $playSession->max_waiting_players)
            <article class="public-session-card">
                <div class="public-session-date"><strong>{{ $playSession->scheduled_at->format('d') }}</strong><span>{{ $playSession->scheduled_at->translatedFormat('M') }}</span></div>
                <div class="public-session-main">
                    <small>{{ $playSession->scheduled_at->translatedFormat('l') }} · {{ $playSession->scheduled_at->format('H:i') }} WITA</small>
                    <h2>{{ $playSession->venue_name }}</h2>
                    <p>{{ $playSession->court_name }}</p>
                    <div class="public-session-meta"><span>{{ rupiah($playSession->price_per_session) }}</span><span>{{ $confirmedCount }}/{{ $playSession->max_players }} pemain</span></div>
                    <div class="session-slot-summary"><span class="main">Slot utama {{ max(0, $playSession->max_players - $confirmedCount) }}</span><span class="waiting">Waiting {{ $waitingCount }}/{{ $playSession->max_waiting_players }}</span></div>
                    <a class="btn {{ $isClosed ? 'soft' : 'primary' }} full" href="{{ route('public-sessions.show', $playSession) }}">{{ $isClosed ? 'Daftar penuh' : ($confirmedCount >= $playSession->max_players ? 'Masuk waiting list' : 'Lihat dan ikut') }}</a>
                </div>
            </article>
        @empty
            <div class="empty-state"><span>🏸</span><h2>{{ $selectedMonth ? 'Belum ada jadwal' : 'Pilih bulan terlebih dahulu' }}</h2><p>{{ $selectedMonth ? 'Tidak ada jadwal tersedia pada bulan ini.' : 'Pilih salah satu bulan di atas untuk melihat jadwal main.' }}</p></div>
        @endforelse
    </section>

    {{ $playSessions->links() }}
@endsection
