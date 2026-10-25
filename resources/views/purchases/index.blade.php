@extends('layouts.main')

@section('title', 'Transaksi Pembelian (Stock-In)')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Transaksi Pembelian / Barang Masuk (Purchases)</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Daftar pengadaan barang (Purchase Order) dari supplier yang otomatis menambah stok produk.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('purchases.create') }}" class="btn btn-success" id="btn-create-purchase">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Buat Transaksi Pembelian Baru
        </a>
    </div>
</div>

<!-- Filter Bar (Search, Supplier, Status, Date Range) -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('purchases.index') }}" class="filter-bar" style="margin-bottom: 0;">
        <div class="filter-group" style="flex: 1; min-width: 200px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nomor PO (contoh: PO-2026...)"
                   value="{{ $filters['search'] ?? '' }}">
        </div>

        <div class="filter-group">
            <select name="supplier_id" class="form-select" style="min-width: 180px;">
                <option value="">Semua Supplier</option>
                @foreach ($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ ($filters['supplier_id'] == $sup->id) ? 'selected' : '' }}>
                        {{ $sup->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-select" style="min-width: 140px;">
                <option value="">Semua Status</option>
                <option value="completed" {{ ($filters['status'] === 'completed') ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ ($filters['status'] === 'cancelled') ? 'selected' : '' }}>Cancelled</option>
            </select>

            <input type="date" name="date_from" class="form-control" style="width: 150px;" value="{{ $filters['date_from'] ?? '' }}" title="Dari Tanggal">
            <input type="date" name="date_to" class="form-control" style="width: 150px;" value="{{ $filters['date_to'] ?? '' }}" title="Sampai Tanggal">

            <button type="submit" class="btn btn-secondary">Filter</button>
            @if(!empty($filters['search']) || !empty($filters['supplier_id']) || !empty($filters['status']) || !empty($filters['date_from']) || !empty($filters['date_to']))
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary" style="color: #ef4444;">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Purchases Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="table-purchases">
            <thead>
                <tr>
                    <th>Nomor PO</th>
                    <th>Tanggal Pembelian</th>
                    <th>Supplier (Pemasok)</th>
                    <th>Jumlah Item</th>
                    <th>Total Pembelian</th>
                    <th>Status</th>
                    <th>Dicatat Oleh</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($purchases->isEmpty())
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Belum ada data transaksi pembelian yang sesuai filter.
                        </td>
                    </tr>
                @else
                    @foreach ($purchases as $po)
                        <tr>
                            <td>
                                <a href="{{ route('purchases.show', $po) }}" style="font-weight: 700;">
                                    <code>{{ $po->purchase_number }}</code>
                                </a>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted); white-space: nowrap;">
                                {{ $po->purchased_at->format('d M Y, H:i') }}
                            </td>
                            <td style="font-weight: 600;">
                                <a href="{{ route('suppliers.show', $po->supplier_id) }}" style="color: var(--text-main);">
                                    {{ $po->supplier->name ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $po->items_count }} jenis barang</span>
                            </td>
                            <td style="font-weight: 700; color: var(--success);">
                                Rp {{ number_format($po->total, 0, ',', '.') }}
                            </td>
                            <td>
                                @if ($po->status === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-danger">Cancelled</span>
                                @endif
                            </td>
                            <td style="font-size: 0.85rem;">
                                {{ $po->user->full_name ?? $po->user->username }}
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('purchases.show', $po) }}" class="btn btn-secondary btn-sm">
                                    Detail Invoice
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@if ($purchases->hasPages())
<div class="pagination">
    @if ($purchases->onFirstPage())
        <span class="page-link disabled">« Prev</span>
    @else
        <a href="{{ $purchases->previousPageUrl() }}" class="page-link">« Prev</a>
    @endif

    @foreach ($purchases->getUrlRange(1, $purchases->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page == $purchases->currentPage() ? 'active' : '' }}">{{ $page }}</a>
    @endforeach

    @if ($purchases->hasMorePages())
        <a href="{{ $purchases->nextPageUrl() }}" class="page-link">Next »</a>
    @else
        <span class="page-link disabled">Next »</span>
    @endif
</div>
@endif
@endsection
