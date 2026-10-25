@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div style="display: flex; justify-content: center; margin-bottom: 0.5rem;">
            <div class="brand-icon" style="width: 52px; height: 52px; font-size: 1.6rem;">📦</div>
        </div>
        <h1 class="auth-title">InvenTrack <span style="color: #6366f1; font-size: 1.1rem;">ERP</span></h1>
        <p class="auth-subtitle">Inventory & Sales Management System (UTS Laravel Project)</p>
    </div>

    @if(session('danger'))
        <div class="alert alert-danger">
            <span>{{ session('danger') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" id="login-form">
        @csrf
        <div class="form-group">
            <label for="username" class="form-label">Username atau Email</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="admin / admin@example.com" value="{{ old('username') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem;" id="login-btn">
                Masuk ke Sistem
            </button>
        </div>
    </form>

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-subtle); font-size: 0.82rem; color: var(--text-muted);">
        <strong>Akun Demo (Database Seeder):</strong><br>
        • <strong>Admin/Owner:</strong> <code>admin</code> (atau <code>admin@example.com</code>) / <code>admin123</code><br>
        • <strong>Staff Operasional:</strong> <code>staff</code> (atau <code>staff@example.com</code>) / <code>staff123</code>
    </div>
</div>
@endsection
