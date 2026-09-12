@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="page-head dashboard-head">
        <div>
            <span class="eyebrow">Hari ini</span>
            <h1>Selamat datang, {{ str(auth()->user()->name)->before(' ') }}.</h1>
            <p>Ringkasan operasional komunitas.</p>
        </div>
        <div class="actions">
            <a class="btn primary" href="{{ route('incomes.create') }}"><span aria-hidden="true">＋</span> Pemasukan</a>
            <a class="btn dark" href="{{ route('expenses.create') }}"><span aria-hidden="true">−</span> Pengeluaran</a>
        </div>
    </div>

    <div class="club-strip">
        <a href="{{ route('members.index') }}"><small>Member</small><strong>{{ $memberCount }}</strong><span>Lihat data <b>→</b></span></a>
        <a href="{{ route('play-sessions.index') }}"><small>Sesi mendatang</small><strong>{{ $upcomingSessionCount }}</strong><span>Lihat jadwal <b>→</b></span></a>
        <a href="{{ route('inventory.index') }}"><small>Stok rendah</small><strong>{{ $lowStockCount }}</strong><span>Lihat stok <b>→</b></span></a>
    </div>

    <section class="balance-card">
        <div>
            <small>Saldo kas</small>
            <strong>{{ rupiah($balance) }}</strong>
            <p>Posisi kas saat ini.</p>
        </div>
        <span class="balance-mark" aria-hidden="true"><img src="{{ asset('icon.png') }}" alt=""></span>
    </section>

    <div class="section-heading">
        <div><span class="eyebrow">Kas</span><h2>Bulan ini</h2></div>
    </div>
    <div class="stats">
        <article class="green"><small>Masuk</small><strong>{{ rupiah($totalIncome) }}</strong></article>
        <article class="red"><small>Keluar</small><strong>{{ rupiah($totalExpense) }}</strong></article>
        <article class="blue"><small>Selisih</small><strong>{{ signed_rupiah($difference) }}</strong></article>
    </div>

    <section class="card activity-card">
        <div class="card-head"><div><span class="eyebrow">Aktivitas</span><h2>Transaksi terbaru</h2></div></div>
        @forelse($latest as $row)
            <a class="transaction-row" href="{{ route($row['type'].'s.show', $row['item']) }}">
                <span class="dot {{ $row['type'] }}">{{ $row['type'] === 'income' ? '↗' : '↘' }}</span>
                <span><b>{{ $row['item']->category->name }}</b><small>{{ $row['item']->date->translatedFormat('d M Y') }} · {{ $row['item']->description ?: 'Tanpa catatan' }}</small></span>
                <strong class="{{ $row['type'] }}">{{ $row['type'] === 'income' ? '+ ' : '- ' }}{{ rupiah($row['item']->details_sum_amount) }}</strong>
            </a>
        @empty
            <div class="empty-state compact"><span>↗</span><h2>Belum ada transaksi</h2><p>Transaksi terbaru akan ditampilkan di sini.</p></div>
        @endforelse
    </section>
@endsection
