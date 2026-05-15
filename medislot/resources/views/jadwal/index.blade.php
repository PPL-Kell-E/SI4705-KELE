@extends('layouts.app')

@section('title', 'Jadwal Saya - MEDISLOT')
@section('page-title', 'Jadwal Saya')
@section('page-subtitle', 'Kelola janji temu dan jadwal pemeriksaan medis')

@section('topbar-actions')
    <a href="{{ route('jadwal.create') }}" class="btn-tambah">
        <i class="fas fa-plus"></i> Tambah Jadwal
    </a>
@endsection

@section('extra-styles')
<style>
    .btn-tambah {
        display: inline-flex; align-items: center; gap: 8px;
        background: #1a3c34; color: #fff;
        padding: 9px 18px; border-radius: 8px;
        font-size: 13.5px; font-weight: 600;
        text-decoration: none; transition: background 0.18s;
    }
    .btn-tambah:hover { background: #2d9e72; }

    .filter-tabs {
        display: flex; gap: 10px; margin-bottom: 24px;
    }
    .filter-tab {
        padding: 8px 24px; border-radius: 999px;
        font-size: 14px; font-weight: 500;
        border: 1.5px solid #d0ddd9; background: #fff;
        color: #5a7a70; cursor: pointer; text-decoration: none;
        transition: all 0.18s;
    }
    .filter-tab.active { background: #1a3c34; color: #fff; border-color: #1a3c34; }
    .filter-tab:hover:not(.active) { border-color: #2d9e72; color: #1a3c34; }

    .jadwal-list { display: flex; flex-direction: column; gap: 16px; }

    .jadwal-card {
        background: #fff; border-radius: 14px;
        display: flex; align-items: stretch;
        overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }

    .date-card {
        background: #d4ede3; min-width: 100px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 20px 16px; text-align: center; gap: 2px;
    }
    .date-day { font-size: 32px; font-weight: 700; color: #1a3c34; line-height: 1; }
    .date-month { font-size: 14px; font-weight: 600; color: #1a3c34; text-transform: uppercase; }
    .date-year { font-size: 13px; font-weight: 500; color: #3a7a60; }

    .jadwal-body { flex: 1; padding: 16px 20px; display: flex; flex-direction: column; gap: 6px; }
    .jadwal-header { display: flex; align-items: center; gap: 12px; }
    .jadwal-title { font-size: 15px; font-weight: 600; color: #1a3c34; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 500;
    }
    .status-mendatang { background: #dbeeff; color: #2563eb; }
    .status-selesai   { background: #d4ede3; color: #1a3c34; }
    .status-batal     { background: #fee2e2; color: #dc2626; }

    .jadwal-meta { display: flex; gap: 20px; align-items: center; }
    .jadwal-meta span { font-size: 13px; color: #5a7a70; display: flex; align-items: center; gap: 5px; }
    .jadwal-meta i { color: #2d9e72; }

    .jadwal-catatan {
        background: #f5f8f7; border-radius: 8px;
        padding: 8px 14px; font-size: 13px;
        color: #5a7a70; font-style: italic;
        margin-top: 4px;
    }

    .jadwal-actions { display: flex; flex-direction: column; gap: 8px; padding: 16px 16px 16px 0; justify-content: center; }

    .btn-edit, .btn-hapus {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 18px; border-radius: 8px;
        font-size: 13px; font-weight: 500;
        border: 1.5px solid; cursor: pointer; text-decoration: none;
        transition: all 0.18s; white-space: nowrap;
    }
    .btn-edit  { border-color: #d0ddd9; color: #1a3c34; background: #fff; }
    .btn-edit:hover { background: #f0f4f3; }
    .btn-hapus { border-color: #fca5a5; color: #dc2626; background: #fff; }
    .btn-hapus:hover { background: #fee2e2; }

    .empty-state {
        text-align: center; padding: 60px 20px; color: #7a9a90;
    }
    .empty-state i { font-size: 48px; margin-bottom: 16px; color: #c8ddd7; }
    .empty-state p { font-size: 15px; }
</style>
@endsection

@section('content')

@if(session('success'))
    <div id="successModal" class="modal-overlay active">
        <div class="modal-box">
            <div class="modal-icon"><i class="fas fa-check"></i></div>
            <p class="modal-msg">{{ session('success') }}</p>
            <button onclick="document.getElementById('successModal').classList.remove('active')" class="btn-continue">Continue</button>
        </div>
    </div>
    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:999; align-items:center; justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal-box { background:#fff; border-radius:20px; padding:40px 48px; text-align:center; max-width:360px; width:90%; }
        .modal-icon { width:90px; height:90px; background:#2d9e72; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
        .modal-icon i { color:#fff; font-size:42px; }
        .modal-msg { font-size:17px; font-weight:600; color:#1a3c34; margin-bottom:24px; }
        .btn-continue { background:#1a3c34; color:#fff; border:none; padding:12px 40px; border-radius:999px; font-size:15px; font-weight:600; cursor:pointer; transition:background 0.18s; }
        .btn-continue:hover { background:#2d9e72; }
    </style>
@endif

<div class="filter-tabs">
    <a href="{{ route('jadwal.index') }}"
       class="filter-tab {{ $filter === 'semua' ? 'active' : '' }}">Semua</a>
    <a href="{{ route('jadwal.index', ['filter' => 'mendatang']) }}"
       class="filter-tab {{ $filter === 'mendatang' ? 'active' : '' }}">Mendatang</a>
    <a href="{{ route('jadwal.index', ['filter' => 'selesai']) }}"
       class="filter-tab {{ $filter === 'selesai' ? 'active' : '' }}">Selesai / Batal</a>
</div>

@if($jadwals->isEmpty())
    <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <p>Belum ada jadwal. Klik <strong>Tambah Jadwal</strong> untuk membuat jadwal baru.</p>
    </div>
@else
    <div class="jadwal-list">
        @foreach($jadwals as $jadwal)
        <div class="jadwal-card">
            <div class="date-card">
                <div class="date-day">{{ $jadwal->tanggal->format('d') }}</div>
                <div class="date-month">{{ strtoupper($jadwal->tanggal->translatedFormat('M')) }}</div>
                <div class="date-year">{{ $jadwal->tanggal->format('Y') }}</div>
            </div>

            <div class="jadwal-body">
                <div class="jadwal-header">
                    <span class="jadwal-title">{{ $jadwal->jenis_pemeriksaan }}</span>
                    @if($jadwal->status === 'mendatang')
                        <span class="status-badge status-mendatang"><i class="fas fa-clock"></i> Mendatang</span>
                    @elseif($jadwal->status === 'selesai')
                        <span class="status-badge status-selesai"><i class="fas fa-check-circle"></i> Selesai</span>
                    @else
                        <span class="status-badge status-batal"><i class="fas fa-times-circle"></i> Batal</span>
                    @endif
                </div>

                <div class="jadwal-meta">
                    <span><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($jadwal->waktu)->format('H.i') }} WIB</span>
                    <span><i class="fas fa-map-marker-alt"></i> {{ $jadwal->fasilitas_klinik }}</span>
                </div>

                @if($jadwal->catatan)
                    <div class="jadwal-catatan">"{{ $jadwal->catatan }}"</div>
                @endif
            </div>

            <div class="jadwal-actions">
                <a href="{{ route('jadwal.edit', $jadwal) }}" class="btn-edit">
                    <i class="fas fa-pen"></i> Edit
                </a>
                <form action="{{ route('jadwal.destroy', $jadwal) }}" method="POST"
                      onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-hapus">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection
