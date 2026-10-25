@extends('layouts.main')

@section('title', 'Tambah Produk Baru')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('products.index') }}" style="color: var(--text-muted); font-size: 0.88rem; display: inline-flex; align-items: center; gap: 0.35rem;">
        ← Kembali ke Katalog Produk
    </a>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-subtle);">
        Tambah Produk Baru
    </h2>

    <form action="{{ route('products.store') }}" method="POST" id="form-create-product">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
            <!-- SKU -->
            <div class="form-group">
                <label for="sku" class="form-label">Kode SKU (Harus Unik) *</label>
                <input type="text" name="sku" id="sku" class="form-control" placeholder="Contoh: SKU-KB-011"
                       value="{{ old('sku') }}" required>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Kode SKU unik untuk identifikasi produk.</small>
            </div>

            <!-- Nama Produk -->
            <div class="form-group">
                <label for="name" class="form-label">Nama Produk *</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Nama lengkap produk"
                       value="{{ old('name') }}" required>
            </div>

            <!-- Kategori -->
            <div class="form-group">
                <label for="category_id" class="form-label">Kategori Produk *</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (old('category_id') == $cat->id) ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Harga Beli (Purchase Price) -->
            <div class="form-group">
                <label for="purchase_price" class="form-label">Harga Beli / Modal (Rp) *</label>
                <input type="number" step="500" min="0" name="purchase_price" id="purchase_price" class="form-control" placeholder="0"
                       value="{{ old('purchase_price') }}" required>
            </div>

            <!-- Harga Jual (Selling Price) -->
            <div class="form-group">
                <label for="selling_price" class="form-label">Harga Jual Retail (Rp) *</label>
                <input type="number" step="500" min="0" name="selling_price" id="selling_price" class="form-control" placeholder="0"
                       value="{{ old('selling_price') }}" required>
            </div>

            <!-- Stok Awal -->
            <div class="form-group">
                <label for="stock" class="form-label">Stok Awal</label>
                <input type="number" min="0" name="stock" id="stock" class="form-control" placeholder="0"
                       value="{{ old('stock', 0) }}">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Otomatis tercatat sebagai mutasi masuk awal.</small>
            </div>

            <!-- Stok Minimum -->
            <div class="form-group">
                <label for="minimum_stock" class="form-label">Batas Stok Minimum *</label>
                <input type="number" min="0" name="minimum_stock" id="minimum_stock" class="form-control" placeholder="5"
                       value="{{ old('minimum_stock', 5) }}" required>
                <small style="color: var(--text-muted); font-size: 0.75rem;">Muncul di peringatan Low Stock jika stok ≤ batas ini.</small>
            </div>
        </div>

        <!-- Bagian Many-to-Many: Pilihan Supplier & Harga Beli -->
        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-subtle);">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">
                Supplier Terkait (Opsional)
            </h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">
                Pilih satu atau beberapa supplier yang menyuplai produk ini beserta harga pasokannya.
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @forelse ($suppliers as $s)
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.75rem 1rem; background-color: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; flex: 1; min-width: 200px;">
                            <input type="checkbox" name="supplier_ids[]" value="{{ $s->id }}" {{ in_array($s->id, (array)old('supplier_ids', [])) ? 'checked' : '' }}>
                            <div>
                                <strong style="font-size: 0.92rem;">{{ $s->name }}</strong>
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $s->contact_person ?? 'Kontak tidak ada' }}</div>
                            </div>
                        </label>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 0.85rem; color: var(--text-muted);">Harga Pasok: Rp</span>
                            <input type="number" step="500" min="0" name="supplier_prices[{{ $s->id }}]" class="form-control"
                                   placeholder="Opsional" value="{{ old("supplier_prices.{$s->id}") }}"
                                   style="width: 140px; padding: 0.4rem 0.6rem; font-size: 0.85rem;">
                        </div>
                    </div>
                @empty
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Belum ada supplier terdaftar.</p>
                @endforelse
            </div>
        </div>

        <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn-save-product">Simpan Produk</button>
        </div>
    </form>
</div>
@endsection
