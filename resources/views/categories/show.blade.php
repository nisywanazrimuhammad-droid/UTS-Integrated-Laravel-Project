@extends('layouts.main')

@section('title', 'Detail Kategori')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('categories.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">← Kembali ke Daftar Kategori</a>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <span class="badge badge-primary" style="margin-bottom: 0.5rem;">Kategori Produk</span>
    <h1 style="font-size: 1.6rem; font-weight: 800;">{{ $category->name }}</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.35rem;">{{ $category->description ?? 'Tidak ada deskripsi.' }}</p>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 1.1rem; font-weight: 700;">Daftar Produk dalam Kategori Ini</h3>
        <span class="badge badge-primary">{{ $products->total() }} produk</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr>
                        <td><code>{{ $p->sku }}</code></td>
                        <td style="font-weight: 600;"><a href="{{ route('products.show', $p) }}">{{ $p->name }}</a></td>
                        <td>Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                        <td style="font-weight: 600;">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                        <td><strong>{{ $p->stock }}</strong> unit</td>
                        <td style="text-align: right;">
                            <a href="{{ route('products.show', $p) }}" class="btn btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">Belum ada produk di kategori ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
