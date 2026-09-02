@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">NOTIFIKASI</span>
            <h1>Kirim notifikasi</h1>
            <p>Kirim informasi langsung ke perangkat pemain.</p>
        </div>
        <span class="notification-device-count"><b>{{ $subscriptionCount }}</b> perangkat aktif</span>
    </div>

    <div class="admin-split notification-layout">
        <section class="card notification-form-card">
            <span class="eyebrow">PESAN BARU</span>
            <h2>Detail notifikasi</h2>
            <form method="post" action="{{ route('push-notifications.store') }}" class="compact-form">
                @csrf
                <label>Jenis
                    <select name="type" required>
                        <option value="schedule" @selected(old('type') === 'schedule')>Jadwal main</option>
                        <option value="slots" @selected(old('type') === 'slots')>Sisa slot</option>
                        <option value="important" @selected(old('type', 'important') === 'important')>Informasi penting</option>
                    </select>
                </label>
                <label>Penerima
                    <select name="audience" required>
                        <option value="all" @selected(old('audience', 'all') === 'all')>Semua akun pemain</option>
                        <option value="session" @selected(old('audience') === 'session')>Pemain di jadwal terpilih</option>
                    </select>
                </label>
                <label>Jadwal <span class="optional">Wajib untuk jadwal/sisa slot</span>
                    <select name="play_session_id">
                        <option value="">Tanpa jadwal tertentu</option>
                        @foreach($playSessions as $playSession)
                            <option value="{{ $playSession->id }}" @selected((string) old('play_session_id') === (string) $playSession->id)>
                                {{ $playSession->scheduled_at->translatedFormat('d M Y, H:i') }} · {{ $playSession->venue_name }} · {{ max(0, $playSession->max_players - $playSession->registrations_count) }} slot
                            </option>
                        @endforeach
                    </select>
                </label>
                <label>Judul
                    <input name="title" value="{{ old('title') }}" maxlength="255" placeholder="Contoh: Jadwal main Jumat dibuka" required>
                </label>
                <label>Isi notifikasi
                    <textarea name="body" rows="4" maxlength="500" placeholder="Tulis informasi singkat dan jelas." required>{{ old('body') }}</textarea>
                </label>
                <button class="btn primary full">Kirim sekarang</button>
            </form>
            <p class="notification-hint">Notifikasi hanya dikirim ke perangkat yang telah diaktifkan oleh pemain melalui dashboard.</p>
        </section>

        <section class="card notification-history-card">
            <div class="card-head">
                <div><span class="eyebrow">RIWAYAT</span><h2>Pengiriman terakhir</h2></div>
            </div>
            @forelse($notifications as $notification)
                <article class="notification-history-row">
                    <div>
                        <strong>{{ $notification->title }}</strong>
                        <p>{{ $notification->body }}</p>
                        <small>
                            {{ $notification->created_at->translatedFormat('d M Y, H:i') }}
                            · {{ $notification->sender->name }}
                            @if($notification->playSession)
                                · {{ $notification->playSession->venue_name }}
                            @endif
                        </small>
                    </div>
                    <span class="notification-result">
                        <b>{{ $notification->success_count }}/{{ $notification->device_count }}</b>
                        <small>terkirim</small>
                    </span>
                </article>
            @empty
                <div class="empty-state compact"><span>◉</span><h2>Belum ada notifikasi</h2><p>Riwayat pengiriman akan tampil di sini.</p></div>
            @endforelse
            {{ $notifications->links() }}
        </section>
    </div>
@endsection
