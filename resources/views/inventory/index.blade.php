@extends('layouts.app')
@section('title', 'Inventori Shuttlecock')
@section('content')
<div class="page-head"><div><span class="eyebrow">INVENTORI</span><h1>Shuttlecock</h1><p>Pantau persediaan dan penggunaan shuttlecock.</p></div></div>

<div class="inventory-summary">
    @forelse($items as $item)
        <article @class(['low-stock' => (int) $item->stock <= $item->minimum_stock, 'inactive-item' => ! $item->is_active])>
            <div class="inventory-item-summary"><span>🏸</span><div><small>{{ $item->brand ?: 'Tanpa merek' }}</small><strong>{{ $item->name }}</strong><p>{{ (int) $item->stock }} buah · {{ number_format((int) $item->stock / $item->pieces_per_tube, 1, ',', '.') }} tabung</p></div><b>{{ ! $item->is_active ? 'Nonaktif' : ((int) $item->stock <= $item->minimum_stock ? 'Stok menipis' : 'Aman') }}</b></div>
            <details class="inventory-editor">
                <summary>Edit data</summary>
                <form method="post" action="{{ route('inventory.items.update', $item) }}" class="compact-form">
                    @csrf @method('put')
                    <label>Nama produk<input name="name" value="{{ $item->name }}" required></label>
                    <label>Merek<input name="brand" value="{{ $item->brand }}"></label>
                    <div class="form-grid"><label>Isi per tabung<input type="number" name="pieces_per_tube" value="{{ $item->pieces_per_tube }}" min="1" required></label><label>Batas minimum<input type="number" name="minimum_stock" value="{{ $item->minimum_stock }}" min="0" required></label></div>
                    <label>Status<select name="is_active"><option value="1" @selected($item->is_active)>Aktif</option><option value="0" @selected(! $item->is_active)>Nonaktif</option></select></label>
                    <button class="btn primary full">Simpan perubahan</button>
                </form>
                @if((int) $item->movements_count === 0)
                    <form method="post" action="{{ route('inventory.items.destroy', $item) }}" class="inventory-delete" onsubmit="return confirm('Hapus jenis shuttlecock ini?')">@csrf @method('delete')<button class="link danger">Hapus jenis</button></form>
                @endif
            </details>
        </article>
    @empty
        <div class="empty-state"><span>🏸</span><h2>Belum ada persediaan</h2><p>Tambahkan jenis shuttlecock terlebih dahulu.</p></div>
    @endforelse
</div>

<div class="admin-split inventory-forms">
    <section class="card package-form-card">
        <span class="eyebrow">DATA BARANG</span><h2>Tambah jenis</h2>
        <form method="post" action="{{ route('inventory.items.store') }}" class="compact-form">
            @csrf
            <label>Nama produk<input name="name" value="{{ old('name') }}" required></label>
            <label>Merek <span class="optional">Opsional</span><input name="brand" value="{{ old('brand') }}"></label>
            <div class="form-grid"><label>Isi per tabung<input type="number" name="pieces_per_tube" value="{{ old('pieces_per_tube', 12) }}" min="1" required></label><label>Batas stok minimum<input type="number" name="minimum_stock" value="{{ old('minimum_stock', 12) }}" min="0" required></label></div>
            <button class="btn dark full">Simpan jenis</button>
        </form>
    </section>

    <section class="card package-form-card">
        <span class="eyebrow">MUTASI STOK</span><h2>Catat perubahan</h2>
        <form method="post" action="{{ route('inventory.movements.store') }}" class="compact-form">
            @csrf
            <label>Shuttlecock<select name="shuttlecock_item_id" required><option value="">Pilih produk</option>@foreach($items->where('is_active', true) as $item)<option value="{{ $item->id }}" @selected(old('shuttlecock_item_id') == $item->id)>{{ $item->brand }} {{ $item->name }} · stok {{ (int) $item->stock }}</option>@endforeach</select></label>
            <div class="form-grid"><label>Jenis<select name="type" required><option value="purchase">Stok masuk</option><option value="usage">Dipakai</option><option value="adjustment">Koreksi tambah</option></select></label><label>Jumlah buah<input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required></label></div>
            <label>Sesi terkait <span class="optional">Opsional</span><select name="play_session_id"><option value="">Tanpa sesi</option>@foreach($playSessions as $session)<option value="{{ $session->id }}">{{ $session->scheduled_at->translatedFormat('d M Y') }} · {{ $session->venue_name }}</option>@endforeach</select></label>
            <label>Harga per buah <span class="optional">Opsional</span><input type="number" name="unit_cost" value="{{ old('unit_cost') }}" min="0"></label>
            <label>Catatan <span class="optional">Opsional</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
            <button class="btn primary full">Catat mutasi</button>
        </form>
    </section>
</div>

<section class="card table-card">
    <div class="card-head"><div><span class="eyebrow">BUKU STOK</span><h2>Riwayat mutasi</h2></div></div>
    <table><thead><tr><th>WAKTU</th><th>BARANG</th><th>JENIS</th><th>SESI</th><th>JUMLAH</th><th>PENCATAT</th></tr></thead><tbody>
    @forelse($movements as $movement)
        <tr><td>{{ $movement->created_at->translatedFormat('d M Y, H:i') }}</td><td><strong>{{ $movement->item->name }}</strong><small>{{ $movement->item->brand }}</small></td><td><span class="status-pill {{ $movement->quantity > 0 ? 'active' : 'warning' }}">{{ ['purchase' => 'Masuk', 'usage' => 'Dipakai', 'adjustment' => 'Koreksi'][$movement->type] }}</span></td><td>{{ $movement->playSession?->scheduled_at?->translatedFormat('d M Y') ?? '—' }}</td><td><strong class="{{ $movement->quantity > 0 ? 'income' : 'expense' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</strong></td><td>{{ $movement->creator->name }}</td></tr>
    @empty
        <tr><td colspan="6"><div class="empty">Belum ada mutasi stok.</div></td></tr>
    @endforelse
    </tbody></table>
    {{ $movements->links() }}
</section>
@endsection
