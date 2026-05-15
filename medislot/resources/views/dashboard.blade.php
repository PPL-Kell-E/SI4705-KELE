@extends('layouts.app')

@section('title', 'Dashboard - MEDISLOT')
@section('page-title', 'Halo, ' . (Auth::user()->full_name ?? Auth::user()->name) . '! 👋')
@section('page-subtitle', 'Jaga kesehatanmu hari ini. Cek rekomendasi pemeriksaan rutinmu.')

@section('extra-styles')
<style>
.dash-date {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600; color: #4a6a60;
    margin-bottom: 20px;
}
.dash-date i { color: #2d9e72; }

/* STAT CARDS */
.stat-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 16px; margin-bottom: 24px;
}
.stat-card {
    background: #fff; border-radius: 14px; padding: 20px;
    display: flex; align-items: flex-start; gap: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.stat-value { font-size: 28px; font-weight: 800; color: #1a3c34; line-height: 1.1; }
.stat-label { font-size: 12px; color: #7a9a90; margin-top: 2px; }
.stat-link { font-size: 11.5px; color: #2d9e72; text-decoration: none; margin-top: 8px; display: block; font-weight: 500; }
.stat-link:hover { text-decoration: underline; }

/* LAYOUT */
.dash-row { display: grid; gap: 20px; margin-bottom: 20px; }
.dash-row-3-2 { grid-template-columns: 1fr 300px; }
.dash-row-2-2 { grid-template-columns: 1fr 1fr; }

/* CARD */
.dash-card {
    background: #fff; border-radius: 14px; padding: 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.card-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: #1a3c34; margin-bottom: 16px;
}
.card-title i { color: #2d9e72; font-size: 15px; }
.card-header-row {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;
}
.card-link {
    font-size: 12.5px; color: #2d9e72; text-decoration: none; font-weight: 500;
    display: flex; align-items: center; gap: 4px;
}
.card-link:hover { text-decoration: underline; }

/* PEMERIKSAAN BERIKUTNYA */
.next-exam {
    display: flex; align-items: center; gap: 16px;
    padding: 16px; background: #f7faf9; border-radius: 12px; margin-bottom: 16px;
}
.next-exam-icon {
    width: 52px; height: 52px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; flex-shrink: 0; background: #e8f4ff;
}
.next-exam-name { font-size: 15px; font-weight: 700; color: #1a3c34; }
.next-exam-date { font-size: 13px; color: #2d9e72; font-weight: 600; margin-top: 3px; }
.next-exam-time { font-size: 12px; color: #7a9a90; margin-top: 2px; }
.btn-lihat-jadwal {
    display: flex; align-items: center; justify-content: space-between;
    background: #1a3c34; color: #fff; text-decoration: none;
    border-radius: 10px; padding: 13px 18px;
    font-size: 13.5px; font-weight: 600; transition: background 0.18s;
}
.btn-lihat-jadwal:hover { background: #2d9e72; }

/* PROGRESS */
.progress-pct { font-size: 32px; font-weight: 800; color: #2d9e72; }
.progress-bar-wrap { height: 10px; background: #e8eeec; border-radius: 10px; overflow: hidden; margin: 10px 0; }
.progress-bar-fill { height: 100%; background: #2d9e72; border-radius: 10px; }
.progress-meta { font-size: 12px; color: #7a9a90; margin-bottom: 16px; }
.progress-steps { display: flex; align-items: center; overflow-x: auto; padding-bottom: 4px; }
.step-item { display: flex; flex-direction: column; align-items: center; min-width: 60px; }
.step-connector { flex: 1; height: 3px; background: #2d9e72; min-width: 12px; }
.step-connector.pending { background: #e8eeec; }
.step-circle {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; margin-bottom: 5px;
}
.step-circle.done { background: #2d9e72; color: #fff; }
.step-circle.pending { background: #e8eeec; color: #7a9a90; }
.step-label { font-size: 10px; color: #5a7a70; font-weight: 500; text-align: center; }
.step-status { font-size: 10px; padding: 2px 5px; border-radius: 8px; margin-top: 3px; font-weight: 500; }
.step-status.done { background: #e8fff4; color: #2d9e72; }
.step-status.pending { background: #f0f4f3; color: #7a9a90; }

/* AKSES CEPAT */
.quick-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px; }
.quick-item {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 14px 10px; background: #f7faf9; border-radius: 12px;
    text-decoration: none; transition: background 0.15s;
}
.quick-item:hover { background: #eef5f2; }
.quick-item-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.quick-item-label { font-size: 11px; font-weight: 600; color: #1a3c34; text-align: center; }

/* INSIGHT */
.insight-card {
    display: flex; align-items: flex-start; gap: 14px;
    background: #f0f7ff; border-radius: 12px; padding: 16px;
    text-decoration: none; transition: background 0.15s;
}
.insight-card:hover { background: #e0efff; }
.insight-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;
}
.insight-text { font-size: 13px; font-weight: 700; color: #1a3c34; margin-bottom: 4px; }
.insight-sub { font-size: 12px; color: #7a9a90; }

/* TIPS */
.tips-list { display: flex; flex-direction: column; gap: 10px; }
.tip-item { display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #4a6a60; line-height: 1.5; }
.tip-dot { width: 7px; height: 7px; border-radius: 50%; background: #2d9e72; flex-shrink: 0; margin-top: 6px; }
.tip-arrow { color: #b0c8c0; font-size: 11px; margin-left: auto; align-self: center; flex-shrink: 0; }

/* PENCAPAIAN */
.achievement-latest {
    display: flex; align-items: center; gap: 16px;
    padding: 14px; background: #f7faf9; border-radius: 12px;
}
.achievement-badge-icon {
    width: 54px; height: 54px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
}
.achievement-latest-name { font-size: 14px; font-weight: 700; color: #1a3c34; margin-bottom: 4px; }
.achievement-latest-desc { font-size: 12px; color: #7a9a90; line-height: 1.45; }

/* AKTIVITAS */
.activity-list { display: flex; flex-direction: column; gap: 12px; }
.activity-item { display: flex; align-items: flex-start; gap: 12px; }
.activity-icon {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0;
}
.activity-label { font-size: 13px; font-weight: 600; color: #1a3c34; }
.activity-sub { font-size: 12px; color: #7a9a90; }
.activity-time { font-size: 11px; color: #b0c8c0; white-space: nowrap; }

.empty-card { text-align: center; padding: 20px; color: #7a9a90; font-size: 13px; }
.empty-card i { font-size: 26px; margin-bottom: 8px; display: block; opacity: 0.35; }
</style>
@endsection

@section('content')

<div class="dash-date">
    <i class="fas fa-calendar-alt"></i>
    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
</div>

{{-- STAT CARDS --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f4ff;color:#4a90d9;"><i class="fas fa-calendar-check"></i></div>
        <div>
            <div class="stat-value">{{ $jadwalBulanIni }}</div>
            <div class="stat-label">Jadwal Bulan Ini</div>
            <a href="{{ route('jadwal.index') }}" class="stat-link">Lihat jadwal →</a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff0e8;color:#e67e22;"><i class="fas fa-bell"></i></div>
        <div>
            <div class="stat-value">{{ $pengingatAktif }}</div>
            <div class="stat-label">Pengingat Aktif</div>
            <a href="{{ route('jadwal.index') }}" class="stat-link">Lihat pengingat →</a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8fff4;color:#2d9e72;"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value">{{ $pemeriksaanSelesai }}</div>
            <div class="stat-label">Pemeriksaan Selesai</div>
            <a href="{{ route('riwayat.index') }}" class="stat-link">Lihat riwayat →</a>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff3e0;color:#f47c20;"><i class="fas fa-fire"></i></div>
        <div>
            <div class="stat-value">{{ $streak }}</div>
            <div class="stat-label">Hari Streak</div>
            <div style="font-size:11.5px;color:#7a9a90;margin-top:8px;">
                {{ $streak > 0 ? 'Pertahankan konsistensimu!' : 'Mulai streak hari ini!' }}
            </div>
        </div>
    </div>
</div>

{{-- ROW 1: Pemeriksaan & Progress | Akses Cepat --}}
<div class="dash-row dash-row-3-2">

    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Pemeriksaan Berikutnya --}}
        <div class="dash-card">
            <div class="card-title"><i class="fas fa-calendar-alt"></i> Pemeriksaan Berikutnya</div>
            @if($jadwalTerdekat)
                <div class="next-exam">
                    <div class="next-exam-icon">🦷</div>
                    <div>
                        <div class="next-exam-name">{{ $jadwalTerdekat->jenis_pemeriksaan }}</div>
                        <div class="next-exam-date">
                            {{ \Carbon\Carbon::parse($jadwalTerdekat->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </div>
                        <div class="next-exam-time">
                            <i class="far fa-clock"></i>
                            {{ \Carbon\Carbon::parse($jadwalTerdekat->waktu)->format('H:i') }} WIB
                        </div>
                    </div>
                </div>
                <a href="{{ route('jadwal.index') }}" class="btn-lihat-jadwal">
                    Lihat Jadwal <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <div class="empty-card">
                    <i class="fas fa-calendar-plus"></i>
                    Belum ada jadwal pemeriksaan mendatang.<br>
                    <a href="{{ route('jadwal.create') }}" style="color:#2d9e72;font-weight:600;text-decoration:none;">+ Tambah Jadwal</a>
                </div>
            @endif
        </div>

        {{-- Progress Konsistensi --}}
        <div class="dash-card">
            <div class="card-header-row">
                <div class="card-title" style="margin-bottom:0;">Progress Konsistensi</div>
                <a href="{{ route('insight.index') }}" class="card-link">Lihat Detail <i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="progress-pct">{{ $persentase }}%</div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" style="width:{{ $persentase }}%"></div>
            </div>
            <div class="progress-meta">
                {{ $selesaiBulanIni }}/{{ $totalBulanIni }} Selesai &nbsp;·&nbsp; {{ $totalBulanIni - $selesaiBulanIni }} Tersisa
            </div>
            @if($jadwalBulanIniList->count() > 0)
            <div class="progress-steps">
                @foreach($jadwalBulanIniList->take(5) as $idx => $j)
                    @if($idx > 0)
                        <div class="step-connector {{ $j->status !== 'selesai' ? 'pending' : '' }}"></div>
                    @endif
                    <div class="step-item">
                        <div class="step-circle {{ $j->status === 'selesai' ? 'done' : 'pending' }}">
                            @if($j->status === 'selesai') <i class="fas fa-check"></i> @else {{ $idx + 1 }} @endif
                        </div>
                        <div class="step-label">{{ \Illuminate\Support\Str::limit($j->jenis_pemeriksaan, 7, '') }}</div>
                        <div class="step-status {{ $j->status === 'selesai' ? 'done' : 'pending' }}">
                            {{ $j->status === 'selesai' ? 'Selesai' : ($j->status === 'batal' ? 'Batal' : 'Menunggu') }}
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <p style="font-size:12px;color:#7a9a90;text-align:center;margin:0;">Belum ada jadwal bulan ini.</p>
            @endif
        </div>
    </div>

    {{-- Akses Cepat --}}
    <div class="dash-card">
        <div class="card-title"><i class="fas fa-th"></i> Akses Cepat</div>
        <div class="quick-grid">
            <a href="{{ route('rekomendasi.index') }}" class="quick-item">
                <div class="quick-item-icon" style="background:#e8fff4;color:#2d9e72;"><i class="fas fa-lightbulb"></i></div>
                <span class="quick-item-label">Rekomendasi</span>
            </a>
            <a href="{{ route('pengingat.index') }}" class="quick-item">
                <div class="quick-item-icon" style="background:#fff0e8;color:#e67e22;"><i class="fas fa-bell"></i></div>
                <span class="quick-item-label">Pengingat</span>
            </a>
            <a href="{{ route('data-kesehatan.index') }}" class="quick-item">
                <div class="quick-item-icon" style="background:#fde8e8;color:#e74c3c;"><i class="fas fa-heartbeat"></i></div>
                <span class="quick-item-label">Data Kesehatan</span>
            </a>
            <a href="{{ route('jadwal.index') }}" class="quick-item">
                <div class="quick-item-icon" style="background:#e8f4ff;color:#4a90d9;"><i class="fas fa-calendar-alt"></i></div>
                <span class="quick-item-label">Jadwal Saya</span>
            </a>
        </div>
        <a href="{{ route('katalog.index') }}" class="card-link">Lihat Semua Menu <i class="fas fa-arrow-right"></i></a>
    </div>

</div>

{{-- ROW 2: Insight + Tips | Pencapaian + Aktivitas --}}
<div class="dash-row dash-row-2-2">

    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Insight Hari Ini --}}
        <div class="dash-card">
            <div class="card-title"><i class="fas fa-lightbulb"></i> Insight Hari Ini</div>
            @if($insightHariIni)
            <a href="{{ route('insight.index') }}" class="insight-card">
                <div class="insight-icon" style="background:{{ $insightHariIni['bg'] }};color:{{ $insightHariIni['color'] }};">
                    <i class="{{ $insightHariIni['icon'] }}"></i>
                </div>
                <div>
                    <div class="insight-text">{{ $insightHariIni['text'] }}</div>
                    <div class="insight-sub">{{ $insightHariIni['sub'] }}</div>
                </div>
                <i class="fas fa-chevron-right" style="color:#b0c8c0;margin-left:auto;align-self:center;font-size:12px;"></i>
            </a>
            @else
            <div class="empty-card"><i class="fas fa-lightbulb"></i>Tidak ada insight hari ini.</div>
            @endif
        </div>

        {{-- Tips Kesehatan --}}
        <div class="dash-card">
            <div class="card-title"><i class="fas fa-leaf"></i> Tips Kesehatan</div>
            <div class="tips-list">
                @foreach($tips as $tip)
                <div class="tip-item">
                    <span class="tip-dot"></span>
                    <span>{{ $tip }}</span>
                    <i class="fas fa-chevron-right tip-arrow"></i>
                </div>
                @endforeach
            </div>
            <a href="{{ route('insight.index') }}" class="card-link" style="margin-top:14px;display:inline-flex;">
                Lihat semua tips →
            </a>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Pencapaian Terbaru --}}
        <div class="dash-card">
            <div class="card-header-row">
                <div class="card-title" style="margin-bottom:0;"><i class="fas fa-trophy"></i> Pencapaian Terbaru</div>
                <a href="{{ route('insight.index') }}" class="card-link">Lihat Semua</a>
            </div>
            @if($pencapaianTerbaru)
            <div class="achievement-latest">
                <div class="achievement-badge-icon" style="background:#e8fff4;color:{{ $pencapaianTerbaru['color'] }};">
                    <i class="{{ $pencapaianTerbaru['icon'] }}"></i>
                </div>
                <div>
                    <div class="achievement-latest-name">{{ $pencapaianTerbaru['name'] }}</div>
                    <div class="achievement-latest-desc">{{ $pencapaianTerbaru['desc'] }}</div>
                </div>
            </div>
            @else
            <div class="empty-card"><i class="fas fa-trophy"></i>Selesaikan aktivitas untuk raih pencapaian.</div>
            @endif
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="dash-card">
            <div class="card-header-row">
                <div class="card-title" style="margin-bottom:0;"><i class="fas fa-history"></i> Aktivitas Terbaru</div>
                <a href="{{ route('riwayat.index') }}" class="card-link">Lihat semua →</a>
            </div>
            @if($aktivitasTerbaru->count() > 0)
            <div class="activity-list">
                @foreach($aktivitasTerbaru as $act)
                <div class="activity-item">
                    <div class="activity-icon" style="background:{{ $act['bg'] }};color:{{ $act['color'] }};">
                        <i class="{{ $act['icon'] }}"></i>
                    </div>
                    <div style="flex:1;">
                        <div class="activity-label">{{ $act['label'] }}</div>
                        <div class="activity-sub">{{ $act['jenis'] }} - {{ $act['tanggal'] }}</div>
                    </div>
                    <div class="activity-time">{{ $act['ago'] }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-card"><i class="fas fa-history"></i>Belum ada aktivitas tercatat.</div>
            @endif
        </div>
    </div>

</div>
@endsection
