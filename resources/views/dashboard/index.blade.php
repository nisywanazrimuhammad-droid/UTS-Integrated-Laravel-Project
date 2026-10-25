@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<!-- Quick Action Banner -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 800;">Ringkasan Operasional & Penjualan</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Selamat datang, <strong>{{ auth()->user()->full_name }}</strong>. Pantau stok produk, pembelian (stock-in), dan transaksi penjualan (sales) secara real-time.
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="{{ route('purchases.create') }}" class="btn btn-success" id="btn-quick-purchase">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Transaksi Pembelian (Stock-In)
        </a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary" id="btn-quick-sale">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"></path></svg>
            + Transaksi Penjualan (Sales)
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('products.create') }}" class="btn btn-secondary" id="btn-quick-add-product">
            + Produk Baru
        </a>
        @endif
    </div>
</div>

<!-- Baris 1: Widget Statistik Utama (UTS Slide 6 & 18) -->
<div class="stats-grid">
    <!-- 1. Total Produk -->
    <div class="card stat-card primary" id="stat-total-products">
        <div class="stat-header">
            <span class="stat-label">Total Produk</span>
            <div class="stat-icon-wrapper primary">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>
        <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
        <div class="stat-subtext">{{ $stats['total_categories'] }} kategori & {{ $stats['total_suppliers'] }} supplier</div>
    </div>

    <!-- 2. Total Penjualan (Sales) Bulan Ini -->
    <div class="card stat-card success" id="stat-sales-month">
        <div class="stat-header">
            <span class="stat-label">Penjualan Bulan Ini</span>
            <div class="stat-icon-wrapper success">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="font-size: 1.45rem; color: #10b981;">Rp {{ number_format($stats['sales_this_month'], 0, ',', '.') }}</div>
        <div class="stat-subtext">Hari ini: Rp {{ number_format($stats['sales_today'], 0, ',', '.') }} ({{ $stats['total_sales_count'] }} invoice)</div>
    </div>

    <!-- 3. Total Pembelian (Purchases / Stock-In) -->
    <div class="card stat-card cyan" id="stat-purchases">
        <div class="stat-header">
            <span class="stat-label">Total Pembelian Stok</span>
            <div class="stat-icon-wrapper cyan">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="font-size: 1.45rem; color: var(--accent-cyan);">Rp {{ number_format($stats['total_purchases_amount'], 0, ',', '.') }}</div>
        <div class="stat-subtext">Dari {{ $stats['total_purchases_count'] }} transaksi PO selesai</div>
    </div>

    <!-- 4. Valuasi Nilai Inventory -->
    <div class="card stat-card primary" id="stat-inventory-value">
        <div class="stat-header">
            <span class="stat-label">Valuasi Inventory</span>
            <div class="stat-icon-wrapper primary">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="font-size: 1.45rem;">Rp {{ number_format($stats['total_inventory_value'], 0, ',', '.') }}</div>
        <div class="stat-subtext">Masuk: +{{ number_format($stats['total_in']) }} | Keluar: -{{ number_format($stats['total_out']) }} unit</div>
    </div>

    <!-- 5. Produk Stok Rendah -->
    <div class="card stat-card warning" id="stat-low-stock">
        <div class="stat-header">
            <span class="stat-label">Stok Rendah (Alert)</span>
            <div class="stat-icon-wrapper warning">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
        <div class="stat-value" style="color: #f59e0b;">{{ number_format($stats['low_stock_count']) }}</div>
        <div class="stat-subtext">Produk dengan stok ≤ batas minimum</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Widget Peringatan Stok Rendah -->
    <div class="card" id="card-low-stock-alert">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <span style="color: #f59e0b;">⚠️</span> Produk Perlu Re-Stock (Low Stock)
            </h3>
            <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>

        @if($stats['low_stock_products']->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">Semua stok produk berada di atas batas minimum.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Sisa Stok</th>
                            <th>Min Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['low_stock_products'] as $lp)
                            <tr>
                                <td><code>{{ $lp->sku }}</code></td>
                                <td style="font-weight: 600;">
                                    <a href="{{ route('products.show', $lp) }}">{{ $lp->name }}</a>
                                </td>
                                <td>
                                    @if ($lp->stock <= 0)
                                        <span class="badge badge-danger">Habis (0)</span>
                                    @else
                                        <span class="badge badge-warning">{{ $lp->stock }} unit</span>
                                    @endif
                                </td>
                                <td style="color: var(--text-muted);">{{ $lp->minimum_stock }}</td>
                                <td>
                                    <a href="{{ route('purchases.create', ['product_id' => $lp->id]) }}" class="btn btn-success btn-sm">+ Beli Stok</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Transaksi Penjualan (Sales) Terbaru -->
    <div class="card" id="card-recent-sales">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <span>🧾</span> Transaksi Penjualan Terbaru
            </h3>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">Semua Penjualan</a>
        </div>

        @if($stats['recent_sales']->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">Belum ada transaksi penjualan.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['recent_sales'] as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale) }}" style="font-weight: 700;">
                                        <code>{{ $sale->sale_number }}</code>
                                    </a>
                                </td>
                                <td style="font-size: 0.82rem; color: var(--text-muted);">
                                    {{ $sale->sold_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="font-size: 0.88rem;">{{ $sale->customer_name ?? 'Umum' }}</td>
                                <td style="font-weight: 700;">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                <td>
                                    @if($sale->status === 'completed')
                                        <span class="badge badge-success">Selesai</span>
                                    @else
                                        <span class="badge badge-danger">Dibatalkan</span>
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

<!-- Baris 3: Pembelian Terbaru & Log Mutasi -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 1.5rem;">
    <!-- Transaksi Pembelian (Purchases) Terbaru -->
    <div class="card" id="card-recent-purchases">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <span>🚚</span> Pembelian Stok (Stock-In) Terbaru
            </h3>
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">Semua Pembelian</a>
        </div>

        @if($stats['recent_purchases']->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">Belum ada transaksi pembelian stok.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. PO</th>
                            <th>Supplier</th>
                            <th>Item</th>
                            <th>Total Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['recent_purchases'] as $po)
                            <tr>
                                <td>
                                    <a href="{{ route('purchases.show', $po) }}" style="font-weight: 700;">
                                        <code>{{ $po->purchase_number }}</code>
                                    </a>
                                </td>
                                <td style="font-size: 0.88rem;">{{ $po->supplier->name ?? '-' }}</td>
                                <td><span class="badge badge-primary">{{ $po->items_count }} item</span></td>
                                <td style="font-weight: 700;">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                                <td>
                                    @if($po->status === 'completed')
                                        <span class="badge badge-success">Selesai</span>
                                    @else
                                        <span class="badge badge-danger">Dibatalkan</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Mutasi Stok Terakhir -->
    <div class="card" id="card-recent-movements">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                <span>⏱️</span> Log Mutasi Stok Terbaru
            </h3>
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">Semua Riwayat</a>
        </div>

        @if($stats['recent_movements']->isEmpty())
            <p style="color: var(--text-muted); font-size: 0.9rem; padding: 1rem 0;">Belum ada riwayat transaksi mutasi barang.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stats['recent_movements'] as $rm)
                            <tr>
                                <td style="font-size: 0.82rem; color: var(--text-muted); white-space: nowrap;">
                                    {{ $rm->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    @if ($rm->type === 'IN')
                                        <span class="badge badge-success">+ MASUK</span>
                                    @else
                                        <span class="badge badge-danger">- KELUAR</span>
                                    @endif
                                </td>
                                <td style="font-weight: 600;">{{ $rm->product->name ?? '-' }}</td>
                                <td><strong>{{ $rm->quantity }}</strong></td>
                                <td style="font-size: 0.85rem;">{{ $rm->user->full_name ?? $rm->user->username }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
