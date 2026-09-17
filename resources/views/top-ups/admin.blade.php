@extends('layouts.app')

@section('title', 'Verifikasi Top Up')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Pembayaran</span>
            <h1>Verifikasi top up</h1>
            <p>Periksa transfer sebelum menambahkan kuota.</p>
        </div>
    </div>

    <section class="card top-up-setting-card">
        <div>
            <span class="eyebrow">Pengaturan</span>
            <h2>Paket top up</h2>
            <p>{{ $topUpSetting->credits }} kuota per pengajuan.</p>
        </div>
        <form method="post" action="{{ route('top-up-settings.update') }}" class="setting-form">
            @csrf @method('put')
            <label>Harga paket<input type="number" name="amount" value="{{ old('amount', $topUpSetting->amount) }}" min="1000" max="100000000" required></label>
            <button class="btn primary">Simpan pengaturan</button>
        </form>
    </section>

    <section class="top-up-summary" aria-label="Ringkasan top up">
        <article>
            <span>Perlu diperiksa</span>
            <strong>{{ $topUpSummary['pending'] }}</strong>
            <small>Pengajuan menunggu</small>
        </article>
        <article>
            <span>Disetujui bulan ini</span>
            <strong>{{ $topUpSummary['approved_this_month'] }}</strong>
            <small>Pengajuan selesai</small>
        </article>
        <article>
            <span>Top up bulan ini</span>
            <strong>{{ rupiah($topUpSummary['amount_this_month']) }}</strong>
            <small>Sudah masuk kas</small>
        </article>
    </section>

    <nav class="top-up-filters" aria-label="Filter status pengajuan">
        <a @class(['active' => $statusFilter === null]) href="{{ route('top-ups.index') }}">Semua</a>
        <a @class(['active' => $statusFilter === 'pending']) href="{{ route('top-ups.index', ['status' => 'pending']) }}">Menunggu · {{ $topUpSummary['pending'] }}</a>
        <a @class(['active' => $statusFilter === 'approved']) href="{{ route('top-ups.index', ['status' => 'approved']) }}">Disetujui</a>
        <a @class(['active' => $statusFilter === 'rejected']) href="{{ route('top-ups.index', ['status' => 'rejected']) }}">Ditolak</a>
    </nav>

    <section class="card table-card">
        <table class="review-table">
            <thead><tr><th>MEMBER</th><th>PAKET</th><th>TRANSFER</th><th>BUKTI</th><th>STATUS</th><th>VERIFIKASI</th></tr></thead>
            <tbody>
            @forelse($topUpRequests as $topUpRequest)
                <tr>
                    <td><strong>{{ $topUpRequest->member->name }}</strong><small>{{ $topUpRequest->created_at->translatedFormat('d M Y, H:i') }}</small></td>
                    <td><strong>{{ $topUpRequest->membership->venue_name }}</strong><small>{{ $topUpRequest->membership->court_name }}</small></td>
                    <td><strong>{{ rupiah($topUpRequest->amount) }}</strong><small>{{ config('community.top_up.accounts.'.$topUpRequest->bank.'.bank') }} · {{ $topUpRequest->credits }} kuota</small></td>
                    <td><a class="btn soft proof-button" href="{{ route('top-ups.proof', $topUpRequest) }}" target="_blank" rel="noopener">Lihat bukti</a></td>
                    <td><span class="status-pill {{ ['pending' => 'warning', 'approved' => 'active', 'rejected' => 'muted'][$topUpRequest->status] }}">{{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$topUpRequest->status] }}</span></td>
                    <td>
                        @if($topUpRequest->status === 'pending')
                            <form method="post" action="{{ route('top-ups.update', $topUpRequest) }}" class="review-form">
                                @csrf @method('put')
                                <input name="review_notes" placeholder="Alasan jika ditolak" aria-label="Catatan verifikasi untuk {{ $topUpRequest->member->name }}">
                                <div class="review-actions">
                                    <button class="btn primary" name="status" value="approved">Setujui</button>
                                    <button class="btn soft" name="status" value="rejected">Tolak</button>
                                </div>
                            </form>
                        @else
                            <span>{{ $topUpRequest->status === 'approved' ? $topUpRequest->credits.' kuota' : 'Tidak diberikan' }}</span>
                            @if($topUpRequest->income_id)
                                <a class="link" href="{{ route('incomes.show', $topUpRequest->income_id) }}">Lihat pemasukan</a>
                            @elseif($topUpRequest->status === 'approved')
                                <small>Persetujuan lama: belum ditautkan ke kas otomatis.</small>
                            @endif
                            @if($topUpRequest->review_notes)<small>{{ $topUpRequest->review_notes }}</small>@endif
                        @endif
                        @if($topUpRequest->status !== 'approved')
                            <form method="post" action="{{ route('top-ups.destroy', $topUpRequest) }}" class="mt-3" onsubmit="return confirm('Hapus pengajuan top up ini? Bukti transfer juga akan dihapus.')">
                                @csrf @method('delete')
                                <button class="link danger">Hapus pengajuan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">{{ $statusFilter ? 'Tidak ada pengajuan dengan status ini.' : 'Belum ada pengajuan top up.' }}</div></td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $topUpRequests->links() }}
    </section>
@endsection
