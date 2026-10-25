@extends('layouts.main')

@section('title', 'Tambah Supplier')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('suppliers.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">← Kembali ke Daftar Supplier</a>
</div>

<div class="card" style="max-width: 640px; margin: 0 auto;">
    <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 1.25rem;">Tambah Supplier Baru</h2>
    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Nama Supplier *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Kontak Person (PIC)</label>
            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person') }}">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Supplier</button>
        </div>
    </form>
</div>
@endsection
