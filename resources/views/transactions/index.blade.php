@extends('layouts.main')

@section('title', 'Log Riwayat Mutasi Stok')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Log Audit Mutasi Stok (IN / OUT)</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Catatan kronologis seluruh arus barang masuk (Pembelian) dan barang keluar (Penjualan / Penyesuaian).</p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            + Transaksi Pembelian (Multi-Item)
        </a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">
            + Transaksi Penjualan (Multi-Item)
        </a>
        <a href="{{ route('transactions.in') }}" class="btn btn-secondary">
            + Mutasi Cepat IN
        </a>
        <a href="{{ route('transactions.out') }}" class="btn btn-secondary">
            - Mutasi Cepat OUT
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('transactions.index') }}" class="filter-bar" style="margin-bottom: 0;">
        <div class="filter-group" style="flex: 1; min-width: 240px;">
            <select name="product_id" class="form-select" id="filter-product">
                <option value="">Semua Produk</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" {{ ($filters['product_id'] == $p->id) ? 'selected' : '' }}>
                        {{ $p->sku }} - {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <select name="type" class="form-select" style="min-width: 150px;" id="filter-type">
                <option value="">Semua Jenis Mutasi</option>
                <option value="IN" {{ ($filters['type'] === 'IN') ? 'selected' : '' }}>Barang Masuk (IN)</option>
                <option value="OUT" {{ ($filters['type'] === 'OUT') ? 'selected' : '' }}>Barang Keluar (OUT)</option>
            </select>

            <input type="date" name="date_from" class="form-control" style="width: 150px;" value="{{ $filters['date_from'] ?? '' }}">
            <input type="date" name="date_to" class="form-control" style="width: 150px;" value="{{ $filters['date_to'] ?? '' }}">

            <button type="submit" class="btn btn-secondary">Filter</button>
            @if (!empty($filters['product_id']) || !empty($filters['type']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary" style="color: #ef4444;">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Table History -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="table-stock-movements">
            <thead>
                <tr>
                    <th>Waktu Transaksi</th>
                    <th>Tipe Mutasi</th>
                    <th>No. Referensi</th>
                    <th>SKU / Nama Produk</th>
                    <th>Jumlah Mutasi</th>
                    <th>Sisa Stok Saat Ini</th>
                    <th>Petugas (User)</th>
                    <th>Keterangan / Catatan</th>
                </tr>
            </thead>
            <tbody>
                @if ($history->isEmpty())
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Belum ada riwayat transaksi mutasi yang tercatat.
                        </td>
                    </tr>
                @else
                    @foreach ($history as $m)
                        <tr>
                            <td style="font-size: 0.85rem; color: var(--text-muted); white-space: nowrap;">
                                {{ $m->created_at->format('d M Y, H:i') }}
                            </td>
                            <td>
                                @if ($m->type === 'IN')
                                    <span class="badge badge-success">+ MASUK (IN)</span>
                                @else
                                    <span class="badge badge-danger">- KELUAR (OUT)</span>
                                @endif
                            </td>
                            <td><code>{{ $m->reference_number ?? '-' }}</code></td>
                            <td>
                                @if($m->product)
                                    <a href="{{ route('products.show', $m->product) }}" style="font-weight: 600;">
                                        {{ $m->product->name }}
                                    </a>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><code>{{ $m->product->sku }}</code></div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <strong style="font-size: 1.05rem;">{{ $m->quantity }}</strong> unit
                            </td>
                            <td>
                                {{ $m->product->stock ?? 0 }} unit
                            </td>
                            <td>
                                <span style="font-weight: 500; font-size: 0.88rem;">
                                    {{ $m->user->full_name ?? $m->user->username }}
                                </span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted); max-width: 250px;">
                                {{ $m->notes ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@if ($history->hasPages())
<div class="pagination">
    @if ($history->onFirstPage())
        <span class="page-link disabled">« Prev</span>
    @else
        <a href="{{ $history->previousPageUrl() }}" class="page-link">« Prev</a>
    @endif

    @foreach ($history->getUrlRange(1, $history->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page == $history->currentPage() ? 'active' : '' }}">{{ $page }}</a>
    @endforeach

    @if ($history->hasMorePages())
        <a href="{{ $history->nextPageUrl() }}" class="page-link">Next »</a>
    @else
        <span class="page-link disabled">Next »</span>
    @endif
</div>
@endif
@endsection
