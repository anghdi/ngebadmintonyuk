@extends('layouts.app')
@section('title', 'Tamu')
@section('content')
<div class="page-head"><div><h1>Tamu</h1><p>Pemain tanpa akun. Tambahkan ke sesi lewat halaman absensi.</p></div><a class="btn soft" href="{{ route('play-sessions.index') }}">Pilih sesi</a></div>
<section class="card">
    <details class="registration-create-panel" @if($errors->any()) open @endif>
        <summary>+ Tambah tamu</summary>
        <form method="post" action="{{ route('guests.store') }}" class="registration-create-form">
            @csrf
            <label>Nama<input name="name" value="{{ old('name') }}" maxlength="255" required></label>
            <label>WhatsApp <span class="optional">Opsional</span><input name="phone" value="{{ old('phone') }}" inputmode="tel" placeholder="08…" maxlength="25"></label>
            <button class="btn primary">Simpan tamu</button>
        </form>
    </details>
    <div class="overflow-x-auto">
        <table><thead><tr><th>NAMA</th><th>WHATSAPP</th><th>HADIR</th><th>TIDAK HADIR</th><th></th></tr></thead><tbody>
        @forelse($guests as $guest)
            <tr><td><strong>{{ $guest->name }}</strong><small>{{ $guest->registrations_count }} sesi terdaftar</small></td><td>{{ $guest->phone ?: '—' }}</td><td>{{ $guest->present_count }}×</td><td>{{ $guest->no_show_count }}/3 @if($guest->no_show_count >= 3)<span class="status-pill danger">Diblokir</span>@endif</td><td>
                <details class="registration-editor"><summary>Edit</summary>
                    <form method="post" action="{{ route('guests.update', $guest) }}">
                        @csrf @method('put')
                        <label>Nama<input name="name" value="{{ $guest->name }}" maxlength="255" required></label>
                        <label>WhatsApp<input name="phone" value="{{ $guest->phone }}" inputmode="tel" maxlength="25"></label>
                        <button class="btn primary">Simpan</button>
                    </form>
                </details>
            </td></tr>
        @empty
            <tr><td colspan="5"><div class="empty">Belum ada tamu. Tambahkan nama untuk mulai.</div></td></tr>
        @endforelse
        </tbody></table>
    </div>
    {{ $guests->links() }}
</section>
@endsection
