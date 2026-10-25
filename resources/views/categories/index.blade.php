@extends('layouts.main')

@section('title', 'Kategori Produk')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700;">Kategori Produk</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Klasifikasi produk dan statistik agregat stok per kategori.</p>
    </div>
    @if(auth()->user()->isAdmin())
    <div style="display: flex; gap: 0.75rem;">
        <button class="btn btn-primary" onclick="openModal('modal-add-category')" id="btn-add-category">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kategori
        </button>
    </div>
    @endif
</div>

<!-- Search Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.1rem;">
    <form method="GET" action="{{ route('categories.index') }}" class="filter-bar" style="margin-bottom: 0;">
        <div class="filter-group" style="flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama kategori..." value="{{ $search ?? '' }}">
        </div>
        <div class="filter-group">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(!empty($search))
                <a href="{{ route('categories.index') }}" class="btn btn-secondary" style="color: #ef4444;">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="table" id="table-categories">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Total Produk</th>
                    <th>Total Stok</th>
                    <th>Rata-rata Harga Jual</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($categories->isEmpty())
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Belum ada kategori yang ditemukan.
                        </td>
                    </tr>
                @else
                    @foreach ($categories as $cat)
                        <tr>
                            <td>#{{ $cat->id }}</td>
                            <td>
                                <a href="{{ route('categories.show', $cat) }}" style="font-weight: 700; color: var(--text-main);">
                                    {{ $cat->name }}
                                </a>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.88rem;">
                                {{ $cat->description ?? '-' }}
                            </td>
                            <td>
                                <span class="badge badge-primary">{{ $cat->products_count }} produk</span>
                            </td>
                            <td>
                                <strong>{{ number_format($cat->total_stock ?? 0) }}</strong> unit
                            </td>
                            <td style="font-weight: 600;">
                                Rp {{ number_format($cat->average_price ?? 0, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <a href="{{ route('categories.show', $cat) }}" class="btn btn-secondary btn-sm">Detail</a>
                                    @if(auth()->user()->isAdmin())
                                    <button class="btn btn-secondary btn-sm"
                                            onclick='openEditCategoryModal(@json($cat))'>
                                        Edit
                                    </button>
                                    <form action="{{ route('categories.destroy', $cat) }}" method="POST" style="display: inline;"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary btn-sm" style="color: #ef4444;">
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

@if ($categories->hasPages())
<div class="pagination">
    @if ($categories->onFirstPage())
        <span class="page-link disabled">« Prev</span>
    @else
        <a href="{{ $categories->previousPageUrl() }}" class="page-link">« Prev</a>
    @endif

    @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
        <a href="{{ $url }}" class="page-link {{ $page == $categories->currentPage() ? 'active' : '' }}">{{ $page }}</a>
    @endforeach

    @if ($categories->hasMorePages())
        <a href="{{ $categories->nextPageUrl() }}" class="page-link">Next »</a>
    @else
        <span class="page-link disabled">Next »</span>
    @endif
</div>
@endif

@if(auth()->user()->isAdmin())
<!-- Modal Tambah Kategori -->
<div class="modal-overlay" id="modal-add-category">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Kategori Baru</h3>
            <button type="button" class="btn-close" onclick="closeModal('modal-add-category')">✕</button>
        </div>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="cat-name" class="form-label">Nama Kategori *</label>
                    <input type="text" name="name" id="cat-name" class="form-control" placeholder="Contoh: Perangkat Audio" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="cat-desc" class="form-label">Deskripsi</label>
                    <textarea name="description" id="cat-desc" class="form-control" placeholder="Keterangan singkat kategori..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-category')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kategori -->
<div class="modal-overlay" id="modal-edit-category">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Edit Kategori</h3>
            <button type="button" class="btn-close" onclick="closeModal('modal-edit-category')">✕</button>
        </div>
        <form id="form-edit-cat" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit-cat-name" class="form-label">Nama Kategori *</label>
                    <input type="text" name="name" id="edit-cat-name" class="form-control" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="edit-cat-desc" class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-cat-desc" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-category')">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui Kategori</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCategoryModal(cat) {
    document.getElementById('form-edit-cat').action = '/categories/' + cat.id;
    document.getElementById('edit-cat-name').value = cat.name || '';
    document.getElementById('edit-cat-desc').value = cat.description || '';
    openModal('modal-edit-category');
}
</script>
@endif
@endsection
