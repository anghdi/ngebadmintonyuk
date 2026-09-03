@extends('layouts.app')

@section('title', 'Papan Skor')

@section('content')
    <div class="page-head scoreboard-heading">
        <div>
            <span class="eyebrow">PERTANDINGAN</span>
            <h1>Papan skor</h1>
            <p>Skor pertandingan badminton dalam satu perangkat.</p>
        </div>
        <span class="scoreboard-storage">Tersimpan di perangkat ini</span>
    </div>

    <section class="scoreboard" data-scoreboard>
        <div class="scoreboard-topline">
            <span data-game-label>Game 1</span>
            <strong>Best of 3</strong>
            <span data-game-history>Belum ada game selesai</span>
        </div>

        <div class="scoreboard-court">
            @foreach(['a', 'b'] as $team)
                <article class="score-team score-team-{{ $team }}" data-score-team="{{ $team }}">
                    <span class="serve-badge" data-serve-badge>Servis</span>
                    <label>
                        <span>Nama tim</span>
                        <input value="Tim {{ strtoupper($team) }}" maxlength="30" data-team-name="{{ $team }}" aria-label="Nama Tim {{ strtoupper($team) }}">
                    </label>
                    <strong class="score-number" data-score="{{ $team }}">0</strong>
                    <span class="game-wins"><b data-games="{{ $team }}">0</b> game</span>
                    <button type="button" class="score-point-button" data-add-point="{{ $team }}">+1 poin</button>
                </article>
            @endforeach

            <span class="score-versus" aria-hidden="true">VS</span>
        </div>

        <div class="scoreboard-status" aria-live="polite" data-score-status>Tim A melakukan servis dari sisi kanan.</div>

        <div class="scoreboard-controls">
            <button type="button" class="btn soft" data-score-undo disabled>Batalkan poin</button>
            <button type="button" class="btn primary" data-score-next hidden>Game berikutnya</button>
            <button type="button" class="btn danger-bg" data-score-reset>Mulai ulang</button>
        </div>
    </section>

    <section class="card score-rules">
        <div><span class="eyebrow">ATURAN SKOR</span><h2>Rally point</h2></div>
        <div class="score-rule-list">
            <span><b>21</b>Poin untuk menang</span>
            <span><b>2</b>Selisih saat deuce</span>
            <span><b>30</b>Batas poin tertinggi</span>
            <span><b>2</b>Game untuk menang</span>
        </div>
    </section>
@endsection
