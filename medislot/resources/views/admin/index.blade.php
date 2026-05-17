@extends('layouts.admin')

@section('title', 'Dashboard Admin - MEDISLOT')
@section('page-title', 'Dashboard Admin')
@section('page-subtitle', 'Ringkasan data sistem MediSlot')

@section('extra-styles')
<style>
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 22px 24px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .stat-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .stat-info { flex: 1; }
    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #1a3c34;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-label {
        font-size: 12.5px;
        color: #7a9a90;
        font-weight: 500;
    }

    .section-card {
        background: #fff;
        border-radius: 14px;
        padding: 24px 28px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }
    .section-card h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-card h3 i { color: #2d9e72; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
        font-size: 12px; font-weight: 600; color: #7a9a90;
        text-align: left; padding: 0 0 12px; border-bottom: 1px solid #eef2f1;
    }
    tbody tr { border-bottom: 1px solid #f5f8f7; }
    tbody tr:last-child { border-bottom: none; }
    tbody td { padding: 12px 0; font-size: 14px; color: #333; vertical-align: middle; }

    .item-icon-sm {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; margin-right: 10px; flex-shrink: 0;
    }
    .name-cell { display: flex; align-items: center; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
    .badge-aktif    { background: #d4edda; color: #155724; }
    .badge-nonaktif { background: #fde8e8; color: #a52020; }

    .quick-link {
        display: flex; align-items: center; gap: 12px;
        padding: 16px; border-radius: 12px;
        background: #f8fbfa; border: 1.5px solid #e8f0ee;
        text-decoration: none; color: #1a3c34;
        font-weight: 600; font-size: 14px;
        transition: all 0.18s; margin-bottom: 10px;
    }
    .quick-link:last-child { margin-bottom: 0; }
    .quick-link:hover { background: #e8f5f0; border-color: #2d9e72; }
    .quick-link i { color: #2d9e72; font-size: 18px; width: 24px; text-align: center; }

    .two-col { display: grid; grid-template-columns: 1fr 340px; gap: 20px; }
</style>
@endsection

@section('content')

{{-- STAT CARDS --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f5f0; color:#2d9e72;">
            <i class="fas fa-book-medical"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalKatalog }}</div>
            <div class="stat-label">Total Katalog</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#d4edda; color:#155724;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $katalogAktif }}</div>
            <div class="stat-label">Katalog Aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e8f0f5; color:#3a7abf;">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $totalUser }}</div>
            <div class="stat-label">Total Pengguna</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f5f0e8; color:#bf8a3a;">
            <i class="fas fa-tags"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value">{{ $kategoriCount }}</div>
            <div class="stat-label">Kategori</div>
        </div>
    </div>
</div>

<div class="two-col">
    {{-- KATALOG TERBARU --}}
    <div class="section-card">
        <h3><i class="fas fa-clock"></i>Katalog Terbaru</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Pemeriksaan</th>
                    <th>Kategori</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($katalogTerbaru as $item)
                <tr>
                    <td>
                        <div class="name-cell">
                            <div class="item-icon-sm" style="background:{{ $item->bg_color }};color:{{ $item->icon_color }};">
                                <i class="fas {{ $item->icon }}"></i>
                            </div>
                            <span style="font-weight:600;color:#1a3c34;">{{ $item->nama }}</span>
                        </div>
                    </td>
                    <td style="color:#7a9a90;">{{ $item->kategori }}</td>
                    <td><span class="badge badge-{{ $item->status }}">{{ $item->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:16px;">
            <a href="{{ route('katalog.index') }}" style="font-size:13px;color:#2d9e72;font-weight:600;text-decoration:none;">
                Lihat semua katalog <i class="fas fa-arrow-right" style="font-size:11px;"></i>
            </a>
        </div>
    </div>

    {{-- QUICK LINKS --}}
    <div class="section-card">
        <h3><i class="fas fa-bolt"></i>Aksi Cepat</h3>
        <a href="{{ route('katalog.index') }}" class="quick-link">
            <i class="fas fa-book-medical"></i>
            Kelola Katalog Pemeriksaan
        </a>
        <a href="{{ route('katalog.index') }}?status=nonaktif" class="quick-link">
            <i class="fas fa-eye-slash"></i>
            Lihat Katalog Nonaktif
        </a>

        <div style="margin-top:24px;padding-top:20px;border-top:1px solid #eef2f1;">
            <div style="font-size:12px;font-weight:600;color:#7a9a90;margin-bottom:12px;letter-spacing:0.5px;text-transform:uppercase;">Info Sistem</div>
            <div style="font-size:13px;color:#555;line-height:2;">
                <div>Katalog aktif: <strong>{{ $katalogAktif }}</strong></div>
                <div>Katalog nonaktif: <strong>{{ $totalKatalog - $katalogAktif }}</strong></div>
                <div>Pengguna terdaftar: <strong>{{ $totalUser }}</strong></div>
            </div>
        </div>
    </div>
</div>

@endsection
