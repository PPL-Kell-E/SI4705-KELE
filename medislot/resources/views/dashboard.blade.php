@extends('layouts.app')

@section('title', 'Dashboard - MEDISLOT')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang kembali!')

@section('extra-styles')
<style>
    /* ── BANNER ── */
    .dash-banner {
        background: linear-gradient(135deg, #1a3c34 0%, #2d9e72 100%);
        border-radius: 16px;
        padding: 28px 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        color: #fff;
    }
    .dash-banner-text h2 {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 6px;
    }
    .dash-banner-text p {
        font-size: 13.5px;
        opacity: 0.82;
    }
    .dash-banner-badge {
        background: rgba(255,255,255,0.15);
        border-radius: 12px;
        padding: 12px 20px;
        text-align: center;
        backdrop-filter: blur(4px);
    }
    .dash-banner-badge .badge-val {
        font-size: 22px;
        font-weight: 800;
    }
    .dash-banner-badge .badge-label {
        font-size: 11px;
        opacity: 0.8;
        margin-top: 2px;
    }

    /* ── STAT CARDS ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .stat-info .stat-val {
        font-size: 22px;
        font-weight: 700;
        color: #1a3c34;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-info .stat-label {
        font-size: 12px;
        color: #7a9a90;
        font-weight: 500;
    }

    /* ── SECTION TITLE ── */
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 14px;
    }

    /* ── QUICK ACCESS ── */
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 28px;
    }
    .quick-card {
        background: #fff;
        border-radius: 14px;
        padding: 22px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        text-align: center;
        text-decoration: none;
        color: inherit;
        transition: box-shadow 0.18s, transform 0.18s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .quick-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .quick-card .qc-icon {
        width: 50px; height: 50px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
    }
    .quick-card .qc-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #1a3c34;
    }

    /* ── BOTTOM GRID ── */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    .info-card {
        background: #fff;
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .info-card .section-title { margin-bottom: 16px; }

    /* Tips list */
    .tip-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f4f3;
        font-size: 13.5px;
        color: #444;
        line-height: 1.5;
    }
    .tip-item:last-child { border-bottom: none; }
    .tip-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #2d9e72;
        flex-shrink: 0;
        margin-top: 5px;
    }

    /* Profile info */
    .profile-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f4f3;
        font-size: 13.5px;
    }
    .profile-row:last-child { border-bottom: none; }
    .profile-row .pr-label {
        width: 100px;
        color: #7a9a90;
        font-weight: 500;
        flex-shrink: 0;
    }
    .profile-row .pr-val {
        color: #1a3c34;
        font-weight: 600;
    }
    .profile-row .pr-avatar {
        width: 52px; height: 52px;
        background: #2d9e72;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px; font-weight: 700;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')

{{-- BANNER --}}
<div class="dash-banner">
    <div class="dash-banner-text">
        <h2>Halo, {{ Auth::user()->full_name ?? Auth::user()->name }}! 👋</h2>
        <p>Jaga kesehatanmu hari ini. Cek rekomendasi pemeriksaan rutinmu.</p>
    </div>
    <div style="display:flex; gap:12px;">
        <div class="dash-banner-badge">
            <div class="badge-val">5</div>
            <div class="badge-label">Rekomendasi</div>
        </div>
        <div class="dash-banner-badge">
            <div class="badge-val">{{ now()->format('d') }}</div>
            <div class="badge-label">{{ now()->translatedFormat('F Y') }}</div>
        </div>
    </div>
</div>

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f5f0; color:#2d9e72;">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val">5</div>
            <div class="stat-label">Rekomendasi Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fdf0f0; color:#e05252;">
            <i class="fas fa-bell"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val">0</div>
            <div class="stat-label">Pengingat Hari Ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0f5e8; color:#6a9e2d;">
            <i class="fas fa-heartbeat"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val">—</div>
            <div class="stat-label">Data Kesehatan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f0f5; color:#3a7abf;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <div class="stat-val">0</div>
            <div class="stat-label">Jadwal Bulan Ini</div>
        </div>
    </div>
</div>

{{-- QUICK ACCESS --}}
<div class="section-title">Akses Cepat</div>
<div class="quick-grid">
    <a href="{{ route('rekomendasi.index') }}" class="quick-card">
        <div class="qc-icon" style="background:#e8f5f0; color:#2d9e72;">
            <i class="fas fa-lightbulb"></i>
        </div>
        <span class="qc-label">Rekomendasi</span>
    </a>
    <a href="{{ route('pengingat.index') }}" class="quick-card">
        <div class="qc-icon" style="background:#fdf5e8; color:#bf8a3a;">
            <i class="fas fa-bell"></i>
        </div>
        <span class="qc-label">Pengingat</span>
    </a>
    <a href="{{ route('data-kesehatan.index') }}" class="quick-card">
        <div class="qc-icon" style="background:#fdf0f0; color:#e05252;">
            <i class="fas fa-heartbeat"></i>
        </div>
        <span class="qc-label">Data Kesehatan</span>
    </a>
    <a href="{{ route('profile.show') }}" class="quick-card">
        <div class="qc-icon" style="background:#e8f0f5; color:#3a7abf;">
            <i class="fas fa-user"></i>
        </div>
        <span class="qc-label">Profil Saya</span>
    </a>
</div>

{{-- BOTTOM: TIPS + PROFILE --}}
<div class="bottom-grid">

    {{-- Health Tips --}}
    <div class="info-card">
        <div class="section-title"><i class="fas fa-star" style="color:#f0b429; margin-right:8px;"></i>Tips Kesehatan</div>
        <div class="tip-item"><div class="tip-dot"></div>Lakukan pemeriksaan gigi setiap 6 bulan sekali untuk mencegah masalah gigi.</div>
        <div class="tip-item"><div class="tip-dot"></div>Periksa tekanan darah secara rutin, terutama jika memiliki riwayat hipertensi.</div>
        <div class="tip-item"><div class="tip-dot"></div>Konsumsi air putih minimal 8 gelas per hari untuk menjaga metabolisme tubuh.</div>
        <div class="tip-item"><div class="tip-dot"></div>Lakukan pemeriksaan mata setahun sekali untuk mendeteksi masalah penglihatan dini.</div>
    </div>

    {{-- Profile Summary --}}
    <div class="info-card">
        <div class="section-title"><i class="fas fa-user-circle" style="color:#2d9e72; margin-right:8px;"></i>Informasi Akun</div>
        <div class="profile-row">
            <div class="pr-avatar" style="{{ !empty($authProfile?->avatar_url) ? 'background:transparent;padding:0;overflow:hidden;' : '' }}">
                @if(!empty($authProfile?->avatar_url))
                    <img src="{{ $authProfile->avatar_url }}" style="width:52px;height:52px;border-radius:50%;object-fit:cover;display:block;">
                @else
                    {{ strtoupper(substr(Auth::user()->full_name ?? Auth::user()->name ?? 'U', 0, 1)) }}
                @endif
            </div>
            <div>
                <div style="font-weight:700; color:#1a3c34; font-size:15px;">
                    {{ Auth::user()->full_name ?? Auth::user()->name }}
                </div>
                <div style="font-size:12px; color:#7a9a90;">{{ ucfirst(Auth::user()->role ?? 'User') }}</div>
            </div>
        </div>
        <div class="profile-row">
            <span class="pr-label">Email</span>
            <span class="pr-val">{{ Auth::user()->email }}</span>
        </div>
        <div class="profile-row">
            <span class="pr-label">Bergabung</span>
            <span class="pr-val">{{ Auth::user()->created_at?->translatedFormat('d F Y') ?? '—' }}</span>
        </div>
        <div class="profile-row">
            <span class="pr-label">Status</span>
            <span class="pr-val" style="color:#2d9e72;">
                <i class="fas fa-circle" style="font-size:8px; margin-right:4px;"></i>Aktif
            </span>
        </div>
    </div>

</div>

@endsection
