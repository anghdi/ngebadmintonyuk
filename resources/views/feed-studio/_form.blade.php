@php
    $editing = isset($feedDesign);
    $layoutSettings = $editing ? ($feedDesign->layout_settings ?? []) : [];
@endphp
<input type="hidden" name="zoom" value="{{ old('zoom', $layoutSettings['zoom'] ?? 1) }}">
<input type="hidden" name="position_x" value="{{ old('position_x', $layoutSettings['x'] ?? 0) }}">
<input type="hidden" name="position_y" value="{{ old('position_y', $layoutSettings['y'] ?? 0) }}">
<div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
    <div class="grid gap-5">
        <section class="card grid gap-4">
            <div class="card-head"><div><span class="eyebrow">FORMAT</span><h2>Jumlah post</h2></div></div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach(['single' => ['1 post', 'Standalone'], 'connected_2' => ['2 × 1', 'Connected'], 'connected_3' => ['3 × 1', 'Hero row']] as $value => [$label, $hint])
                    <label class="feed-choice"><input type="radio" name="format" value="{{ $value }}" @checked(old('format', $feedDesign->format ?? 'single') === $value)><b>{{ $label }}</b><small>{{ $hint }}</small></label>
                @endforeach
            </div>
        </section>
        <section class="card form-grid">
            <div class="field full"><label for="content_type">Tipe konten</label><select id="content_type" name="content_type" required>
                @foreach(['mabar' => 'Mabar', 'announcement' => 'Announcement', 'community' => 'Community / Moment', 'editorial' => 'Editorial', 'hero' => 'Brand / Hero'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('content_type', $feedDesign->content_type ?? 'mabar') === $value)>{{ $label }}</option>
                @endforeach
            </select></div>
            <div class="field full"><label for="headline">Headline</label><input id="headline" name="headline" maxlength="90" required value="{{ old('headline', $feedDesign->headline ?? '') }}" placeholder="Main bareng tanpa drama"></div>
            <div class="field full"><label for="supporting_text">Supporting text</label><textarea id="supporting_text" name="supporting_text" maxlength="180" rows="3" placeholder="Singkat dan langsung.">{{ old('supporting_text', $feedDesign->supporting_text ?? '') }}</textarea></div>
            <div class="field"><label for="event_date">Tanggal</label><input id="event_date" type="date" name="event_date" value="{{ old('event_date', isset($feedDesign) && $feedDesign->event_date ? $feedDesign->event_date->format('Y-m-d') : '') }}"></div>
            <div class="field"><label for="event_time">Jam</label><input id="event_time" type="time" name="event_time" value="{{ old('event_time', $feedDesign->event_time ?? '') }}"></div>
            <div class="field"><label for="venue">Venue</label><input id="venue" name="venue" maxlength="255" value="{{ old('venue', $feedDesign->venue ?? '') }}"></div>
            <div class="field"><label for="price">Harga</label><input id="price" name="price" maxlength="30" value="{{ old('price', $feedDesign->price ?? '') }}" placeholder="Rp35.000"></div>
            <div class="field"><label for="cta">CTA</label><input id="cta" name="cta" maxlength="40" value="{{ old('cta', $feedDesign->cta ?? '') }}" placeholder="Daftar sekarang"></div>
            <div class="field"><label for="layout_variant">Komposisi</label><select id="layout_variant" name="layout_variant" required>
                @foreach(['editorial' => 'Editorial', 'kinetic' => 'Kinetic Court', 'sideline' => 'Sideline'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('layout_variant', $feedDesign->layout_variant ?? 'editorial') === $value)>{{ $label }}</option>
                @endforeach
            </select></div>
        </section>
    </div>
    <aside class="card h-fit lg:sticky lg:top-24">
        <span class="eyebrow">FOTO UTAMA</span>
        <img
            class="mt-3 aspect-[3/4] w-full rounded-xl object-cover {{ $editing && $feedDesign->photo_path ? '' : 'hidden' }}"
            @if($editing && $feedDesign->photo_path) src="{{ route('feed-studio.photo', $feedDesign).'?v='.$feedDesign->updated_at->getTimestamp() }}" @endif
            alt="Preview foto sumber desain"
            data-feed-photo-preview
        >
        <div class="field mt-4"><label for="photo">{{ $editing ? 'Ganti foto' : 'Pilih foto' }}</label><input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-feed-photo-input><small>JPG, PNG, atau WebP. Maksimal 10 MB.</small></div>
        <p class="mt-4 text-xs text-slate-500">Foto disimpan privat. Feed Studio mengatur crop di tahap editor.</p>
        <button class="btn primary full mt-5" type="submit">{{ $editing ? 'Simpan perubahan' : 'Generate layout' }}</button>
        @if($editing)<a class="btn soft full mt-2" href="{{ route('feed-studio.show', $feedDesign) }}">Preview grid & export</a>@endif
    </aside>
</div>
