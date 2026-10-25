@extends('layouts.main')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="card" style="max-width: 600px; margin: 3rem auto; text-align: center; padding: 3rem;">
    <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
    <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">404 - Halaman Tidak Ditemukan</h2>
    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
        Halaman atau data transaksi yang Anda tuju tidak ditemukan.
    </p>
    <div>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
    </div>
</div>
@endsection
