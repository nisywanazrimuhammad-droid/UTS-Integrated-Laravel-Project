@extends('layouts.main')

@section('title', 'Buat Transaksi Penjualan (Sales)')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('sales.index') }}" style="color: var(--text-muted); font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        ← Kembali ke Daftar Penjualan
    </a>
</div>

<div class="card" style="max-width: 1000px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-subtle);">
        <div class="stat-icon-wrapper primary" style="width: 44px; height: 44px;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"></path></svg>
        </div>
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 700;">Input Transaksi Penjualan Baru (Multi-Item Sales / Stock-Out)</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">
                Harga jual diambil resmi dari database server. Jika ada salah satu produk yang stoknya tidak mencukupi, seluruh transaksi otomatis dibatalkan (Rollback).
            </p>
        </div>
    </div>

    <form action="{{ route('sales.store') }}" method="POST" id="form-sale">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="customer_name" class="form-label">Nama Pelanggan / Instansi</label>
                <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Pelanggan Umum / Nama PT..." value="{{ old('customer_name') }}">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="notes" class="form-label">Catatan Penjualan</label>
                <input type="text" name="notes" id="notes" class="form-control" placeholder="Contoh: Pembayaran Tunai / Transfer..." value="{{ old('notes') }}">
            </div>
        </div>

        <!-- Daftar Item Penjualan -->
        <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.05rem; font-weight: 700;">Daftar Barang yang Dijual *</h3>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addSaleRow()" id="btn-add-sale-row">
                + Tambah Baris Produk
            </button>
        </div>

        <div class="table-responsive" style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
            <table class="table" id="sale-items-table">
                <thead>
                    <tr>
                        <th style="width: 44%;">Produk & Sisa Stok</th>
                        <th style="width: 18%;">Harga Jual (Database)</th>
                        <th style="width: 16%;">Kuantitas Beli</th>
                        <th style="width: 17%;">Subtotal</th>
                        <th style="width: 5%; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="sale-items-body">
                    <tr class="item-row">
                        <td>
                            <select name="items[0][product_id]" class="form-select sale-product-select" required onchange="onSaleProductChange(this)">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}"
                                            data-price="{{ (float)$p->selling_price }}"
                                            data-stock="{{ $p->stock }}"
                                            {{ ($selectedProductId == $p->id) ? 'selected' : '' }}>
                                        [{{ $p->sku }}] {{ $p->name }} — Sisa Stok: {{ $p->stock }} unit
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="row-price" style="font-weight: 600; color: var(--text-muted);">
                            Rp 0
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control sale-qty-input" min="1" value="1" required oninput="recalcSaleTotal()">
                        </td>
                        <td class="row-subtotal" style="font-weight: 700; color: var(--primary);">
                            Rp 0
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-secondary btn-sm" style="color: #ef4444;" onclick="removeSaleRow(this)" title="Hapus Baris">✕</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--bg-surface);">
                        <td colspan="3" style="text-align: right; font-weight: 700; font-size: 1rem;">TOTAL PENJUALAN (Dihitung Server):</td>
                        <td colspan="2" id="sale-grand-total" style="font-weight: 800; font-size: 1.15rem; color: var(--primary);">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn-submit-sale">Proses & Simpan Penjualan</button>
        </div>
    </form>
</div>

<script>
let saleRowIdx = 1;

const saleProductOptionsHtml = `
    <option value="">-- Pilih Produk --</option>
    @foreach ($products as $p)
        <option value="{{ $p->id }}" data-price="{{ (float)$p->selling_price }}" data-stock="{{ $p->stock }}">
            [{{ $p->sku }}] {{ addslashes($p->name) }} — Sisa Stok: {{ $p->stock }} unit
        </option>
    @endforeach
`;

function formatRupiahSale(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function onSaleProductChange(selectEl) {
    const row = selectEl.closest('tr');
    const opt = selectEl.options[selectEl.selectedIndex];
    const priceCell = row.querySelector('.row-price');
    const price = opt && opt.value ? (parseFloat(opt.getAttribute('data-price')) || 0) : 0;
    priceCell.textContent = formatRupiahSale(price);
    priceCell.setAttribute('data-raw-price', price);
    recalcSaleTotal();
}

function addSaleRow() {
    const tbody = document.getElementById('sale-items-body');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td>
            <select name="items[${saleRowIdx}][product_id]" class="form-select sale-product-select" required onchange="onSaleProductChange(this)">
                ${saleProductOptionsHtml}
            </select>
        </td>
        <td class="row-price" data-raw-price="0" style="font-weight: 600; color: var(--text-muted);">Rp 0</td>
        <td>
            <input type="number" name="items[${saleRowIdx}][quantity]" class="form-control sale-qty-input" min="1" value="1" required oninput="recalcSaleTotal()">
        </td>
        <td class="row-subtotal" style="font-weight: 700; color: var(--primary);">Rp 0</td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" style="color: #ef4444;" onclick="removeSaleRow(this)">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
    saleRowIdx++;
}

function removeSaleRow(btn) {
    const tbody = document.getElementById('sale-items-body');
    if (tbody.querySelectorAll('tr').length <= 1) {
        alert('Minimal harus ada 1 baris produk untuk dijual.');
        return;
    }
    btn.closest('tr').remove();
    recalcSaleTotal();
}

function recalcSaleTotal() {
    let grandTotal = 0;
    document.querySelectorAll('#sale-items-body tr').forEach(row => {
        const selectEl = row.querySelector('.sale-product-select');
        const opt = selectEl ? selectEl.options[selectEl.selectedIndex] : null;
        const price = opt && opt.value ? (parseFloat(opt.getAttribute('data-price')) || 0) : 0;
        const qty = parseFloat(row.querySelector('.sale-qty-input')?.value) || 0;
        const sub = price * qty;
        grandTotal += sub;
        const subCell = row.querySelector('.row-subtotal');
        if (subCell) subCell.textContent = formatRupiahSale(sub);
    });
    document.getElementById('sale-grand-total').textContent = formatRupiahSale(grandTotal);
}

document.addEventListener('DOMContentLoaded', () => {
    const firstSelect = document.querySelector('.sale-product-select');
    if (firstSelect && firstSelect.value) {
        onSaleProductChange(firstSelect);
    }
});
</script>
@endsection
