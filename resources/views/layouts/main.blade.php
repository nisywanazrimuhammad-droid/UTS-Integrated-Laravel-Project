<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inventory & Sales Management System - UTS Integrated Laravel Project">
    <title>@yield('title', 'Dashboard') - InvenTrack ERP</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>
<div class="app-layout">
    <!-- Sidebar -->
    <aside class="app-sidebar" id="app-sidebar">
        <div class="sidebar-header">
            <a href="{{ route('dashboard') }}" class="brand-logo">
                <div class="brand-icon">📦</div>
                <span>InvenTrack <small style="font-size: 0.65rem; color: #818cf8; font-weight: 600;">UTS ERP</small></span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu Utama</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-label">Master Data</div>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" id="nav-products">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Produk</span>
            </a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" id="nav-categories">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span>Kategori</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" id="nav-suppliers">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Supplier</span>
            </a>

            <div class="nav-section-label">Transaksi Bisnis (UTS)</div>
            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" id="nav-purchases">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Pembelian (Stock-In)</span>
            </a>
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" id="nav-sales">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Penjualan (Sales)</span>
            </a>

            <div class="nav-section-label">Mutasi & Laporan</div>
            <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" id="nav-transactions">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span>Log Mutasi Stok</span>
            </a>
            <a href="{{ route('reports.inventory') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" id="nav-reports">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Laporan Agregat</span>
            </a>

            @if(auth()->user()->isAdmin())
            <div class="nav-section-label">Administrasi</div>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" id="nav-users">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Kelola Pengguna</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-mini-profile">
                <div class="avatar-circle">
                    {{ strtoupper(substr(auth()->user()->username ?? auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="user-mini-name">{{ auth()->user()->full_name }}</div>
                    <div class="user-mini-role">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;" title="Logout" id="logout-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="app-main">
        <header class="app-header">
            <div class="header-left">
                <button class="btn-icon-toggle" id="mobile-menu-btn" style="display: none;" title="Toggle Menu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="header-right">
                <span class="badge {{ auth()->user()->isAdmin() ? 'badge-primary' : 'badge-secondary' }}">
                    Role: {{ strtoupper(auth()->user()->role) }}
                </span>
                <button class="btn-icon-toggle" id="theme-toggle-btn" title="Toggle Tema"></button>
            </div>
        </header>

        <main class="app-content">
            @if(session('success'))
                <div class="alert alert-success" id="flash-alert">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="font-size: 1rem;">✕</button>
                </div>
            @endif

            @if(session('danger'))
                <div class="alert alert-danger" id="flash-alert">
                    <span>{{ session('danger') }}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="font-size: 1rem;">✕</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning" id="flash-alert">
                    <span>{{ session('warning') }}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="font-size: 1rem;">✕</button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info" id="flash-alert">
                    <span>{{ session('info') }}</span>
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="font-size: 1rem;">✕</button>
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

            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
