@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<div class="actions report-download">
    <a class="btn dark" href="{{ route('reports.pdf', ['start_date' => $start, 'end_date' => $end]) }}">Unduh PDF</a>
</div>
<div class="page-head"><div><h1>Laporan</h1><p>Ringkasan keuangan berdasarkan periode.</p></div></div>
<div class="card filters"><form><label>Dari<input type="date" name="start_date" value="{{ $start }}" required></label><label>Sampai<input type="date" name="end_date" value="{{ $end }}" required></label><button class="btn primary">Tampilkan</button></form></div>
<div class="stats four">
    <article class="green"><small>PEMASUKAN</small><strong>{{ rupiah($totalIncome) }}</strong></article>
    <article class="red"><small>PENGELUARAN</small><strong>{{ rupiah($totalExpense) }}</strong></article>
    <article class="blue"><small>SELISIH PERIODE</small><strong>{{ signed_rupiah($difference) }}</strong></article>
    <article class="yellow"><small>SALDO PER {{ \Carbon\Carbon::parse($end)->translatedFormat('d M Y') }}</small><strong>{{ rupiah($balance) }}</strong></article>
</div>
<div class="grid-2">
    <div class="card"><h2>Pemasukan per kategori</h2>@forelse($incomeByCategory as $name => $amount)<div class="detail-line"><span>{{ $name }}</span><b>{{ rupiah($amount) }}</b></div>@empty<div class="empty">Tidak ada pemasukan.</div>@endforelse</div>
    <div class="card"><h2>Pengeluaran per kategori</h2>@forelse($expenseByCategory as $name => $amount)<div class="detail-line"><span>{{ $name }}</span><b>{{ rupiah($amount) }}</b></div>@empty<div class="empty">Tidak ada pengeluaran.</div>@endforelse</div>
</div>
<div class="grid-2">
    <div class="card"><h2>Daftar pemasukan</h2>@forelse($incomes as $item)<a class="transaction-row" href="{{ route('incomes.show', $item) }}"><span class="dot">{{ $loop->iteration }}</span><span><b>{{ $item->details->pluck('name')->join(', ') }}</b><small>{{ $item->category->name }} · {{ $item->date->translatedFormat('d M Y') }}</small></span><strong>{{ rupiah($item->details_sum_amount) }}</strong></a>@empty<div class="empty">Tidak ada transaksi.</div>@endforelse</div>
    <div class="card"><h2>Daftar pengeluaran</h2>@forelse($expenses as $item)<a class="transaction-row" href="{{ route('expenses.show', $item) }}"><span class="dot">{{ $loop->iteration }}</span><span><b>{{ $item->details->pluck('name')->join(', ') }}</b><small>{{ $item->category->name }} · {{ $item->date->translatedFormat('d M Y') }}</small></span><strong>{{ rupiah($item->details_sum_amount) }}</strong></a>@empty<div class="empty">Tidak ada transaksi.</div>@endforelse</div>
</div>
@endsection
