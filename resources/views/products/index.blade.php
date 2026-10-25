@extends('layouts.main')

@section('title', 'Daftar Produk')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Katalog & Stok Produk</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Total {{ $products->total() }} produk terdaftar dalam sistem.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('products.create') }}" class="btn btn-primary" id="btn-add-product">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Produk
        </a>
        @endif
    </div>
</div>

<!-- Filter Bar: Search, Category Filter, Low Stock Toggle, Sorting -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('products.index') }}" class="filter-bar" style="margin-bottom: 0;">
        <div class="filter-group" style="flex: 1; min-width: 240px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama atau SKU produk..."
                   value="{{ $filters['search'] ?? '' }}" id="input-search">
        </div>

        <div class="filter-group">
            <select name="category_id" class="form-select" style="min-width: 170px;" id="select-category-filter">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] == $cat->id) ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>

            <select name="sort" class="form-select" style="min-width: 140px;" id="select-sort">
                <option value="name" {{ ($filters['sort'] === 'name') ? 'selected' : '' }}>Urut: Nama</option>
                <option value="selling_price" {{ ($filters['sort'] === 'selling_price') ? 'selected' : '' }}>Urut: Harga Jual</option>
                <option value="stock" {{ ($filters['sort'] === 'stock') ? 'selected' : '' }}>Urut: Stok</option>
                <option value="sku" {{ ($filters['sort'] === 'sku') ? 'selected' : '' }}>Urut: SKU</option>
            </select>

            <select name="dir" class="form-select" style="min-width: 100px;" id="select-dir">
                <option value="asc" {{ ($filters['dir'] === 'asc') ? 'selected' : '' }}>Naik (ASC)</option>
                <option value="desc" {{ ($filters['dir'] === 'desc') ? 'selected' : '' }}>Turun (DESC)</option>
            </select>

            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.88rem; cursor: pointer; user-select: none;">
                <input type="checkbox" name="low_stock" value="1" {{ !empty($filters['low_stock']) ? 'checked' : '' }} id="check-low-stock">
                <span>Stok Rendah</span>
            </label>

            <button type="submit" class="btn btn-secondary" id="btn-filter-apply">Terapkan</button>
            @if(!empty($filters['search']) || !empty($filters['category_id']) || !empty($filters['low_stock']))
                <a href="{{ route('products.index') }}" class="btn btn-secondary" style="color: #ef4444;" title="Reset Filter">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Product Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="product-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok Saat Ini</th>
                    <th>Status Stok</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if($products->isEmpty())
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            Tidak ada produk yang sesuai dengan kriteria pencarian.
                        </td>
                    </tr>
                @else
                    @foreach ($products as $p)
                        <tr>
                            <td><code>{{ $p->sku }}</code></td>
                            <td>
                                <a href="{{ route('products.show', $p) }}" style="font-weight: 600; color: var(--text-main);">
                                    {{ $p->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $p->category->name ?? 'Tanpa Kategori' }}</span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.86rem;">
                                Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                            </td>
                            <td style="font-weight: 700;">
                                Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                            </td>
                            <td>
                                <span style="font-size: 1.05rem; font-weight: 700;">{{ $p->stock }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">/ min {{ $p->minimum_stock }}</span>
                            </td>
                            <td>
                                @if ($p->stock <= 0)
                                    <span class="badge badge-danger">Habis</span>
                                @elseif ($p->stock <= $p->minimum_stock)
                                    <span class="badge badge-warning">Menipis</span>
                                @else
                                    <span class="badge badge-success">Aman</span>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <a href="{{ route('products.show', $p) }}" class="btn btn-secondary btn-sm" title="Detail">
                                        Detail
                                    </a>
                                    <a href="{{ route('purchases.create', ['product_id' => $p->id]) }}" class="btn btn-success btn-sm" title="Pembelian Stok">
                                        + Beli
                                    </a>
                                    <a href="{{ route('sales.create', ['product_id' => $p->id]) }}" class="btn btn-primary btn-sm" title="Penjualan">
                                        Jual
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                    <a href="{{ route('products.edit', $p) }}" class="btn btn-secondary btn-sm" title="Edit">
                                        Edit
                                    </a>
                                    <form action="{{ route('products.destroy', $p) }}" method="POST" style="display: inline;"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: #ef4444;" title="Hapus">
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if ($products->hasPages())
<div class="pagination">
    @if ($products->onFirstPage())
        <span class="page-link disabled">« Prev</span>
    @else
        <a href="{{ $products->previousPageUrl() }}" class="page-link">« Prev</a>
    @endif

    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page == $products->currentPage() ? 'active' : '' }}">{{ $page }}</a>
    @endforeach

    @if ($products->hasMorePages())
        <a href="{{ $products->nextPageUrl() }}" class="page-link">Next »</a>
    @else
        <span class="page-link disabled">Next »</span>
    @endif
</div>
@endif
@endsection
