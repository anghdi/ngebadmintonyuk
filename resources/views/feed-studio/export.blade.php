@extends('layouts.app')
@section('title', 'Preview Feed')
@section('content')
@php
    $incoming = $feedDesign->connection_state['incoming_connection'][0] ?? null;
    $outgoing = $feedDesign->connection_state['court_line_exit'][0] ?? null;
    $editorData = [
        'id' => $feedDesign->id,
        'isSeed' => $feedDesign->is_seed,
        'postCount' => $feedDesign->postCount(),
        'headline' => $feedDesign->headline,
        'supportingText' => $feedDesign->supporting_text,
        'eventDate' => $feedDesign->event_date?->format('d.m.Y'),
        'eventTime' => $feedDesign->event_time,
        'venue' => $feedDesign->venue,
        'price' => $feedDesign->price,
        'cta' => $feedDesign->cta,
        'variant' => $feedDesign->layout_variant,
        'settings' => $feedDesign->layout_settings,
        'connection' => $feedDesign->connection_state,
        'photoUrl' => $feedDesign->photo_path ? route('feed-studio.photo', $feedDesign).'?v='.$feedDesign->updated_at->getTimestamp() : null,
    ];
@endphp
<div class="feed-studio-page" data-feed-editor data-design='@json($editorData)' data-assets-url="{{ route('feed-studio.assets', $feedDesign) }}" data-csrf="{{ csrf_token() }}">
    <div class="page-heading flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2"><span class="eyebrow">ROW {{ $feedDesign->row_number }} · GRID PREVIEW</span><span class="status-pill {{ $feedDesign->workflowTone() }}" data-feed-workflow-status>{{ $feedDesign->workflowLabel() }}</span></div>
            <h1>{{ $feedDesign->headline }}</h1><p>Periksa crop, sambungan, dan urutan upload.</p>
        </div>
        <a class="btn soft" href="{{ $feedDesign->is_seed || $feedDesign->published_at ? route('feed-studio.index') : route('feed-studio.edit', $feedDesign) }}">{{ $feedDesign->is_seed || $feedDesign->published_at ? 'Kembali' : 'Edit konten' }}</a>
    </div>

    <div class="feed-editor-layout">
        <section class="feed-master-stage">
            <div class="feed-stage-head"><span>MASTER CANVAS</span><span>{{ $feedDesign->postCount() }} × 1080 · 1440 px</span></div>
            <div class="feed-canvas-scroll"><canvas width="{{ 1080 * $feedDesign->postCount() }}" height="1440" data-feed-canvas></canvas></div>
            <p data-feed-status role="status">Menyiapkan desain…</p>
        </section>
        <aside class="feed-editor-controls">
            @if($feedDesign->published_at)
                <div class="feed-published-note"><span class="status-pill active">Dipublikasikan</span><p>Batch dikunci agar riwayat feed tetap konsisten.</p></div>
                <a class="btn primary full" href="{{ route('feed-studio.create') }}">Buat row berikutnya</a>
            @else
                @unless($feedDesign->is_seed)
                    <section><span class="eyebrow">CROP FOTO</span><label>Zoom<input type="range" min="1" max="3" step="0.01" value="{{ $feedDesign->layout_settings['zoom'] ?? 1 }}" data-feed-setting="zoom"></label><label>Horizontal<input type="range" min="-1" max="1" step="0.01" value="{{ $feedDesign->layout_settings['x'] ?? 0 }}" data-feed-setting="x"></label><label>Vertikal<input type="range" min="-1" max="1" step="0.01" value="{{ $feedDesign->layout_settings['y'] ?? 0 }}" data-feed-setting="y"></label></section>
                    <button class="btn soft full" type="button" data-feed-regenerate>Regenerate layout</button>
                @endunless
                <button class="btn primary full" type="button" data-feed-export>Export for Instagram</button>
                @unless($feedDesign->is_seed)
                    <form method="post" action="{{ route('feed-studio.publish', $feedDesign) }}" data-feed-publish-form @class(['hidden' => $feedDesign->exported_at === null])>
                        @csrf
                        <button class="btn soft full" type="submit">Tandai dipublikasikan</button>
                    </form>
                    <a class="btn soft full {{ $feedDesign->exported_at === null ? 'hidden' : '' }}" href="{{ route('feed-studio.create') }}" data-feed-next-row>Buat row berikutnya</a>
                    <form method="post" action="{{ route('feed-studio.destroy', $feedDesign) }}" onsubmit="return confirm('Hapus batch ini? Koneksi turunannya akan disesuaikan.')">
                        @csrf @method('delete')
                        <button class="link danger" type="submit">Hapus batch</button>
                    </form>
                @endunless
            @endif
        </aside>
    </div>

    <section class="feed-connection-summary">
        <div><span>PARENT</span><strong>{{ $feedDesign->connectedFrom ? 'Row '.$feedDesign->connectedFrom->row_number : 'Seed origin' }}</strong><small>{{ $incoming ? 'Masuk dari bawah · '.round($incoming['x'] * 100).'%' : 'Titik awal rangkaian' }}</small></div>
        <div class="feed-connection-arrow" aria-hidden="true">→</div>
        <div><span>NEXT HANDOFF</span><strong>{{ $outgoing ? 'Keluar dari atas · '.round($outgoing['x'] * 100).'%' : 'Tanpa titik keluar' }}</strong><small>{{ $outgoing ? str($outgoing['direction'])->replace('-', ' ')->headline() : 'Standalone' }}</small></div>
    </section>

    <section class="mt-8">
        <div class="section-heading"><div><span class="eyebrow">INSTAGRAM GRID</span><h2>Setelah konten di-upload</h2></div><small>Upload sesuai nomor</small></div>
        <div class="feed-grid-preview" data-feed-grid>
            @foreach($history as $item)
                @if($item->is_seed)
                    @for($seedSlice = 0; $seedSlice < 3; $seedSlice++)
                        <canvas width="1080" height="1440" data-feed-seed-canvas data-feed-seed-grid data-feed-seed-slice="{{ $seedSlice }}" data-connection='@json($item->connection_state)' aria-label="Seed Row post {{ $seedSlice + 1 }}"></canvas>
                    @endfor
                @elseif(filled($item->exported_assets))
                    @foreach(array_reverse($item->exported_assets, true) as $assetIndex => $assetPath)
                        <img src="{{ route('feed-studio.asset', [$item, $assetIndex]).'?v='.$item->updated_at->getTimestamp() }}" alt="Post {{ $item->headline }}">
                    @endforeach
                @endif
            @endforeach
        </div>
    </section>
    <section class="card mt-6"><h2 class="text-base">Urutan upload</h2><ol class="mt-3 grid gap-2 text-sm" data-feed-order></ol><p class="mt-3 text-xs text-slate-500">Instagram menempatkan post terbaru di kiri. Ikuti urutan file agar komposisi tidak terbalik.</p></section>
</div>
@endsection
