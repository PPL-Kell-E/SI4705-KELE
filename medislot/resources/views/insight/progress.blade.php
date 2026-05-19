@extends('layouts.app')

@section('title', 'Progress Kesehatan - MEDISLOT')
@section('page-title', 'Progress Kesehatan')
@section('page-subtitle', 'Pantau perkembangan kesehatanmu setiap hari.')

@section('extra-styles')
<style>
/* ── GRID UTAMA ── */
.progress-grid-top  { display:grid; grid-template-columns:1fr 260px; gap:16px; margin-bottom:16px; }
.progress-grid-bot  { display:grid; grid-template-columns:1fr 280px; gap:16px; margin-bottom:16px; }

/* ── CARD BASE ── */
.pg-card {
    background:#fff; border-radius:16px;
    padding:24px; box-shadow:0 1px 4px rgba(0,0,0,0.07);
}

/* ── PROGRESS KONSISTENSI ── */
.pk-label { font-size:16px; font-weight:700; color:#1a3c34; margin-bottom:4px; }
.pk-persen {
    font-size:24px; font-weight:800; color:#1a3c34;
    margin-left:auto;
}
.pk-header { display:flex; align-items:center; margin-bottom:14px; }
.pk-bar-wrap {
    height:14px; background:#e8eeec; border-radius:99px;
    overflow:hidden; margin-bottom:14px;
}
.pk-bar-fill {
    height:100%; background:#1a3c34; border-radius:99px;
    transition:width 0.6s ease;
}
.pk-info { display:flex; gap:32px; }
.pk-info-item { text-align:center; }
.pk-info-val { font-size:15px; font-weight:700; color:#1a3c34; }
.pk-info-lbl { font-size:12px; color:#7a9a90; margin-top:2px; }
.pk-divider { width:1px; background:#e8eeec; }

/* ── STREAK CARD ── */
.streak-card {
    background:#fff; border-radius:16px; padding:24px;
    box-shadow:0 1px 4px rgba(0,0,0,0.07);
    display:flex; flex-direction:column; align-items:center;
    justify-content:center; gap:8px; text-align:center;
}
.streak-fire {
    width:60px; height:60px; border-radius:50%;
    background:#fff3e0; display:flex; align-items:center;
    justify-content:center; font-size:28px; margin-bottom:4px;
}
.streak-count { font-size:38px; font-weight:800; color:#1a3c34; line-height:1; }
.streak-label { font-size:14px; font-weight:700; color:#f47c20; }
.streak-sub   { font-size:12px; color:#7a9a90; }
.streak-zero-msg { font-size:12px; color:#7a9a90; margin-top:6px; line-height:1.5; }

/* ── STATUS PEMERIKSAAN (STEP TRACKER) ── */
.step-section { margin-bottom:16px; }
.step-title { font-size:14px; font-weight:700; color:#1a3c34; margin-bottom:18px; }
.step-track {
    display:flex; align-items:flex-start;
    overflow-x:auto; padding-bottom:4px;
}
.step-item { display:flex; flex-direction:column; align-items:center; flex:1; min-width:90px; }
.step-row { display:flex; align-items:center; width:100%; }
.step-line { flex:1; height:2px; background:#e8eeec; }
.step-line.done { background:#2d9e72; }
.step-line.hidden { visibility:hidden; }
.step-circle {
    width:40px; height:40px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:14px; font-weight:700; flex-shrink:0;
    border:2px solid #e8eeec; background:#fff; color:#94a3b8;
}
.step-circle.done  { background:#2d9e72; border-color:#2d9e72; color:#fff; }
.step-circle.batal { background:#fee2e2; border-color:#fca5a5; color:#dc2626; }
.step-meta { margin-top:10px; text-align:center; }
.step-name { font-size:11.5px; font-weight:600; color:#1a3c34;
    max-width:80px; word-break:break-word; line-height:1.3; }
.step-badge {
    display:inline-block; font-size:10px; font-weight:600;
    padding:2px 8px; border-radius:99px; margin-top:5px;
}
.step-badge.selesai   { background:#d4ede3; color:#1a5c3a; }
.step-badge.mendatang { background:#dbeeff; color:#2563eb; }
.step-badge.batal     { background:#fee2e2; color:#dc2626; }

/* ── NEXT JADWAL BANNER ── */
.next-banner {
    display:flex; align-items:center; gap:14px;
    background:#eff6ff; border-radius:12px;
    padding:14px 20px; margin-bottom:16px;
    border:1px solid #bfdbfe; cursor:pointer;
    text-decoration:none; transition:background 0.18s;
}
.next-banner:hover { background:#dbeafe; }
.next-icon {
    width:38px; height:38px; border-radius:50%;
    background:#2563eb; display:flex; align-items:center;
    justify-content:center; color:#fff; font-size:16px; flex-shrink:0;
}
.next-text-lbl { font-size:12px; color:#5a7a90; margin-bottom:2px; }
.next-text-val { font-size:14px; font-weight:700; color:#1e40af; }
.next-arrow { margin-left:auto; color:#3b82f6; font-size:16px; }
.next-empty {
    display:flex; align-items:center; gap:12px;
    background:#f8fafc; border-radius:12px;
    padding:14px 20px; margin-bottom:16px;
    border:1px solid #e2e8f0; color:#7a9a90; font-size:13px;
}

/* ── CHART CARD ── */
.chart-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.chart-title  { font-size:15px; font-weight:700; color:#1a3c34; }
.chart-filter {
    padding:7px 12px; border:1.5px solid #d0ddd9; border-radius:8px;
    font-size:13px; color:#1a3c34; font-family:inherit;
    background:#fff; cursor:pointer; outline:none;
}
.chart-wrap   { position:relative; height:260px; }
.chart-legend {
    display:flex; gap:20px; justify-content:center;
    margin-top:16px; flex-wrap:wrap;
}
.legend-item  { display:flex; align-items:center; gap:6px; font-size:12px; color:#5a7a70; }
.legend-dot   { width:10px; height:10px; border-radius:50%; }
.chart-empty {
    height:260px; display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    color:#94a3b8; gap:10px;
}
.chart-empty i { font-size:36px; opacity:0.4; }

/* ── STATISTIK ── */
.stat-title { font-size:14px; font-weight:700; color:#1a3c34; margin-bottom:16px; }
.stat-item {
    display:flex; align-items:center; gap:14px;
    padding:14px 0; border-bottom:1px solid #f0f4f3;
}
.stat-item:last-child { border-bottom:none; }
.stat-icon-wrap {
    width:44px; height:44px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px; flex-shrink:0;
}
.stat-body { flex:1; }
.stat-lbl   { font-size:12px; color:#7a9a90; margin-bottom:2px; }
.stat-val   { font-size:18px; font-weight:700; color:#1a3c34; line-height:1; }
.stat-sub   { font-size:11px; color:#94a3b8; margin-top:2px; }

/* ── FOOTER ── */
.pg-footer {
    display:flex; align-items:center; gap:8px;
    font-size:12px; color:#94a3b8; margin-top:4px;
}
.pg-footer i { color:#cbd5e1; }

/* ── ERROR / EMPTY STATES ── */
.pg-error {
    background:#fff0f0; border:1px solid #fca5a5; border-radius:12px;
    padding:16px 20px; color:#dc2626; font-size:13.5px;
    display:flex; align-items:center; gap:10px; margin-bottom:16px;
}
.pg-empty-full {
    text-align:center; padding:64px 20px; color:#94a3b8;
}
.pg-empty-full i { font-size:52px; opacity:0.3; display:block; margin-bottom:16px; }
.pg-empty-full p { font-size:14px; }

/* ── ZOOM CONTROLS ── */
.zoom-controls {
    display:flex; align-items:center; justify-content:center; gap:16px;
    margin-top:8px;
}
.zoom-btn {
    width:32px; height:32px; border-radius:50%;
    border:1.5px solid #d0ddd9; background:#fff;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#5a7a70; font-size:14px;
    transition:all 0.18s;
}
.zoom-btn:hover { border-color:#2d9e72; color:#2d9e72; }
</style>
@endsection

@section('content')

{{-- ERROR STATE --}}
@if($error)
<div class="pg-error"><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
@endif

@if(!$error && $totalBulanIni === 0 && $totalPeriode === 0)
{{-- EMPTY STATE --}}
<div class="pg-empty-full">
    <i class="fas fa-chart-line"></i>
    <p><strong>Belum ada data pemeriksaan untuk ditampilkan.</strong></p>
    <p style="margin-top:8px;font-size:13px;">Buat jadwal pemeriksaan pertamamu untuk mulai memantau progres kesehatan.</p>
    <a href="{{ route('jadwal.create') }}"
       style="display:inline-flex;align-items:center;gap:8px;margin-top:20px;background:#1a3c34;color:#fff;padding:10px 24px;border-radius:99px;text-decoration:none;font-size:14px;font-weight:600;">
        <i class="fas fa-plus"></i> Buat Jadwal Sekarang
    </a>
</div>
@else

{{-- ROW 1: Progress Konsistensi + Streak --}}
<div class="progress-grid-top">

    {{-- Progress Konsistensi --}}
    <div class="pg-card">
        <div class="pk-header">
            <div class="pk-label">Progress Konsistensi</div>
            <div class="pk-persen">{{ $progressPersen }}%</div>
        </div>
        <div class="pk-bar-wrap">
            <div class="pk-bar-fill" style="width:{{ $progressPersen }}%"></div>
        </div>
        @if($totalBulanIni > 0)
        <div class="pk-info">
            <div class="pk-info-item">
                <div class="pk-info-val">{{ $selesaiBulanIni }}/{{ $totalBulanIni }} Selesai</div>
                <div class="pk-info-lbl">Bulan ini</div>
            </div>
            <div class="pk-divider"></div>
            <div class="pk-info-item">
                <div class="pk-info-val">{{ $sisaBulanIni }} Tersisa</div>
                <div class="pk-info-lbl">Belum selesai</div>
            </div>
        </div>
        @else
        <p style="font-size:13px;color:#94a3b8;">Belum ada jadwal pemeriksaan bulan ini.</p>
        @endif
    </div>

    {{-- Health Streak --}}
    <div class="streak-card">
        <div class="streak-fire">🔥</div>
        @if($streak > 0)
            <div class="streak-count">{{ $streak }}</div>
            <div class="streak-label">Hari Konsisten</div>
            <div class="streak-sub">Pertahankan konsistensimu!</div>
        @else
            <div class="streak-count" style="font-size:28px;color:#94a3b8;">—</div>
            <div class="streak-label" style="color:#94a3b8;">Belum Ada Streak</div>
            <div class="streak-zero-msg">Mulai lakukan pemeriksaan rutin untuk mendapatkan streak.</div>
        @endif
    </div>
</div>

{{-- ROW 2: Status Pemeriksaan (Step Tracker) --}}
@if($recentJadwal->isNotEmpty())
<div class="pg-card step-section">
    <div class="step-title">Status Pemeriksaan</div>
    <div class="step-track">
        @foreach($recentJadwal as $i => $jadwal)
        <div class="step-item">
            <div class="step-row">
                {{-- line kiri --}}
                @if($i === 0)
                    <div class="step-line hidden"></div>
                @elseif($recentJadwal[$i-1]->status === 'selesai')
                    <div class="step-line done"></div>
                @else
                    <div class="step-line"></div>
                @endif

                {{-- circle --}}
                @if($jadwal->status === 'selesai')
                    <div class="step-circle done"><i class="fas fa-check"></i></div>
                @elseif($jadwal->status === 'batal')
                    <div class="step-circle batal"><i class="fas fa-times"></i></div>
                @else
                    <div class="step-circle">{{ $i + 1 }}</div>
                @endif

                {{-- line kanan --}}
                @if($i === $recentJadwal->count() - 1)
                    <div class="step-line hidden"></div>
                @elseif($jadwal->status === 'selesai')
                    <div class="step-line done"></div>
                @else
                    <div class="step-line"></div>
                @endif
            </div>
            <div class="step-meta">
                <div class="step-name">{{ $jadwal->jenis_pemeriksaan }}</div>
                <span class="step-badge {{ $jadwal->status }}">
                    @if($jadwal->status === 'selesai') Selesai
                    @elseif($jadwal->status === 'batal') Batal
                    @else Menunggu
                    @endif
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ROW 3: Jadwal Berikutnya --}}
@if($nextJadwal)
<a href="{{ route('jadwal.index') }}" class="next-banner">
    <div class="next-icon"><i class="fas fa-clock"></i></div>
    <div>
        <div class="next-text-lbl">Pemeriksaan berikutnya:</div>
        <div class="next-text-val">
            {{ $nextJadwal->jenis_pemeriksaan }} —
            {{ $nextJadwal->tanggal->translatedFormat('l, d M Y') }}
            | {{ \Carbon\Carbon::parse($nextJadwal->waktu)->format('H.i') }} WIB
        </div>
    </div>
    <div class="next-arrow"><i class="fas fa-chevron-right"></i></div>
</a>
@else
<div class="next-empty">
    <i class="fas fa-calendar-times"></i>
    Belum ada jadwal pemeriksaan berikutnya.
</div>
@endif

{{-- ROW 4: Chart + Statistik --}}
<div class="progress-grid-bot">

    {{-- Tren Pemeriksaan --}}
    <div class="pg-card">
        <div class="chart-header">
            <div class="chart-title">Tren Pemeriksaan</div>
            <form method="GET" action="{{ route('insight.progress') }}" style="margin:0;">
                <select name="periode" class="chart-filter" onchange="this.form.submit()">
                    <option value="1_bulan"  {{ $periode === '1_bulan'  ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="6_bulan"  {{ $periode === '6_bulan'  ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="1_tahun"  {{ $periode === '1_tahun'  ? 'selected' : '' }}>1 Tahun Terakhir</option>
                </select>
            </form>
        </div>

        @if(count($chartData['labels']) > 0)
        <div class="chart-wrap">
            <canvas id="trendChart"></canvas>
        </div>
        <div class="chart-legend">
            <div class="legend-item"><div class="legend-dot" style="background:#2d9e72;"></div> Selesai</div>
            <div class="legend-item"><div class="legend-dot" style="background:#f59e0b;"></div> Berlangsung</div>
            <div class="legend-item"><div class="legend-dot" style="background:#94a3b8;"></div> Pending</div>
        </div>
        <div class="zoom-controls">
            <button class="zoom-btn" onclick="zoomChart(-1)" title="Zoom out"><i class="fas fa-search-minus"></i></button>
            <button class="zoom-btn" onclick="zoomChart(1)"  title="Zoom in"><i class="fas fa-search-plus"></i></button>
        </div>
        @else
        <div class="chart-empty">
            <i class="fas fa-chart-line"></i>
            <span>Belum ada data pemeriksaan untuk periode ini.</span>
        </div>
        @endif
    </div>

    {{-- Statistik --}}
    <div class="pg-card">
        <div class="stat-title">Statistik Pemeriksaan</div>

        <div class="stat-item">
            <div class="stat-icon-wrap" style="background:#e8f5f0;">
                <i class="fas fa-calendar-check" style="color:#2d9e72;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-lbl">Total Pemeriksaan</div>
                <div class="stat-val">{{ $totalPeriode }} <span style="font-size:13px;font-weight:400;color:#7a9a90;">Kali</span></div>
                <div class="stat-sub">
                    @if($periode === '1_bulan') 1 bulan terakhir
                    @elseif($periode === '1_tahun') 1 tahun terakhir
                    @else 6 bulan terakhir
                    @endif
                </div>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon-wrap" style="background:#d4ede3;">
                <i class="fas fa-check-circle" style="color:#1a7a4e;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-lbl">Selesai</div>
                <div class="stat-val">{{ $selesaiPeriode }} <span style="font-size:13px;font-weight:400;color:#7a9a90;">Kali</span></div>
                <div class="stat-sub">{{ $selesaiPersen }}% dari total</div>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon-wrap" style="background:#f0f4f8;">
                <i class="fas fa-clock" style="color:#64748b;"></i>
            </div>
            <div class="stat-body">
                <div class="stat-lbl">Pending</div>
                <div class="stat-val">{{ $pendingPeriode }} <span style="font-size:13px;font-weight:400;color:#7a9a90;">Kali</span></div>
                <div class="stat-sub">{{ $pendingPersen }}% dari total</div>
            </div>
        </div>
    </div>
</div>

{{-- Footer --}}
@if($lastUpdated)
<div class="pg-footer">
    <i class="fas fa-info-circle"></i>
    Data terakhir diperbarui: {{ $lastUpdated }}
</div>
@endif

@endif {{-- end !error && !empty --}}
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if(count($chartData['labels']) > 0)
(function () {
    const labels   = @json($chartData['labels']);
    const values   = @json($chartData['values']);
    const statuses = @json($chartData['statuses']);
    const colors   = @json($chartData['colors']);

    const ctx = document.getElementById('trendChart').getContext('2d');

    // Gradient fill
    const grad = ctx.createLinearGradient(0, 0, 0, 260);
    grad.addColorStop(0,   'rgba(45,158,114,0.18)');
    grad.addColorStop(1,   'rgba(45,158,114,0.01)');

    window._trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: '#2d9e72',
                backgroundColor: grad,
                pointBackgroundColor: colors,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 8,
                pointHoverRadius: 10,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + ctx.parsed.y + '% — ' + (statuses[ctx.dataIndex] === 'selesai' ? 'Selesai' : statuses[ctx.dataIndex] === 'pending' ? 'Pending' : 'Berlangsung'),
                    }
                }
            },
            scales: {
                y: {
                    min: 0, max: 100,
                    ticks: {
                        callback: v => v + '%',
                        font: { size: 11 },
                        color: '#94a3b8',
                        stepSize: 25,
                    },
                    grid: { color: '#f0f4f3' },
                    border: { display: false },
                },
                x: {
                    ticks: {
                        font: { size: 11 },
                        color: '#94a3b8',
                        maxRotation: 45,
                    },
                    grid: { display: false },
                    border: { display: false },
                }
            }
        }
    });
})();

function zoomChart(direction) {
    const chart = window._trendChart;
    if (!chart) return;
    const cur = chart.options.scales.y.max;
    const next = Math.max(25, Math.min(200, cur - direction * 25));
    chart.options.scales.y.max = next;
    chart.update();
}
@endif
</script>
@endsection
