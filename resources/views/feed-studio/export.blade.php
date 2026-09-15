@extends('layouts.app')
@section('title', 'Preview Feed')
@section('content')
@php
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
        'photoUrl' => $feedDesign->photo_path ? route('feed-studio.photo', $feedDesign) : null,
    ];
@endphp
<div class="feed-studio-page" data-feed-editor
    data-design='@json($editorData)'
    data-assets-url="{{ route('feed-studio.assets', $feedDesign) }}" data-csrf="{{ csrf_token() }}">
    <div class="page-heading flex flex-wrap items-end justify-between gap-4"><div><span class="eyebrow">GRID PREVIEW & EXPORT</span><h1>{{ $feedDesign->headline }}</h1><p>Periksa crop, sambungan, dan urutan upload.</p></div><a class="btn soft" href="{{ $feedDesign->is_seed ? route('feed-studio.index') : route('feed-studio.edit', $feedDesign) }}">{{ $feedDesign->is_seed ? 'Kembali' : 'Edit konten' }}</a></div>
    <div class="feed-editor-layout">
        <section class="feed-master-stage"><div class="feed-stage-head"><span>MASTER CANVAS</span><span>{{ $feedDesign->postCount() }} × 1080 · 1350 px</span></div><div class="feed-canvas-scroll"><canvas width="{{ 1080 * $feedDesign->postCount() }}" height="1350" data-feed-canvas></canvas></div><p data-feed-status role="status">Menyiapkan desain…</p></section>
        <aside class="feed-editor-controls">
            @unless($feedDesign->is_seed)
            <section><span class="eyebrow">CROP FOTO</span><label>Zoom<input type="range" min="1" max="3" step="0.01" value="{{ $feedDesign->layout_settings['zoom'] ?? 1 }}" data-feed-setting="zoom"></label><label>Horizontal<input type="range" min="-1" max="1" step="0.01" value="{{ $feedDesign->layout_settings['x'] ?? 0 }}" data-feed-setting="x"></label><label>Vertikal<input type="range" min="-1" max="1" step="0.01" value="{{ $feedDesign->layout_settings['y'] ?? 0 }}" data-feed-setting="y"></label></section>
            <button class="btn soft full" type="button" data-feed-regenerate>Regenerate layout</button>
            @endunless
            <button class="btn primary full" type="button" data-feed-export>Export for Instagram</button>
        </aside>
    </div>
    <section class="mt-8"><div class="section-heading"><div><span class="eyebrow">INSTAGRAM GRID</span><h2>Setelah konten di-upload</h2></div><small>Upload sesuai nomor</small></div><div class="feed-grid-preview" data-feed-grid>
        @foreach($history as $item)
            @if($item->is_seed)
                <canvas class="feed-grid-seed-master" width="3240" height="1080" data-feed-seed-canvas data-feed-seed-grid data-connection='@json($item->connection_state)' aria-label="Seed Row connected 3 × 1"></canvas>
            @else
                @for($post = 0; $post < $item->postCount(); $post++)<div class="feed-grid-old"><img src="{{ route('feed-studio.thumbnail', $item) }}" alt=""></div>@endfor
            @endif
        @endforeach
    </div></section>
    <section class="card mt-6"><h2 class="text-base">Urutan upload</h2><ol class="mt-3 grid gap-2 text-sm" data-feed-order></ol><p class="mt-3 text-xs text-slate-500">Instagram menempatkan post terbaru di kiri. Ikuti urutan file agar komposisi tidak terbalik.</p></section>
</div>
@endsection
