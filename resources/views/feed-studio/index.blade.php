@extends('layouts.app')
@section('title', 'Feed Studio')
@section('content')
<div class="feed-studio-page">
    <div class="page-heading flex flex-wrap items-end justify-between gap-4">
        <div><span class="eyebrow">ADMIN CREATIVE TOOL</span><h1>Feed Studio</h1><p>Konten masuk. Feed branded keluar.</p></div>
        <a class="btn primary" href="{{ route('feed-studio.create') }}">Buat konten</a>
    </div>

    <section class="feed-studio-hero">
        <div><span>CONNECTION STATE</span><h2>Satu identitas, terus berkembang.</h2><p>Setiap batch membaca arah visual dari export sebelumnya.</p></div>
        <div class="feed-seed-label"><strong>ROW 0</strong><span>Master canvas 3 × 1</span></div>
    </section>

    <section class="feed-seed-stage" aria-label="Seed Row master canvas">
        <div class="feed-stage-head"><span>SEED ROW · MASTER CANVAS</span><a class="btn soft" href="{{ route('feed-studio.show', $seed) }}">Export Seed Row</a></div>
        <div class="feed-canvas-scroll"><canvas width="3240" height="1440" data-feed-seed-canvas data-connection='@json($seed->connection_state)'></canvas></div>
    </section>

    <section class="feed-connection-panel" aria-labelledby="connection-history-title">
        <div class="section-heading">
            <div><span class="eyebrow">CONNECTION HISTORY</span><h2 id="connection-history-title">Alur antar-row</h2></div>
            <small>{{ $connectionHistory->count() }} batch terakhir</small>
        </div>
        <div class="feed-connection-track">
            @foreach($connectionHistory as $item)
                @php
                    $incoming = $item->connection_state['incoming_connection'][0] ?? null;
                    $outgoing = $item->connection_state['court_line_exit'][0] ?? null;
                @endphp
                <article class="feed-connection-node">
                    <div class="feed-connection-node-head">
                        <b>ROW {{ $item->row_number }}</b>
                        <span class="status-pill {{ $item->workflowTone() }}">{{ $item->workflowLabel() }}</span>
                    </div>
                    <strong>{{ $item->headline }}</strong>
                    <small>{{ $item->connectedFrom ? 'Dari Row '.$item->connectedFrom->row_number : 'Titik awal feed' }}</small>
                    <div class="feed-connection-metrics">
                        <span>Masuk <b>{{ $incoming ? round($incoming['x'] * 100).'%' : '—' }}</b></span>
                        <span>Keluar <b>{{ $outgoing ? round($outgoing['x'] * 100).'%' : '—' }}</b></span>
                    </div>
                </article>
            @endforeach
        </div>
        @if($connectionAnchor)
            @php($nextExit = $connectionAnchor->connection_state['court_line_exit'][0] ?? null)
            <div class="feed-next-handoff">
                <div><span>NEXT HANDOFF</span><strong>Row berikutnya mengikuti Row {{ $connectionAnchor->row_number }}</strong><small>Mulai dari bawah{{ $nextExit ? ' di posisi '.round($nextExit['x'] * 100).'%' : '' }}.</small></div>
                <a class="btn primary" href="{{ route('feed-studio.create') }}">Buat row berikutnya</a>
            </div>
        @endif
    </section>

    <div class="section-heading"><div><span class="eyebrow">FEED HISTORY</span><h2>Desain terbaru</h2></div><small>{{ $designs->total() }} batch tersimpan</small></div>
    <div class="feed-history-grid">
        @foreach($designs as $design)
            <article class="feed-history-card">
                <a class="feed-history-visual" href="{{ $design->published_at ? route('feed-studio.show', $design) : route('feed-studio.edit', $design) }}">
                    @if($design->thumbnail_path)<img src="{{ route('feed-studio.thumbnail', $design).'?v='.$design->updated_at->getTimestamp() }}" alt="Thumbnail {{ $design->headline }}">
                    @elseif($design->photo_path)<img src="{{ route('feed-studio.photo', $design).'?v='.$design->updated_at->getTimestamp() }}" alt="Foto {{ $design->headline }}">
                    @else<span>{{ $design->headline }}</span>@endif
                    <b>{{ $design->postCount() }} POST</b>
                </a>
                <div class="feed-history-copy">
                    <div class="feed-history-meta"><small>ROW {{ $design->row_number }} · {{ str($design->content_type)->headline() }}</small><span class="status-pill {{ $design->workflowTone() }}">{{ $design->workflowLabel() }}</span></div>
                    <strong>{{ $design->headline }}</strong>
                    <span>{{ $design->connectedFrom ? 'Terhubung dari Row '.$design->connectedFrom->row_number : 'Tanpa parent' }} · {{ $design->created_at->format('d M Y · H:i') }}</span>
                </div>
                <div class="feed-history-actions">
                    @if($design->published_at === null)<a class="btn soft" href="{{ route('feed-studio.edit', $design) }}">Edit</a>@endif
                    <a class="btn soft" href="{{ route('feed-studio.show', $design) }}">Preview</a>
                    @if($design->published_at === null)
                        <form method="post" action="{{ route('feed-studio.destroy', $design) }}" onsubmit="return confirm('Hapus batch Row {{ $design->row_number }}? Koneksi turunannya akan disesuaikan.')">
                            @csrf @method('delete')
                            <button class="btn danger-bg" type="submit">Hapus</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    {{ $designs->links() }}
</div>
@endsection
