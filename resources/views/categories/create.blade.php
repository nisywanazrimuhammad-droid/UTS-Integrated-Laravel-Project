@extends('layouts.main')

@section('title', 'Tambah Kategori')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('categories.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">← Kembali ke Daftar Kategori</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 1.25rem;">Tambah Kategori Baru</h2>
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name" class="form-label">Nama Kategori *</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
        </div>
    </form>
</div>
@endsection
