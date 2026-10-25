@extends('layouts.main')

@section('title', 'Transaksi Penjualan (Sales)')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Transaksi Penjualan / Barang Keluar (Sales)</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Daftar penjualan barang ke pelanggan yang otomatis mengurangi stok produk secara atomik.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('sales.create') }}" class="btn btn-primary" id="btn-create-sale">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Buat Transaksi Penjualan Baru
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('sales.index') }}" class="filter-bar" style="margin-bottom: 0;">
        <div class="filter-group" style="flex: 1; min-width: 220px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor invoice atau nama pelanggan..."
                   value="{{ $filters['search'] ?? '' }}">
        </div>

        <div class="filter-group">
            <select name="status" class="form-select" style="min-width: 140px;">
                <option value="">Semua Status</option>
                <option value="completed" {{ ($filters['status'] === 'completed') ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ ($filters['status'] === 'cancelled') ? 'selected' : '' }}>Cancelled</option>
            </select>

            <input type="date" name="date_from" class="form-control" style="width: 150px;" value="{{ $filters['date_from'] ?? '' }}" title="Dari Tanggal">
            <input type="date" name="date_to" class="form-control" style="width: 150px;" value="{{ $filters['date_to'] ?? '' }}" title="Sampai Tanggal">

            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($filters['search']) || !empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                <a href="{{ route('sales.index') }}" class="btn btn-secondary" style="color: #ef4444;">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Sales Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="table-sales">
            <thead>
                <tr>
                    <th>Nomor Invoice</th>
                    <th>Tanggal Penjualan</th>
                    <th>Nama Pelanggan</th>
                    <th>Jumlah Item</th>
                    <th>Total Penjualan</th>
                    <th>Status</th>
                    <th>Kasir / Staff</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($sales->isEmpty())
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Belum ada data transaksi penjualan yang sesuai filter.
                        </td>
                    </tr>
                @else
                    @foreach ($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" style="font-weight: 700;">
                                    <code>{{ $sale->sale_number }}</code>
                                </a>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted); white-space: nowrap;">
                                {{ $sale->sold_at->format('d M Y, H:i') }}
                            </td>
                            <td style="font-weight: 600;">
                                {{ $sale->customer_name ?? 'Pelanggan Umum' }}
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $sale->items_count }} jenis barang</span>
                            </td>
                            <td style="font-weight: 700; color: var(--primary);">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td>
                                @if ($sale->status === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem;">
                                {{ $sale->user->full_name ?? $sale->user->username }}
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary btn-sm">
                                    Detail Struk
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@if ($sales->hasPages())
<div class="pagination">
    @if ($sales->onFirstPage())
        <span class="page-link disabled">« Prev</span>
    @else
        <a href="{{ $sales->previousPageUrl() }}" class="page-link">« Prev</a>
    @endif

    @foreach ($sales->getUrlRange(1, $sales->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page == $sales->currentPage() ? 'active' : '' }}">{{ $page }}</a>
    @endforeach

    @if ($sales->hasMorePages())
        <a href="{{ $sales->nextPageUrl() }}" class="page-link">Next »</a>
    @else
        <span class="page-link disabled">Next »</span>
    @endif
</div>
@endif
@endsection
