@extends('layouts.app')
@section('title', 'Story Studio')
@section('content')
<div class="story-studio" data-story-studio data-logo="{{ asset('logo.png') }}" data-demo="{{ asset('images/member-action-01.webp') }}">
    <div class="page-heading">
        <div><span class="eyebrow">MOMEN DI LAPANGAN</span><h1>Story Studio</h1><p>Foto kamu. Cerita kita.</p></div>
    </div>
    <section class="story-design-picker" aria-label="Pilih desain sebelum menambahkan foto">
        <h2><span>01</span> Pilih desain</h2>
        <fieldset class="grid grid-cols-3 gap-3">
            <legend class="sr-only">Desain story</legend>
            <label class="story-theme"><input type="radio" name="story-theme" value="blue" checked><canvas width="216" height="384" data-story-thumbnail="blue" aria-hidden="true"></canvas><b>Court Blue</b><small>Sporty</small></label>
            <label class="story-theme"><input type="radio" name="story-theme" value="yellow"><canvas width="216" height="384" data-story-thumbnail="yellow" aria-hidden="true"></canvas><b>Matchday Yellow</b><small>Bold</small></label>
            <label class="story-theme"><input type="radio" name="story-theme" value="minimal"><canvas width="216" height="384" data-story-thumbnail="minimal" aria-hidden="true"></canvas><b>Rally Pop</b><small>Kolase</small></label>
        </fieldset>
    </section>
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="story-preview" aria-label="Pratinjau story">
            <div class="story-preview-meta"><span>PRATINJAU</span><span>9:16 · 1080 × 1920</span></div>
            <canvas width="1080" height="1920" data-story-canvas aria-label="Foto dalam frame NgeBadmintonYuk">Browser kamu tidak mendukung pratinjau foto.</canvas>
            <p data-story-preview-note>Contoh template. Tambahkan fotomu untuk mulai.</p>
        </section>
        <section class="story-controls" aria-label="Pengaturan story">
            <div class="story-control-section">
                <h2>02 <span>Tambahkan foto</span></h2>
                <div class="grid grid-cols-2 gap-2">
                    <button class="btn primary" type="button" data-story-camera><x-nav-icon name="camera" /> Ambil foto</button>
                    <button class="btn soft" type="button" data-story-upload>Pilih foto</button>
                </div>
                <input type="file" accept="image/*" capture="environment" class="sr-only" data-story-camera-input aria-label="Ambil foto dengan kamera">
                <input type="file" accept="image/*" class="sr-only" data-story-file-input aria-label="Pilih foto dari perangkat">
                <small>JPG, PNG, WebP. Maksimal 20 MB. Foto tetap di perangkatmu.</small>
            </div>
            <div class="story-control-section">
                <h2>03 <span>Atur foto</span></h2>
                <fieldset data-story-adjust disabled>
                    <label>Perbesar<input type="range" min="1" max="3" step="0.01" value="1" data-story-zoom></label>
                    <label>Geser horizontal<input type="range" min="-1" max="1" step="0.01" value="0" data-story-x></label>
                    <label>Geser vertikal<input type="range" min="-1" max="1" step="0.01" value="0" data-story-y></label>
                    <button class="story-reset" type="button" data-story-reset>Reset posisi</button>
                </fieldset>
            </div>
            <div class="story-export">
                <button class="btn primary full" type="button" data-story-download disabled>Unduh story</button>
                <button class="btn soft full" type="button" data-story-share disabled hidden>Bagikan</button>
                <p role="status" aria-live="polite" data-story-status>Tambahkan foto untuk mengunduh.</p>
                <small>Simpan hasilnya, lalu pilih dari galeri saat membuat Story.</small>
            </div>
        </section>
    </div>
</div>
@endsection
