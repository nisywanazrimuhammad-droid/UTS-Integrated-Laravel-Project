@extends('layouts.main')

@section('title', 'Detail Penjualan #' . $sale->sale_number)

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('sales.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">
        ← Kembali ke Daftar Penjualan
    </a>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-secondary btn-sm" onclick="window.print()">🖨️ Cetak Invoice</button>
        @if(auth()->user()->isAdmin() && $sale->status === 'completed')
            <form action="{{ route('sales.cancel', $sale) }}" method="POST"
                  onsubmit="return confirm('Batalkan transaksi penjualan ini? Stok produk yang terjual akan dikembalikan (dipulihkan) ke gudang.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Batalkan Penjualan (Pulihkan Stok)</button>
            </form>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle);">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span class="badge badge-primary">SALES INVOICE (STOCK-OUT)</span>
                @if($sale->status === 'completed')
                    <span class="badge badge-success">COMPLETED</span>
                @else
                    <span class="badge badge-danger">CANCELLED (STOK DIPULIHKAN)</span>
                @endif
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800;"><code>{{ $sale->sale_number }}</code></h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 0.25rem;">
                Waktu Transaksi: <strong>{{ $sale->sold_at->format('d F Y, H:i') }}</strong>
            </p>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Total Tagihan Penjualan</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--primary);">
                Rp {{ number_format($sale->total, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Pelanggan</div>
            <div style="font-weight: 700; font-size: 1rem; margin-top: 0.2rem;">
                {{ $sale->customer_name ?? 'Pelanggan Umum / Walk-in' }}
            </div>
        </div>

        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Kasir / Staff Melayani</div>
            <div style="font-weight: 700; font-size: 1rem; margin-top: 0.2rem;">
                {{ $sale->user->full_name ?? $sale->user->username }}
            </div>
            <div style="font-size: 0.84rem; color: var(--text-muted);">Role: {{ strtoupper($sale->user->role ?? 'staff') }}</div>
        </div>

        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Metode Pembayaran</div><div style="margin: 0.25rem 0 0.85rem 0;"><span class="badge badge-success">{{ $sale->payment_method ?? 'COD (Bayar di Tempat)' }}</span></div><div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Catatan</div>
            <div style="font-size: 0.9rem; margin-top: 0.2rem;">{{ $sale->notes ?: '-' }}</div>
        </div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-subtle);">
        <h3 style="font-size: 1.1rem; font-weight: 700;">Rincian Barang yang Terjual ({{ $sale->items->count() }} Baris)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Kuantitas Keluar</th>
                    <th>Harga Jual Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><code>{{ $item->product->sku ?? '-' }}</code></td>
                        <td style="font-weight: 600;">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td><span class="badge badge-secondary">{{ $item->product->category->name ?? '-' }}</span></td>
                        <td><strong>-{{ $item->quantity }}</strong> unit</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: var(--bg-surface); font-weight: 800;">
                    <td colspan="6" style="text-align: right; padding: 1.1rem;">TOTAL PENJUALAN:</td>
                    <td style="text-align: right; padding: 1.1rem; color: var(--primary); font-size: 1.1rem;">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
