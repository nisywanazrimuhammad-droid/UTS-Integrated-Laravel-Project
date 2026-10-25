@extends('layouts.main')

@section('title', 'Laporan Mutasi & Valuasi Inventory')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Laporan Agregat Inventory & Transaksi</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">
            Kalkulasi agregat stok, nilai aset (harga beli & jual), serta total pembelian dan penjualan.
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-secondary" onclick="window.print()" id="btn-print-report">
            🖨️ Cetak Laporan
        </button>
    </div>
</div>

<!-- Ringkasan Agregat -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-bottom: 1.5rem;">
    <div class="card stat-card cyan">
        <span class="stat-label">Valuasi Retail Inventory</span>
        <div class="stat-value" style="font-size: 1.35rem; color: var(--accent-cyan);">
            Rp {{ number_format($summary['total_val'], 0, ',', '.') }}
        </div>
        <div class="stat-subtext">Modal: Rp {{ number_format($summary['total_cost'], 0, ',', '.') }}</div>
    </div>
    <div class="card stat-card primary">
        <span class="stat-label">Total Unit Tersedia</span>
        <div class="stat-value">{{ number_format($summary['total_stock']) }}</div>
        <div class="stat-subtext">Masuk: +{{ number_format($summary['total_in']) }} | Keluar: -{{ number_format($summary['total_out']) }}</div>
    </div>
    <div class="card stat-card success">
        <span class="stat-label">Total Nilai Penjualan</span>
        <div class="stat-value" style="font-size: 1.35rem; color: #10b981;">
            Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}
        </div>
        <div class="stat-subtext">Transaksi Sales berstatus Completed</div>
    </div>
    <div class="card stat-card warning">
        <span class="stat-label">Total Nilai Pembelian</span>
        <div class="stat-value" style="font-size: 1.35rem; color: #f59e0b;">
            Rp {{ number_format($summary['total_purchases'], 0, ',', '.') }}
        </div>
        <div class="stat-subtext">Transaksi PO berstatus Completed</div>
    </div>
</div>

<!-- Filter Kategori -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form method="GET" action="{{ route('reports.inventory') }}" class="filter-bar" style="margin-bottom: 0;">
        <div class="filter-group">
            <label for="filter-cat" style="font-size: 0.88rem; font-weight: 600;">Filter Kategori:</label>
            <select name="category_id" id="filter-cat" class="form-select" style="min-width: 220px;" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($categoryId == $cat->id) ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @if (!empty($categoryId))
                <a href="{{ route('reports.inventory') }}" class="btn btn-secondary" style="color: #ef4444;">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Tabel Laporan Agregasi SQL -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="table-inventory-report">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Total IN</th>
                    <th>Total OUT</th>
                    <th>Stok Fisik</th>
                    <th>Nilai Aset (Jual)</th>
                </tr>
            </thead>
            <tbody>
                @if ($report->isEmpty())
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Tidak ada data untuk laporan inventory.
                        </td>
                    </tr>
                @else
                    @foreach ($report as $r)
                        <tr>
                            <td><code>{{ $r->sku }}</code></td>
                            <td>
                                <a href="{{ route('products.show', $r->id) }}" style="font-weight: 600;">
                                    {{ $r->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $r->category_name }}</span>
                            </td>
                            <td style="color: var(--text-muted);">Rp {{ number_format($r->purchase_price, 0, ',', '.') }}</td>
                            <td style="font-weight: 600;">Rp {{ number_format($r->selling_price, 0, ',', '.') }}</td>
                            <td>
                                <span style="color: #10b981; font-weight: 600;">+{{ number_format($r->total_in) }}</span>
                            </td>
                            <td>
                                <span style="color: #ef4444; font-weight: 600;">-{{ number_format($r->total_out) }}</span>
                            </td>
                            <td>
                                <strong style="font-size: 1.05rem;">{{ number_format($r->stock) }}</strong> unit
                                @if ($r->stock <= $r->minimum_stock)
                                    <span class="badge badge-warning" style="font-size: 0.68rem; margin-left: 0.35rem;">Low</span>
                                @endif
                            </td>
                            <td style="font-weight: 700; color: var(--primary);">
                                Rp {{ number_format($r->inventory_value, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr style="background: var(--bg-surface); font-weight: 800;">
                    <td colspan="5" style="text-align: right; padding: 1.25rem 1rem;">TOTAL KESELURUHAN:</td>
                    <td style="color: #10b981; padding: 1.25rem 1rem;">+{{ number_format($summary['total_in']) }}</td>
                    <td style="color: #ef4444; padding: 1.25rem 1rem;">-{{ number_format($summary['total_out']) }}</td>
                    <td style="padding: 1.25rem 1rem;">{{ number_format($summary['total_stock']) }} unit</td>
                    <td style="color: var(--accent-cyan); padding: 1.25rem 1rem; font-size: 1.05rem;">
                        Rp {{ number_format($summary['total_val'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
