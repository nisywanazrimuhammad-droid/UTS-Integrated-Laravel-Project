@extends('layouts.main')

@section('title', 'Detail Pembelian #' . $purchase->purchase_number)

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <a href="{{ route('purchases.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">
        ← Kembali ke Daftar Pembelian
    </a>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-secondary btn-sm" onclick="window.print()">🖨️ Cetak Bukti PO</button>
        @if(auth()->user()->isAdmin() && $purchase->status === 'completed')
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST"
                  onsubmit="return confirm('Batalkan transaksi pembelian ini? Stok produk yang masuk akan dikurangi kembali secara konsisten.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">Batalkan Transaksi (Rollback Stok)</button>
            </form>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle);">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span class="badge badge-primary">PURCHASE ORDER (STOCK-IN)</span>
                @if($purchase->status === 'completed')
                    <span class="badge badge-success">COMPLETED</span>
                @else
                    <span class="badge badge-danger">CANCELLED</span>
                @endif
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800;"><code>{{ $purchase->purchase_number }}</code></h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-top: 0.25rem;">
                Tanggal Transaksi: <strong>{{ $purchase->purchased_at->format('d F Y, H:i') }}</strong>
            </p>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Total Nilai Pembelian</div>
            <div style="font-size: 1.75rem; font-weight: 800; color: var(--success);">
                Rp {{ number_format($purchase->total, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Supplier (Pemasok)</div>
            <div style="font-weight: 700; font-size: 1rem; margin-top: 0.2rem;">
                <a href="{{ route('suppliers.show', $purchase->supplier_id) }}">{{ $purchase->supplier->name ?? '-' }}</a>
            </div>
            <div style="font-size: 0.84rem; color: var(--text-muted);">
                PIC: {{ $purchase->supplier->contact_person ?? '-' }} ({{ $purchase->supplier->phone ?? '-' }})
            </div>
        </div>

        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Petugas Pencatat</div>
            <div style="font-weight: 700; font-size: 1rem; margin-top: 0.2rem;">
                {{ $purchase->user->full_name ?? $purchase->user->username }}
            </div>
            <div style="font-size: 0.84rem; color: var(--text-muted);">Role: {{ strtoupper($purchase->user->role ?? 'staff') }}</div>
        </div>

        <div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase;">Catatan</div>
            <div style="font-size: 0.9rem; margin-top: 0.2rem;">{{ $purchase->notes ?: '-' }}</div>
        </div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-subtle);">
        <h3 style="font-size: 1.1rem; font-weight: 700;">Rincian Item Pembelian ({{ $purchase->items->count() }} Baris)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Kuantitas Masuk</th>
                    <th>Harga Beli Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $idx => $item)
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
                        <td><strong>+{{ $item->quantity }}</strong> unit</td>
                        <td>Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: var(--bg-surface); font-weight: 800;">
                    <td colspan="6" style="text-align: right; padding: 1.1rem;">TOTAL PEMBELIAN:</td>
                    <td style="text-align: right; padding: 1.1rem; color: var(--success); font-size: 1.1rem;">
                        Rp {{ number_format($purchase->total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
