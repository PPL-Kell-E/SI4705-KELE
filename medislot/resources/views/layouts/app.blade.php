<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MEDISLOT')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f3;
            display: flex;
            min-height: 100vh;
            color: #333;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: #1a3c34;
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 20px 28px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 16px;
        }
        .sidebar-logo .logo-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .sidebar-logo .logo-icon img {
            width: 100%; height: 100%; object-fit: contain;
        }

        .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 2px; padding: 0 12px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: rgba(255,255,255,0.72);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.18s ease;
        }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .nav-item.active { background: #2d9e72; color: #fff; }
        .nav-item i { width: 18px; text-align: center; font-size: 14px; }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 10px;
            color: #e74c3c; font-size: 13.5px; font-weight: 500;
            text-decoration: none; transition: opacity 0.18s;
        }
        .sidebar-footer a:hover { opacity: 0.8; }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: 220px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOPBAR ── */
        .topbar {
            background: #fff;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e8eeec;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 20px; font-weight: 700; color: #1a3c34; }
        .topbar-subtitle { font-size: 13px; color: #7a9a90; margin-top: 2px; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .topbar-icon {
            width: 36px; height: 36px;
            background: #f0f4f3; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #5a7a70; font-size: 15px; cursor: pointer;
            transition: background 0.18s;
        }
        .topbar-icon:hover { background: #dbeee7; }
        .topbar-avatar {
            width: 36px; height: 36px; background: #2d9e72;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 14px; font-weight: 700; cursor: pointer;
        }

        /* ── PAGE BODY ── */
        .page-body { padding: 28px 32px; flex: 1; }

        @yield('extra-styles')
    </style>
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <img src="{{ asset('images/logo.svg') }}" alt="MediSlot Logo">
        </div>
        MEDISLOT
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="{{ route('pengingat.index') }}" class="nav-item {{ request()->routeIs('pengingat.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i> Pengingat
        </a>
        <a href="{{ route('rekomendasi.index') }}" class="nav-item {{ request()->routeIs('rekomendasi.*') ? 'active' : '' }}">
            <i class="fas fa-lightbulb"></i> Rekomendasi
        </a>
        <a href="{{ route('data-kesehatan.index') }}" class="nav-item {{ request()->routeIs('data-kesehatan.*') ? 'active' : '' }}">
            <i class="fas fa-heartbeat"></i> Data Kesehatan
        </a>
        <a href="{{ route('katalog.index') }}" class="nav-item {{ request()->routeIs('katalog.*') ? 'active' : '' }}">
            <i class="fas fa-book-medical"></i> Katalog Pemeriksaan
        </a>
        <a href="{{ route('jadwal.index') }}" class="nav-item {{ request()->routeIs('jadwal.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i> Jadwal Saya
        </a>
        <a href="{{ route('riwayat.index') }}" class="nav-item {{ request()->routeIs('riwayat.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Riwayat
        </a>
        <a href="{{ route('insight.index') }}" class="nav-item {{ request()->routeIs('insight.*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Insight
        </a>
        <a href="{{ route('hasil-pemeriksaan.index') }}" class="nav-item {{ request()->routeIs('hasil-pemeriksaan.*') ? 'active' : '' }}">
            <i class="fas fa-file-medical-alt"></i> Hasil Pemeriksaan
        </a>
        <a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="fas fa-user"></i> Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="background:none;border:none;cursor:pointer;width:100%;text-align:left;display:flex;align-items:center;gap:10px;color:#e74c3c;font-size:13.5px;font-weight:500;font-family:inherit;">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </button>
        </form>
    </div>
</aside>

{{-- MAIN --}}
<div class="main-content">
    {{-- TOPBAR --}}
    <header class="topbar">
        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-subtitle">@yield('page-subtitle', '')</div>
        </div>
        <div class="topbar-actions">
            @yield('topbar-actions')
            <div class="topbar-icon"><i class="fas fa-search"></i></div>
            <div class="topbar-icon"><i class="fas fa-bell"></i></div>
            <a href="{{ route('profile.show') }}" style="text-decoration:none;">
                <div class="topbar-avatar" style="{{ !empty($authProfile?->avatar_url) ? 'background:transparent;padding:0;overflow:hidden;' : '' }}">
                    @if(!empty($authProfile?->avatar_url))
                        <img src="{{ $authProfile->avatar_url }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;display:block;">
                    @else
                        {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
            </a>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <div class="page-body">
        @yield('content')
    </div>
</div>

@yield('scripts')
</body>
</html>
