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
        .nav-item .nav-arrow { margin-left: auto; font-size: 10px; transition: transform 0.2s; }
        .nav-item.open .nav-arrow { transform: rotate(180deg); }
        .nav-submenu { display: none; padding-left: 40px; margin-top: 2px; }
        .nav-submenu.open { display: flex; flex-direction: column; gap: 2px; }
        .nav-sub-item {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 12px; color: rgba(255,255,255,0.6);
            text-decoration: none; font-size: 12.5px; font-weight: 500;
            border-radius: 8px; transition: all 0.18s ease;
        }
        .nav-sub-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-sub-item.active { color: #fff; }
        .nav-sub-item .dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: rgba(255,255,255,0.35); flex-shrink: 0;
        }
        .nav-sub-item.active .dot { background: #fff; }

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

        /* ── Notification Bell ── */
        .notif-bell-wrap { position: relative; }
        .notif-badge {
            display: none; position: absolute; top: -4px; right: -4px;
            min-width: 18px; height: 18px; border-radius: 999px;
            background: #e05252; color: #fff;
            font-size: 10px; font-weight: 700;
            align-items: center; justify-content: center; padding: 0 4px;
        }
        .notif-badge.show { display: flex; }
        .notif-dropdown {
            display: none; position: absolute; top: calc(100% + 10px); right: 0;
            width: 340px; background: #fff; border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.14); z-index: 200; overflow: hidden;
        }
        .notif-dropdown.open { display: block; }
        .notif-drop-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px 10px; border-bottom: 1px solid #eef2f1;
        }
        .notif-drop-head h4 { font-size: 14px; font-weight: 700; color: #1a3c34; }
        .notif-drop-read-all {
            font-size: 12px; color: #2d9e72; font-weight: 600;
            background: none; border: none; cursor: pointer; font-family: inherit;
        }
        .notif-drop-list { max-height: 320px; overflow-y: auto; }
        .notif-drop-item {
            padding: 12px 18px; border-bottom: 1px solid #f5f8f7;
            cursor: pointer; transition: background 0.12s;
        }
        .notif-drop-item:hover { background: #f8fbfa; }
        .notif-drop-item.read { opacity: 0.55; }
        .notif-drop-item-head { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .notif-drop-title { font-size: 13px; font-weight: 700; color: #1a3c34; }
        .notif-drop-time  { font-size: 11px; color: #7a9a90; }
        .notif-drop-pesan { font-size: 12.5px; color: #5a7a70; line-height: 1.4; }
        .notif-drop-empty { padding: 28px; text-align: center; color: #7a9a90; font-size: 13px; }
        .notif-drop-empty i { font-size: 28px; color: #c8ddd7; display: block; margin-bottom: 8px; }

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
        @php $insightOpen = request()->routeIs('insight.*') || request()->routeIs('target-kesehatan.*'); @endphp
        <a href="#" class="nav-item {{ $insightOpen ? 'open' : '' }}" onclick="toggleInsightMenu(event)">
            <i class="fas fa-lightbulb"></i> Insight
            <i class="fas fa-chevron-down nav-arrow"></i>
        </a>
        <div class="nav-submenu {{ $insightOpen ? 'open' : '' }}" id="insight-submenu">
            <a href="{{ route('insight.progress') }}" class="nav-sub-item {{ request()->routeIs('insight.progress') ? 'active' : '' }}">
                <span class="dot"></span> Progress
            </a>
            <a href="{{ route('insight.index') }}" class="nav-sub-item {{ request()->routeIs('insight.index') ? 'active' : '' }}">
                <span class="dot"></span> Insight &amp; Pencapaian
            </a>
            <a href="{{ route('target-kesehatan.index') }}" class="nav-sub-item {{ request()->routeIs('target-kesehatan.*') ? 'active' : '' }}">
                <span class="dot"></span> Target Kesehatan
            </a>
        </div>
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
            {{-- Bell with notification badge & dropdown --}}
            <div class="notif-bell-wrap" id="notifBellWrap">
                <div class="topbar-icon" onclick="toggleNotifDropdown()" id="notifBellBtn">
                    <i class="fas fa-bell"></i>
                </div>
                <span class="notif-badge" id="notifBadge">0</span>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-drop-head">
                        <h4>Notifikasi</h4>
                        <button class="notif-drop-read-all" onclick="markAllRead()">Baca Semua</button>
                    </div>
                    <div class="notif-drop-list" id="notifDropList">
                        <div class="notif-drop-empty">
                            <i class="fas fa-bell-slash"></i>
                            Tidak ada notifikasi baru.
                        </div>
                    </div>
                </div>
            </div>
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
<script>
function toggleInsightMenu(e) {
    e.preventDefault();
    const btn = e.currentTarget;
    const sub = document.getElementById('insight-submenu');
    btn.classList.toggle('open');
    sub.classList.toggle('open');
}

// ── Notification Bell ──
const _CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function toggleNotifDropdown() {
    const dd = document.getElementById('notifDropdown');
    dd.classList.toggle('open');
    if (dd.classList.contains('open')) fetchNotifikasi();
}

document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notifBellWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('notifDropdown').classList.remove('open');
    }
});

function fetchNotifikasi() {
    fetch('/notifikasi/check', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notifBadge');
            const list  = document.getElementById('notifDropList');

            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.classList.add('show');
            } else {
                badge.classList.remove('show');
            }

            if (!data.notifikasi || data.notifikasi.length === 0) {
                list.innerHTML = '<div class="notif-drop-empty"><i class="fas fa-bell-slash"></i>Tidak ada notifikasi baru.</div>';
                return;
            }

            list.innerHTML = data.notifikasi.map(n => `
                <div class="notif-drop-item" onclick="readNotifDrop(${n.id}, ${n.jadwal_id ?? 'null'}, this)">
                    <div class="notif-drop-item-head">
                        <span class="notif-drop-title">${n.judul}</span>
                        <span class="notif-drop-time">${n.waktu}</span>
                    </div>
                    <div class="notif-drop-pesan">${n.pesan}</div>
                </div>`).join('');
        })
        .catch(() => {});
}

function readNotifDrop(id, jadwalId, el) {
    fetch(`/notifikasi/${id}/read`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json' }
    });
    el.classList.add('read');
    if (jadwalId) window.location.href = '/jadwal';
}

function markAllRead() {
    fetch('/notifikasi/read-all', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': _CSRF, 'Accept': 'application/json' }
    }).then(() => {
        document.getElementById('notifBadge').classList.remove('show');
        document.querySelectorAll('.notif-drop-item').forEach(el => el.classList.add('read'));
    });
}

// Poll setiap 60 detik
fetchNotifikasi();
setInterval(fetchNotifikasi, 60000);
</script>
</body>
</html>
