@extends('layouts.admin')

@section('title', 'Dashboard Admin - MEDISLOT')
@section('page-title', 'Hello, ' . (Auth::user()->full_name ?? 'Admin'))
@section('page-subtitle', 'Selamat datang di dashboard admin Medislot')

@section('extra-styles')
<style>
    /* ── Stat Cards ── */
    .stat-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: box-shadow 0.18s;
    }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.09); }
    .stat-icon-wrap {
        width: 60px; height: 60px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; flex-shrink: 0;
    }
    .stat-info { flex: 1; min-width: 0; }
    .stat-value {
        font-size: 32px; font-weight: 800;
        color: #2d9e72; line-height: 1; margin-bottom: 6px;
    }
    .stat-label { font-size: 13px; font-weight: 600; color: #1a3c34; margin-bottom: 2px; }
    .stat-sub   { font-size: 12px; color: #7a9a90; }

    /* ── Two-column ── */
    .dash-grid {
        display: grid;
        grid-template-columns: 1fr 460px;
        gap: 20px;
        align-items: start;
    }

    .panel {
        background: #fff;
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }
    .panel-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
    }
    .panel-title {
        font-size: 15px; font-weight: 700; color: #1a3c34;
    }

    /* ── Year selector ── */
    .year-select-wrap {
        display: flex; align-items: center; gap: 8px;
    }
    .year-select {
        padding: 6px 12px; border-radius: 8px;
        border: 1.5px solid #d0ddd9; font-size: 13px;
        color: #1a3c34; font-family: inherit; outline: none;
        cursor: pointer; background: #f8fbfa;
        transition: border-color 0.18s;
        appearance: none;
        padding-right: 28px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a9a90' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
    }
    .year-select:focus { border-color: #2d9e72; }

    /* ── Chart ── */
    .chart-wrap { position: relative; height: 260px; }
    .chart-state {
        height: 260px; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        color: #7a9a90; font-size: 14px; gap: 10px;
    }
    .chart-state i { font-size: 32px; color: #c8ddd7; }

    /* ── Search ── */
    .search-wrap {
        position: relative; width: 200px;
    }
    .search-wrap i {
        position: absolute; left: 11px; top: 50%;
        transform: translateY(-50%);
        color: #7a9a90; font-size: 13px; pointer-events: none;
    }
    .search-wrap input {
        width: 100%; padding: 8px 12px 8px 32px;
        border: 1.5px solid #d0ddd9; border-radius: 8px;
        font-size: 13px; color: #333; font-family: inherit;
        outline: none; transition: border-color 0.18s;
        background: #f8fbfa; box-sizing: border-box;
    }
    .search-wrap input:focus { border-color: #2d9e72; background: #fff; }

    /* ── User Table ── */
    .user-table { width: 100%; border-collapse: collapse; }
    .user-table thead th {
        font-size: 12px; font-weight: 700; color: #7a9a90;
        text-align: left; padding: 0 0 10px;
        border-bottom: 1px solid #eef2f1;
        text-transform: uppercase; letter-spacing: 0.04em;
    }
    .user-table tbody tr {
        border-bottom: 1px solid #f5f8f7;
        transition: background 0.12s;
    }
    .user-table tbody tr:last-child { border-bottom: none; }
    .user-table tbody tr:hover { background: #f8fbfa; }
    .user-table td { padding: 11px 0; font-size: 13.5px; color: #333; vertical-align: middle; }

    .user-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 700; color: #fff;
        flex-shrink: 0; margin-right: 10px;
    }
    .user-name-cell { display: flex; align-items: center; }
    .user-name { font-weight: 600; color: #1a3c34; font-size: 13.5px; }

    .badge-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 28px; padding: 3px 10px;
        background: #e8f5f0; color: #1a7a54;
        border-radius: 999px; font-size: 12.5px; font-weight: 600;
    }

    /* ── Pagination ── */
    .pagination-row {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 16px; padding-top: 14px;
        border-top: 1px solid #eef2f1;
        flex-wrap: wrap; gap: 10px;
    }
    .pagination-info { font-size: 12.5px; color: #7a9a90; }
    .pagination-pages { display: flex; align-items: center; gap: 4px; }
    .page-btn {
        width: 30px; height: 30px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 500; color: #5a7a70;
        border: 1.5px solid #e0e8e5; background: #fff;
        cursor: pointer; text-decoration: none;
        transition: all 0.15s;
    }
    .page-btn:hover   { border-color: #2d9e72; color: #1a3c34; }
    .page-btn.active  { background: #1a3c34; color: #fff; border-color: #1a3c34; }
    .page-btn.dots    { border: none; background: none; cursor: default; color: #7a9a90; }
    .page-btn.disabled { opacity: .4; cursor: not-allowed; pointer-events: none; }

    /* ── Per-page select ── */
    .per-page-select {
        padding: 5px 24px 5px 10px; border-radius: 8px;
        border: 1.5px solid #d0ddd9; font-size: 12.5px;
        color: #333; font-family: inherit; outline: none;
        background: #f8fbfa; cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a9a90' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
    }
    .per-page-select:focus { border-color: #2d9e72; }

    /* ── Empty / Error states ── */
    .empty-row td {
        text-align: center; padding: 40px 0;
        color: #7a9a90; font-size: 14px;
    }
    .empty-icon { font-size: 36px; display: block; margin-bottom: 10px; color: #c8ddd7; }
    .alert-error {
        background: #fee2e2; color: #b91c1c;
        padding: 14px 18px; border-radius: 10px;
        font-size: 13.5px; margin-bottom: 20px;
        display: flex; align-items: center; gap: 10px;
    }

    /* ── Loading skeleton ── */
    @keyframes shimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    .skeleton {
        background: linear-gradient(90deg, #f0f4f3 25%, #e0ebe8 50%, #f0f4f3 75%);
        background-size: 600px 100%;
        animation: shimmer 1.4s infinite;
        border-radius: 6px;
        display: inline-block;
    }

    /* ── Loading overlay ── */
    #loadingOverlay {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(255,255,255,0.55);
        align-items: center; justify-content: center;
    }
    #loadingOverlay.show { display: flex; }
    .spinner {
        width: 40px; height: 40px;
        border: 4px solid #e0ebe8;
        border-top-color: #2d9e72;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')

{{-- Loading overlay --}}
<div id="loadingOverlay">
    <div class="spinner"></div>
</div>

{{-- Error alert (database connection failure) --}}
@if(isset($error) && $error)
<div class="alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span>{{ $error }}</span>
</div>
@endif

{{-- ── STAT CARDS ── --}}
<div class="stat-row">
    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#e8f5f0;">
            <i class="fas fa-users" style="color:#2d9e72;"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value" style="color:#2d9e72;">
                {{ $totalUser > 0 ? number_format($totalUser) : '0 Data' }}
            </div>
            <div class="stat-label">Total Pengguna</div>
            <div class="stat-sub">Semua pengguna terdaftar</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#e8f0ff;">
            <i class="fas fa-calendar-check" style="color:#4f73d9;"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value" style="color:#4f73d9;">
                {{ $totalJadwal > 0 ? number_format($totalJadwal) : '0 Data' }}
            </div>
            <div class="stat-label">Total Jadwal Pemeriksaan</div>
            <div class="stat-sub">Semua jadwal yang dibuat</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon-wrap" style="background:#eef0ff;">
            <i class="fas fa-heartbeat" style="color:#7c6fd4;"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value" style="color:#7c6fd4;">
                {{ $aktifBulanIni > 0 ? number_format($aktifBulanIni) : '0 Data' }}
            </div>
            <div class="stat-label">Pengguna Aktif Bulan Ini</div>
            <div class="stat-sub">Aktif dalam 30 hari terakhir</div>
        </div>
    </div>
</div>

{{-- ── MAIN PANELS ── --}}
<div class="dash-grid">

    {{-- Chart Panel --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Pemeriksaan Bulanan</div>
            <form method="GET" action="{{ route('admin.index') }}" id="yearForm" class="year-select-wrap">
                <input type="hidden" name="search"   value="{{ $search }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <select name="tahun" class="year-select" onchange="submitWithLoading('yearForm')">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $tahun === $y ? 'selected' : '' }}>
                            {{ $y === now()->year ? 'Tahun Ini' : $y }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if(isset($error) && $error)
        <div class="chart-state">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Grafik gagal dimuat.</span>
        </div>
        @else
        <div class="chart-wrap" id="chartWrap">
            <canvas id="monthlyChart"></canvas>
        </div>
        @endif
    </div>

    {{-- User List Panel --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Daftar Pengguna</div>
            <form method="GET" action="{{ route('admin.index') }}" id="searchForm">
                <input type="hidden" name="tahun"    value="{{ $tahun }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search"
                           value="{{ $search }}"
                           placeholder="Cari pengguna..."
                           id="searchInput"
                           oninput="debounceSearch()"
                           autocomplete="off">
                </div>
            </form>
        </div>

        <table class="user-table">
            <thead>
                <tr>
                    <th style="width:36px">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th style="text-align:right">Total Pemeriksaan</th>
                </tr>
            </thead>
            <tbody>
                @if($users->isEmpty())
                <tr class="empty-row">
                    <td colspan="4">
                        @if($search)
                            <i class="fas fa-search empty-icon"></i>
                            Pengguna tidak ditemukan untuk "<strong>{{ $search }}</strong>"
                        @else
                            <i class="fas fa-users-slash empty-icon"></i>
                            Belum ada data pengguna.
                        @endif
                    </td>
                </tr>
                @else
                    @php
                        $colors = ['#2d9e72','#4f73d9','#e05252','#f59e0b','#7c6fd4','#0ea5e9','#ec4899','#14b8a6'];
                        $offset = ($users->currentPage() - 1) * $users->perPage();
                    @endphp
                    @foreach($users as $i => $u)
                    <tr>
                        <td style="color:#7a9a90;font-size:12.5px;">{{ $offset + $loop->iteration }}</td>
                        <td>
                            <div class="user-name-cell">
                                <div class="user-avatar"
                                     style="background:{{ $colors[($offset + $loop->index) % count($colors)] }};">
                                    {{ strtoupper(substr($u->full_name ?? '?', 0, 1)) }}
                                </div>
                                <div class="user-name">{{ $u->full_name ?? '-' }}</div>
                            </div>
                        </td>
                        <td style="color:#7a9a90;font-size:12.5px;">{{ $u->email }}</td>
                        <td style="text-align:right;">
                            <span class="badge-count">{{ $u->total_pemeriksaan }}</span>
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($users->lastPage() > 1)
        <div class="pagination-row">
            <div class="pagination-info">
                {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </div>
            <div class="pagination-pages">
                {{-- Prev --}}
                @if($users->onFirstPage())
                    <span class="page-btn disabled">
                        <i class="fas fa-chevron-left" style="font-size:10px;"></i>
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="page-btn">
                        <i class="fas fa-chevron-left" style="font-size:10px;"></i>
                    </a>
                @endif

                @php
                    $cur  = $users->currentPage();
                    $last = $users->lastPage();
                    if ($last <= 7) {
                        $pages = range(1, $last);
                    } else {
                        $pages = [1, 2, 3];
                        if ($cur > 4) $pages[] = '…';
                        foreach (range(max(4, $cur - 1), min($last - 3, $cur + 1)) as $p)
                            if (!in_array($p, $pages)) $pages[] = $p;
                        if ($cur < $last - 3) $pages[] = '…';
                        foreach ([max($last - 2, 4), $last - 1, $last] as $p)
                            if (!in_array($p, $pages) && $p > 3) $pages[] = $p;
                    }
                @endphp

                @foreach($pages as $p)
                    @if($p === '…')
                        <span class="page-btn dots">…</span>
                    @else
                        <a href="{{ $users->url($p) }}"
                           class="page-btn {{ $cur === $p ? 'active' : '' }}">{{ $p }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="page-btn">
                        <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    </a>
                @else
                    <span class="page-btn disabled">
                        <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    </span>
                @endif
            </div>
            <form method="GET" action="{{ route('admin.index') }}" id="perPageForm">
                <input type="hidden" name="search"  value="{{ $search }}">
                <input type="hidden" name="tahun"   value="{{ $tahun }}">
                <input type="hidden" name="page"    value="1">
                <select name="per_page" class="per-page-select"
                        onchange="submitWithLoading('perPageForm')">
                    @foreach([10, 25, 50] as $n)
                        <option value="{{ $n }}" {{ $perPage === $n ? 'selected' : '' }}>{{ $n }} / halaman</option>
                    @endforeach
                </select>
            </form>
        </div>
        @else
        <div class="pagination-row">
            <div class="pagination-info">
                {{ $users->total() }} pengguna
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if(!isset($error) || !$error)
// ── Monthly Chart ──
const labels  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const dataset = @json(array_values($chartData->toArray()));
const hasData = dataset.some(v => v > 0);

const ctx = document.getElementById('monthlyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Jumlah Pemeriksaan',
            data: dataset,
            backgroundColor: 'rgba(45,158,114,0.85)',
            hoverBackgroundColor: 'rgba(26,60,52,0.9)',
            borderRadius: 6,
            borderSkipped: false,
            barThickness: 28,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { font: { size: 12 }, color: '#5a7a70', boxWidth: 14, padding: 16 }
            },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.parsed.y.toLocaleString('id')} jadwal`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    font: { size: 12 }, color: '#7a9a90',
                    callback: v => v.toLocaleString('id')
                }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 12 }, color: '#7a9a90' }
            }
        }
    }
});

if (!hasData) {
    document.getElementById('chartWrap').insertAdjacentHTML('beforeend',
        '<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;' +
        'justify-content:center;color:#7a9a90;font-size:14px;pointer-events:none;gap:10px;">' +
        '<i class="fas fa-chart-bar" style="font-size:32px;color:#c8ddd7;"></i>' +
        '<span>Belum ada data jadwal pada tahun ini</span></div>'
    );
}
@endif

// ── Debounced search ──
let searchTimer;
function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        showLoading();
        document.getElementById('searchForm').submit();
    }, 450);
}

// ── Loading state ──
function showLoading() {
    document.getElementById('loadingOverlay').classList.add('show');
}
function submitWithLoading(formId) {
    showLoading();
    document.getElementById(formId).submit();
}

// Pagination links juga pakai loading
document.querySelectorAll('a.page-btn').forEach(a => {
    a.addEventListener('click', showLoading);
});
</script>
@endsection
