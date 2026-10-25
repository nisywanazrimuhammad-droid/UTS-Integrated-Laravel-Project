@extends('layouts.main')

@section('title', 'Buat Transaksi Pembelian (Stock-In)')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('purchases.index') }}" style="color: var(--text-muted); font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        ← Kembali ke Daftar Pembelian
    </a>
</div>

<div class="card" style="max-width: 1000px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-subtle);">
        <div class="stat-icon-wrapper success" style="width: 44px; height: 44px;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 700;">Input Transaksi Pembelian Baru (Multi-Item Stock-In)</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Pilih pemasok dan tambahkan satu atau beberapa produk. Stok produk otomatis bertambah setelah transaksi disimpan.</p>
        </div>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="form-purchase">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="supplier_id" class="form-label">Pilih Supplier (Pemasok) *</label>
                <select name="supplier_id" id="supplier_id" class="form-select" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach ($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ (old('supplier_id', $selectedSupplierId) == $sup->id) ? 'selected' : '' }}>
                            {{ $sup->name }} ({{ $sup->phone ?? 'Tanpa Telp' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="notes" class="form-label">Catatan / No. Surat Jalan Supplier</label>
                <input type="text" name="notes" id="notes" class="form-control" placeholder="Contoh: Pengiriman Batch Awal Gudang..." value="{{ old('notes') }}">
            </div>
        </div>

        <!-- Daftar Item Barang -->
        <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.05rem; font-weight: 700;">Daftar Item Barang yang Dibeli *</h3>
            <button type="button" class="btn btn-secondary btn-sm" onclick="addPurchaseRow()" id="btn-add-row">
                + Tambah Baris Produk
            </button>
        </div>

        <div class="table-responsive" style="border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
            <table class="table" id="purchase-items-table">
                <thead>
                    <tr>
                        <th style="width: 42%;">Produk</th>
                        <th style="width: 16%;">Kuantitas</th>
                        <th style="width: 22%;">Harga Beli Satuan (Rp)</th>
                        <th style="width: 15%;">Subtotal (Est.)</th>
                        <th style="width: 5%; text-align: center;">Hapus</th>
                    </tr>
                </thead>
                <tbody id="purchase-items-body">
                    <tr class="item-row">
                        <td>
                            <select name="items[0][product_id]" class="form-select product-select" required onchange="onPurchaseProductChange(this)">
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}"
                                            data-cost="{{ (float)$p->purchase_price }}"
                                            data-stock="{{ $p->stock }}"
                                            {{ ($selectedProductId == $p->id) ? 'selected' : '' }}>
                                        [{{ $p->sku }}] {{ $p->name }} (Stok: {{ $p->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="items[0][quantity]" class="form-control qty-input" min="1" value="1" required oninput="recalcPurchaseTotal()">
                        </td>
                        <td>
                            <input type="number" step="100" min="0" name="items[0][unit_cost]" class="form-control cost-input" placeholder="0" required oninput="recalcPurchaseTotal()">
                        </td>
                        <td class="row-subtotal" style="font-weight: 700; color: var(--success);">
                            Rp 0
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-secondary btn-sm" style="color: #ef4444;" onclick="removePurchaseRow(this)" title="Hapus Baris">✕</button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: var(--bg-surface);">
                        <td colspan="3" style="text-align: right; font-weight: 700; font-size: 1rem;">ESTIMASI TOTAL PEMBELIAN (Dihitung Ulang oleh Server):</td>
                        <td colspan="2" id="grand-total-display" style="font-weight: 800; font-size: 1.15rem; color: var(--success);">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success" id="btn-submit-purchase">Simpan Transaksi Pembelian</button>
        </div>
    </form>
</div>

<script>
let purchaseRowIdx = 1;

const productOptionsHtml = `
    <option value="">-- Pilih Produk --</option>
    @foreach ($products as $p)
        <option value="{{ $p->id }}" data-cost="{{ (float)$p->purchase_price }}" data-stock="{{ $p->stock }}">
            [{{ $p->sku }}] {{ addslashes($p->name) }} (Stok: {{ $p->stock }})
        </option>
    @endforeach
`;

function onPurchaseProductChange(selectEl) {
    const row = selectEl.closest('tr');
    const opt = selectEl.options[selectEl.selectedIndex];
    const costInput = row.querySelector('.cost-input');
    if (opt && opt.value) {
        costInput.value = opt.getAttribute('data-cost') || 0;
    } else {
        costInput.value = '';
    }
    recalcPurchaseTotal();
}

function addPurchaseRow() {
    const tbody = document.getElementById('purchase-items-body');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td>
            <select name="items[${purchaseRowIdx}][product_id]" class="form-select product-select" required onchange="onPurchaseProductChange(this)">
                ${productOptionsHtml}
            </select>
        </td>
        <td>
            <input type="number" name="items[${purchaseRowIdx}][quantity]" class="form-control qty-input" min="1" value="1" required oninput="recalcPurchaseTotal()">
        </td>
        <td>
            <input type="number" step="100" min="0" name="items[${purchaseRowIdx}][unit_cost]" class="form-control cost-input" placeholder="0" required oninput="recalcPurchaseTotal()">
        </td>
        <td class="row-subtotal" style="font-weight: 700; color: var(--success);">Rp 0</td>
        <td style="text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" style="color: #ef4444;" onclick="removePurchaseRow(this)">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
    purchaseRowIdx++;
}

function removePurchaseRow(btn) {
    const tbody = document.getElementById('purchase-items-body');
    if (tbody.querySelectorAll('tr').length <= 1) {
        alert('Minimal harus ada 1 baris produk dalam transaksi.');
        return;
    }
    btn.closest('tr').remove();
    recalcPurchaseTotal();
}

function formatRupiahJs(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function recalcPurchaseTotal() {
    let grandTotal = 0;
    document.querySelectorAll('#purchase-items-body tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input')?.value) || 0;
        const sub = qty * cost;
        grandTotal += sub;
        const subCell = row.querySelector('.row-subtotal');
        if (subCell) subCell.textContent = formatRupiahJs(sub);
    });
    document.getElementById('grand-total-display').textContent = formatRupiahJs(grandTotal);
}

document.addEventListener('DOMContentLoaded', () => {
    const firstSelect = document.querySelector('.product-select');
    if (firstSelect && firstSelect.value) {
        onPurchaseProductChange(firstSelect);
    }
});
</script>
@endsection
