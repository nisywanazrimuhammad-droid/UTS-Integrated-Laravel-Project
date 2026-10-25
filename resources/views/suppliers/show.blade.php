@extends('layouts.main')

@section('title', 'Detail Supplier')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('suppliers.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">← Kembali ke Daftar Supplier</a>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span class="badge badge-primary" style="margin-bottom: 0.5rem;">Supplier Mitra</span>
            <h1 style="font-size: 1.6rem; font-weight: 800;">{{ $supplier->name }}</h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 0.25rem;">
                PIC: <strong>{{ $supplier->contact_person ?? '-' }}</strong> |
                Telp: {{ $supplier->phone ?? '-' }} |
                Email: {{ $supplier->email ?? '-' }}
            </p>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.25rem;">
                Alamat: {{ $supplier->address ?? '-' }}
            </p>
        </div>
        <div>
            <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-success">
                + Buat Pembelian (PO) dari Supplier Ini
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
            <span>📦 Produk yang Disuplai</span>
            <span class="badge badge-primary">{{ $suppliedProducts->count() }} produk</span>
        </h3>

        @if ($suppliedProducts->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.9rem; padding: 1.5rem 0; text-align: center;">
                Belum ada produk yang dikaitkan dengan supplier ini.
            </p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Harga Pasokan</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliedProducts as $sp)
                            <tr>
                                <td><code>{{ $sp->sku }}</code></td>
                                <td style="font-weight: 600;">
                                    <a href="{{ route('products.show', $sp) }}">{{ $sp->name }}</a>
                                </td>
                                <td style="font-weight: 600; color: var(--success);">
                                    Rp {{ number_format($sp->pivot->supplier_price ?? $sp->purchase_price, 0, ',', '.') }}
                                </td>
                                <td>Rp {{ number_format($sp->selling_price, 0, ',', '.') }}</td>
                                <td><strong>{{ $sp->stock }}</strong> unit</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
            <span>🚚 Riwayat Pembelian (PO)</span>
            <span class="badge badge-secondary">{{ $recentPurchases->count() }} transaksi</span>
        </h3>

        @if ($recentPurchases->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.9rem; padding: 1.5rem 0; text-align: center;">
                Belum ada riwayat pembelian dari supplier ini.
            </p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. PO</th>
                            <th>Tanggal</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentPurchases as $po)
                            <tr>
                                <td><a href="{{ route('purchases.show', $po) }}"><code>{{ $po->purchase_number }}</code></a></td>
                                <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $po->purchased_at->format('d/m/Y') }}</td>
                                <td>{{ $po->items_count }} item</td>
                                <td style="font-weight: 600;">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                                <td>
                                    @if($po->status === 'completed')
                                        <span class="badge badge-success">Selesai</span>
                                    @else
                                        <span class="badge badge-danger">Batal</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
