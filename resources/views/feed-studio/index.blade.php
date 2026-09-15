@extends('layouts.app')
@section('title', 'Feed Studio')
@section('content')
<div class="feed-studio-page">
    <div class="page-heading flex flex-wrap items-end justify-between gap-4"><div><span class="eyebrow">ADMIN CREATIVE TOOL</span><h1>Feed Studio</h1><p>Konten masuk. Feed branded keluar.</p></div><a class="btn primary" href="{{ route('feed-studio.create') }}">Buat konten</a></div>
    <section class="feed-studio-hero">
        <div><span>CONNECTION STATE</span><h2>Feed yang berkembang, bukan puzzle yang rapuh.</h2><p>Setiap batch membaca ritme visual sebelumnya dan tetap kuat sebagai post individual.</p></div>
        <div class="feed-seed-mini"><i></i><strong>ROW 0</strong><span>NgeBadminton YUK!</span></div>
    </section>
    <div class="section-heading"><div><span class="eyebrow">FEED HISTORY</span><h2>Desain terbaru</h2></div><small>{{ $designs->total() }} batch tersimpan</small></div>
    <div class="feed-history-grid">
        @foreach($designs as $design)
            <article class="feed-history-card">
                <a class="feed-history-visual" href="{{ $design->is_seed ? route('feed-studio.index') : route('feed-studio.edit', $design) }}">
                    @if($design->thumbnail_path)<img src="{{ route('feed-studio.thumbnail', $design) }}" alt="Thumbnail {{ $design->headline }}">
                    @elseif($design->photo_path)<img src="{{ route('feed-studio.photo', $design) }}" alt="Foto {{ $design->headline }}">
                    @else<span>{{ $design->is_seed ? 'Lagi nyari temen main?' : $design->headline }}</span>@endif
                    <b>{{ $design->postCount() }} POST</b>
                </a>
                <div class="feed-history-copy"><small>ROW {{ $design->row_number }} · {{ str($design->content_type)->headline() }}</small><strong>{{ $design->headline }}</strong><span>{{ $design->created_at->format('d M Y · H:i') }}</span></div>
                @unless($design->is_seed)<div class="flex gap-2"><a class="btn soft" href="{{ route('feed-studio.edit', $design) }}">Edit</a><a class="btn soft" href="{{ route('feed-studio.show', $design) }}">Export</a></div>@endunless
            </article>
        @endforeach
    </div>
    {{ $designs->links() }}
</div>
@endsection
