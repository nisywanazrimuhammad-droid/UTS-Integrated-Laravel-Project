@extends('layouts.main')

@section('title', 'Catat Barang Masuk (Stock In)')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('transactions.index') }}" style="color: var(--text-muted); font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        ← Kembali ke Riwayat Mutasi
    </a>
</div>

<div class="card" style="max-width: 680px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-subtle);">
        <div class="stat-icon-wrapper success" style="width: 44px; height: 44px;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        </div>
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 700;">Catat Barang Masuk Cepat (Stock In)</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Menambah kuantitas stok 1 produk ke gudang secara konsisten.</p>
        </div>
    </div>

    <form action="{{ route('transactions.in') }}" method="POST" id="form-stock-in">
        @csrf
        <div class="form-group">
            <label for="product_id" class="form-label">Pilih Produk *</label>
            <select name="product_id" id="product_id" class="form-select" required onchange="updateProductStockDisplay(this)">
                <option value="">-- Pilih Produk Tujuan --</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" data-sku="{{ $p->sku }}"
                            {{ (old('product_id', $selectedProductId) == $p->id) ? 'selected' : '' }}>
                        [{{ $p->sku }}] {{ $p->name }} (Sisa: {{ $p->stock }} unit)
                    </option>
                @endforeach
            </select>
        </div>

        <div id="product-info-box" style="display: none; padding: 0.75rem 1rem; background: var(--bg-body); border-radius: var(--radius-sm); margin-bottom: 1.25rem; font-size: 0.88rem;">
            Stok saat ini di gudang: <strong id="current-stock-label">0</strong> unit.
        </div>

        <div class="form-group">
            <label for="quantity" class="form-label">Jumlah Barang Masuk (Quantity) *</label>
            <input type="number" min="1" name="quantity" id="quantity" class="form-control" placeholder="1" value="{{ old('quantity', 1) }}" required autofocus>
            <small style="color: var(--text-muted); font-size: 0.75rem;">Nilai harus bilangan bulat positif lebih besar dari nol.</small>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Catatan / Referensi</label>
            <textarea name="notes" id="notes" class="form-control" placeholder="Contoh: Penyesuaian stok masuk gudang...">{{ old('notes') }}</textarea>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-success" id="btn-submit-in">Simpan Barang Masuk</button>
        </div>
    </form>
</div>

<script>
function updateProductStockDisplay(selectElem) {
    const selectedOpt = selectElem.options[selectElem.selectedIndex];
    const infoBox = document.getElementById('product-info-box');
    const label = document.getElementById('current-stock-label');
    if (selectedOpt && selectedOpt.value) {
        label.textContent = selectedOpt.getAttribute('data-stock');
        infoBox.style.display = 'block';
    } else {
        infoBox.style.display = 'none';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('product_id');
    if (sel && sel.value) updateProductStockDisplay(sel);
});
</script>
@endsection
