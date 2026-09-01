@extends('layouts.app')

@section('title', 'Top Up Kuota')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">PEMBAYARAN</span>
            <h1>Top up kuota</h1>
            <p>Transfer dan unggah bukti pembayaran.</p>
        </div>
    </div>

    <div class="top-up-grid">
        <section class="card payment-card">
            <span class="eyebrow">NOMINAL TRANSFER</span>
            <strong class="payment-amount">{{ rupiah($topUpSetting->amount) }}</strong>
            <span class="payment-credit">{{ $topUpSetting->credits }} kuota bermain</span>

            <div class="bank-list">
                @foreach(config('community.top_up.accounts') as $key => $account)
                    <article class="bank-card">
                        <span>{{ $account['bank'] }}</span>
                        <div><strong>{{ $account['number'] }}</strong><small>a.n. {{ $account['holder'] }}</small></div>
                    </article>
                @endforeach
            </div>

            <div class="payment-note">Pilih salah satu rekening tujuan. Nominal transfer harus sesuai.</div>
        </section>

        <section class="card">
            <span class="eyebrow">BUKTI TRANSFER</span>
            <h2>Kirim pengajuan</h2>

            <div class="top-up-steps" aria-label="Tahapan top up">
                <span><b>1</b>Transfer</span>
                <span><b>2</b>Unggah bukti</span>
                <span><b>3</b>Verifikasi</span>
                <span><b>4</b>Kuota masuk</span>
            </div>

            <form method="post" action="{{ route('top-ups.store') }}" enctype="multipart/form-data" class="compact-form">
                @csrf
                <input type="hidden" name="amount" value="{{ $topUpSetting->amount }}">

                @if($memberships->isNotEmpty())
                    <label>Paket
                        <select name="membership_id" required>
                            <option value="">Pilih paket</option>
                            @foreach($memberships as $membership)
                                <option value="{{ $membership->id }}" @selected(old('membership_id') == $membership->id)>{{ $membership->venue_name }} · {{ $membership->court_name }} · sisa {{ (int) $membership->balance }} kuota</option>
                            @endforeach
                        </select>
                    </label>
                @else
                    <div class="top-up-target">
                        <strong>Paket Komunitas</strong>
                        <span>Dibuat otomatis dan berlaku untuk semua sesi komunitas.</span>
                    </div>
                @endif

                <label>Rekening tujuan
                    <select name="bank" required>
                        <option value="">Pilih rekening</option>
                        @foreach(config('community.top_up.accounts') as $key => $account)
                            <option value="{{ $key }}" @selected(old('bank') === $key)>{{ $account['bank'] }} · {{ $account['number'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Bukti transfer
                    <input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <span class="field-help">JPG, PNG, WebP, atau PDF. Maksimal 4 MB.</span>
                </label>
                <button class="btn primary full">Kirim pengajuan</button>
            </form>
        </section>
    </div>

    <section class="card table-card">
        <div class="card-head"><div><span class="eyebrow">RIWAYAT</span><h2>Pengajuan top up</h2></div></div>
        <table>
            <thead><tr><th>TANGGAL</th><th>PAKET</th><th>REKENING</th><th>NOMINAL</th><th>STATUS</th><th>KUOTA</th><th>BUKTI</th></tr></thead>
            <tbody>
            @forelse($topUpRequests as $topUpRequest)
                <tr>
                    <td>{{ $topUpRequest->created_at->translatedFormat('d M Y, H:i') }}</td>
                    <td><strong>{{ $topUpRequest->membership->venue_name }}</strong><small>{{ $topUpRequest->membership->court_name }}</small></td>
                    <td>{{ config('community.top_up.accounts.'.$topUpRequest->bank.'.bank') }}</td>
                    <td class="money">{{ rupiah($topUpRequest->amount) }}</td>
                    <td><span class="status-pill {{ ['pending' => 'warning', 'approved' => 'active', 'rejected' => 'muted'][$topUpRequest->status] }}">{{ ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$topUpRequest->status] }}</span></td>
                    <td>{{ $topUpRequest->credits ? $topUpRequest->credits.'×' : '—' }}</td>
                    <td><a class="link" href="{{ route('top-ups.proof', $topUpRequest) }}" target="_blank" rel="noopener">Lihat</a></td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty">Belum ada pengajuan.</div></td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $topUpRequests->links() }}
    </section>
@endsection
