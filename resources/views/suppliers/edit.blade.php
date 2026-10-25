@extends('layouts.main')

@section('title', 'Edit Supplier')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('suppliers.index') }}" style="color: var(--text-muted); font-size: 0.88rem;">← Kembali ke Daftar Supplier</a>
</div>

<div class="card" style="max-width: 640px; margin: 0 auto;">
    <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 1.25rem;">Edit Supplier: {{ $supplier->name }}</h2>
    <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Nama Supplier *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Kontak Person (PIC)</label>
            <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $supplier->contact_person) }}">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Alamat</label>
            <textarea name="address" class="form-control">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Perbarui Supplier</button>
        </div>
    </form>
</div>
@endsection
