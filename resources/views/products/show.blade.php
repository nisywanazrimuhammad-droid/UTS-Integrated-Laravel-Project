@extends('layouts.main')

@section('title', 'Detail Produk')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('products.index') }}" style="color: var(--text-muted); font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        ← Kembali ke Katalog Produk
    </a>
</div>

<!-- Header & Quick Action -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary); background: var(--primary-light); padding: 0.25rem 0.6rem; border-radius: var(--radius-sm);">
                    {{ $product->sku }}
                </span>
                <span class="badge badge-secondary">{{ $product->category->name ?? 'Tanpa Kategori' }}</span>
                @if ($product->stock <= 0)
                    <span class="badge badge-danger">Stok Habis</span>
                @elseif ($product->stock <= $product->minimum_stock)
                    <span class="badge badge-warning">Stok Menipis</span>
                @else
                    <span class="badge badge-success">Stok Aman</span>
                @endif
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800;">{{ $product->name }}</h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 0.25rem;">
                Terdaftar sejak: {{ $product->created_at?->format('d F Y, H:i') ?? '-' }}
            </p>
        </div>

        <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
            <a href="{{ route('purchases.create', ['product_id' => $product->id]) }}" class="btn btn-success" id="btn-in-detail">
                + Pembelian Stok (Stock-In)
            </a>
            <a href="{{ route('sales.create', ['product_id' => $product->id]) }}" class="btn btn-primary" id="btn-out-detail">
                + Transaksi Penjualan (Sales)
            </a>
            @if (auth()->user()->isAdmin())
            <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary" id="btn-edit-detail">
                Edit
            </a>
            <form action="{{ route('products.destroy', $product) }}" method="POST" style="display: inline;"
                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary" style="color: #ef4444;" id="btn-delete-detail">
                    Hapus
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Statistik Ringkas Produk -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-subtle);">
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Harga Beli (Modal)</div>
            <div style="font-size: 1.25rem; font-weight: 700;">
                Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Harga Jual Satuan</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">
                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Stok Tersedia</div>
            <div style="font-size: 1.35rem; font-weight: 800;">
                {{ $product->stock }} <span style="font-size: 0.85rem; font-weight: 400; color: var(--text-muted);">unit</span>
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Stok Minimum</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--warning);">
                {{ $product->minimum_stock }} <span style="font-size: 0.85rem; font-weight: 400; color: var(--text-muted);">unit</span>
            </div>
        </div>
        <div>
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Nilai Valuasi Stok</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--accent-cyan);">
                Rp {{ number_format($product->stock * $product->selling_price, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.5rem;">
    <!-- Daftar Supplier Terkait -->
    <div class="card" id="card-product-suppliers">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
            <span>🏢 Supplier Terkait</span>
            <span class="badge badge-primary">{{ $product->suppliers->count() }} supplier</span>
        </h3>

        @if ($product->suppliers->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.88rem; padding: 1rem 0;">Belum ada supplier yang dikaitkan dengan produk ini.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Supplier</th>
                            <th>Kontak / Telp</th>
                            <th>Harga Pasok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product->suppliers as $s)
                            <tr>
                                <td>
                                    <a href="{{ route('suppliers.show', $s) }}" style="font-weight: 600;">
                                        {{ $s->name }}
                                    </a>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);">
                                    {{ $s->contact_person ?? '-' }}<br>
                                    {{ $s->phone ?? '-' }}
                                </td>
                                <td style="font-weight: 600; color: var(--success);">
                                    Rp {{ number_format($s->pivot->supplier_price ?? $product->purchase_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Riwayat Mutasi Stok Produk Ini -->
    <div class="card" id="card-product-movements">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
            <span>⏱️ Riwayat Mutasi Stok</span>
            <a href="{{ route('transactions.index', ['product_id' => $product->id]) }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </h3>

        @if ($history->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.88rem; padding: 1rem 0;">Belum ada catatan mutasi stok untuk produk ini.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Jumlah</th>
                            <th>Petugas</th>
                            <th>Referensi / Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $h)
                            <tr>
                                <td style="font-size: 0.82rem; color: var(--text-muted); white-space: nowrap;">
                                    {{ $h->created_at->format('d/m/y H:i') }}
                                </td>
                                <td>
                                    @if ($h->type === 'IN')
                                        <span class="badge badge-success">+ IN</span>
                                    @else
                                        <span class="badge badge-danger">- OUT</span>
                                    @endif
                                </td>
                                <td><strong>{{ $h->quantity }}</strong></td>
                                <td style="font-size: 0.82rem;">{{ $h->user->full_name ?? $h->user->username }}</td>
                                <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $h->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
