@extends('layouts.app')

@section('title', 'Riwayat Kesehatan - MEDISLOT')
@section('page-title', 'Riwayat Kesehatan')
@section('page-subtitle', 'Garis waktu perjalanan kesehatan Anda')

@section('extra-styles')
<style>
/* ── LAYOUT ── */
.riwayat-wrap { max-width: 780px; }

/* ── FILTER BAR ── */
.filter-bar {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-bottom: 28px; align-items: center;
}
.filter-tab {
    padding: 7px 18px; border-radius: 999px;
    font-size: 13px; font-weight: 500;
    border: 1.5px solid #d0ddd9; background: #fff;
    color: #5a7a70; cursor: pointer; text-decoration: none;
    transition: all 0.18s; white-space: nowrap;
}
.filter-tab.active { background: #1a3c34; color: #fff; border-color: #1a3c34; }
.filter-tab:hover:not(.active) { border-color: #2d9e72; color: #1a3c34; }

/* custom range */
.custom-range-form {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
}
.custom-range-form input[type="date"] {
    padding: 6px 10px; border: 1.5px solid #d0ddd9; border-radius: 8px;
    font-size: 13px; color: #1a3c34; font-family: inherit; outline: none;
    transition: border-color 0.18s;
}
.custom-range-form input[type="date"]:focus { border-color: #2d9e72; }
.btn-custom-apply {
    padding: 7px 16px; background: #1a3c34; color: #fff;
    border: none; border-radius: 8px; font-size: 13px;
    font-weight: 600; cursor: pointer; font-family: inherit;
    transition: background 0.18s;
}
.btn-custom-apply:hover { background: #2d9e72; }

/* ── TOAST / NOTIF ── */
.toast {
    position: fixed; bottom: 28px; right: 28px; z-index: 999;
    padding: 12px 20px; border-radius: 12px;
    font-size: 13.5px; font-weight: 500;
    display: flex; align-items: center; gap: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    animation: slideUp 0.3s ease;
}
.toast-success { background: #1a3c34; color: #fff; }
.toast-error   { background: #dc2626; color: #fff; }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* ── TIMELINE ── */
.timeline { position: relative; padding-left: 28px; }
.timeline::before {
    content: ''; position: absolute;
    left: 7px; top: 0; bottom: 0;
    width: 2px; background: #d4ede3;
}

.tl-item { position: relative; margin-bottom: 24px; }
.tl-dot {
    position: absolute; left: -24px; top: 18px;
    width: 12px; height: 12px; border-radius: 50%;
    background: #2d9e72; border: 2px solid #fff;
    box-shadow: 0 0 0 2px #2d9e72;
}

/* ── RIWAYAT CARD ── */
.riwayat-card {
    background: #fff; border-radius: 14px;
    border-left: 4px solid #2d9e72;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    overflow: hidden; transition: box-shadow 0.18s;
    cursor: pointer;
}
.riwayat-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.1); }
.riwayat-card.is-open { box-shadow: 0 4px 18px rgba(45,158,114,0.15); }

.card-header {
    padding: 16px 20px; display: flex;
    align-items: flex-start; justify-content: space-between; gap: 12px;
}
.card-header-left { flex: 1; }
.date-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #2d9e72; color: #fff;
    font-size: 11.5px; font-weight: 600;
    padding: 4px 10px; border-radius: 999px;
    margin-bottom: 8px;
}
.date-badge i { font-size: 10px; }
.card-title { font-size: 15px; font-weight: 700; color: #1a3c34; margin-bottom: 3px; }
.card-doctor { font-size: 13px; color: #5a7a70; }
.card-actions {
    display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.btn-toggle-detail {
    width: 30px; height: 30px; border-radius: 50%;
    border: 1.5px solid #e8eeec; background: #f8faf9;
    display: flex; align-items: center; justify-content: center;
    color: #7a9a90; font-size: 12px; cursor: pointer;
    transition: all 0.18s;
}
.btn-toggle-detail:hover { background: #e8f5f0; border-color: #2d9e72; color: #2d9e72; }
.riwayat-card.is-open .btn-toggle-detail { transform: rotate(180deg); }
.btn-hapus-riwayat {
    width: 30px; height: 30px; border-radius: 50%;
    border: 1.5px solid #fca5a5; background: #fff0f0;
    display: flex; align-items: center; justify-content: center;
    color: #dc2626; font-size: 12px; cursor: pointer;
    transition: all 0.18s;
}
.btn-hapus-riwayat:hover { background: #fee2e2; }

.card-summary {
    margin: 0 20px 16px;
    background: #f5f8f7; border-radius: 8px;
    padding: 10px 14px; font-size: 13px;
    color: #5a7a70; line-height: 1.55;
}

/* ── DETAIL PANEL ── */
.card-detail {
    display: none; border-top: 1px solid #f0f4f3;
    padding: 18px 20px; background: #fafcfb;
    animation: fadeIn 0.2s ease;
}
.riwayat-card.is-open .card-detail { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.detail-item {}
.detail-label { font-size: 11px; font-weight: 700; color: #7a9a90; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 4px; }
.detail-value { font-size: 13.5px; color: #1a3c34; font-weight: 500; }
.detail-block { margin-top: 14px; }
.detail-text-box {
    background: #fff; border: 1.5px solid #e8eeec; border-radius: 9px;
    padding: 12px 14px; font-size: 13px; color: #3a5a50;
    line-height: 1.6; margin-top: 6px;
}

/* ── EMPTY / ERROR ── */
.empty-state {
    text-align: center; padding: 64px 20px; color: #94a3b8;
}
.empty-state i { font-size: 52px; opacity: 0.3; display: block; margin-bottom: 16px; }
.empty-state p { font-size: 14px; }
.empty-state a {
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 20px; background: #1a3c34; color: #fff;
    padding: 10px 24px; border-radius: 99px;
    text-decoration: none; font-size: 14px; font-weight: 600;
}

.pg-error {
    background: #fff0f0; border: 1px solid #fca5a5; border-radius: 12px;
    padding: 16px 20px; color: #dc2626; font-size: 13.5px;
    display: flex; align-items: center; gap: 10px; margin-bottom: 20px;
}

/* ── CONFIRM MODAL ── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.4); z-index: 500;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: #fff; border-radius: 18px;
    padding: 32px 28px; max-width: 380px; width: 90%;
    text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}
.modal-icon {
    width: 64px; height: 64px; border-radius: 50%;
    background: #fee2e2; display: flex; align-items: center;
    justify-content: center; margin: 0 auto 16px;
    font-size: 28px; color: #dc2626;
}
.modal-title { font-size: 17px; font-weight: 700; color: #1a3c34; margin-bottom: 8px; }
.modal-desc  { font-size: 13px; color: #7a9a90; margin-bottom: 24px; line-height: 1.55; }
.modal-actions { display: flex; gap: 10px; justify-content: center; }
.btn-modal-cancel {
    padding: 10px 24px; border-radius: 99px;
    background: #f0f4f3; color: #4a6a60; border: none;
    font-size: 13.5px; font-weight: 600; cursor: pointer; font-family: inherit;
}
.btn-modal-confirm {
    padding: 10px 24px; border-radius: 99px;
    background: #dc2626; color: #fff; border: none;
    font-size: 13.5px; font-weight: 600; cursor: pointer; font-family: inherit;
    transition: background 0.18s;
}
.btn-modal-confirm:hover { background: #b91c1c; }

/* ── LOADING SKELETON ── */
.skeleton {
    background: linear-gradient(90deg, #e8eeec 25%, #f5f8f7 50%, #e8eeec 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.sk-card { background: #fff; border-radius: 14px; border-left: 4px solid #d4ede3; padding: 20px; margin-bottom: 24px; }
.sk-line { height: 12px; margin-bottom: 10px; }
.sk-badge { height: 22px; width: 100px; border-radius: 99px; margin-bottom: 14px; }
</style>
@endsection

@section('content')
<div class="riwayat-wrap">

{{-- TOAST --}}
@if(session('success'))
<div class="toast toast-success" id="toastMsg">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="toast toast-error" id="toastMsg">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
</div>
@endif

{{-- ERROR --}}
@if($error)
<div class="pg-error"><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
@endif

{{-- FILTER BAR --}}
<div class="filter-bar">
    <a href="{{ route('riwayat.index') }}"
       class="filter-tab {{ $filter === 'semua' ? 'active' : '' }}">Semua</a>
    <a href="{{ route('riwayat.index', ['filter' => 'minggu_ini']) }}"
       class="filter-tab {{ $filter === 'minggu_ini' ? 'active' : '' }}">Minggu Ini</a>
    <a href="{{ route('riwayat.index', ['filter' => 'bulan_ini']) }}"
       class="filter-tab {{ $filter === 'bulan_ini' ? 'active' : '' }}">Bulan Ini</a>
    <a href="{{ route('riwayat.index', ['filter' => 'tahun_ini']) }}"
       class="filter-tab {{ $filter === 'tahun_ini' ? 'active' : '' }}">Tahun Ini</a>

    {{-- Custom Range --}}
    <form method="GET" action="{{ route('riwayat.index') }}" class="custom-range-form" id="customForm">
        <input type="hidden" name="filter" value="custom">
        <input type="date" name="dari"   value="{{ $dari ?? '' }}"
               placeholder="Dari"
               onchange="document.getElementById('customForm').submit()">
        <span style="font-size:12px;color:#7a9a90;">s/d</span>
        <input type="date" name="sampai" value="{{ $sampai ?? '' }}"
               placeholder="Sampai"
               onchange="document.getElementById('customForm').submit()">
        @if($filter === 'custom')
        <button type="submit" class="btn-custom-apply">Terapkan</button>
        @endif
    </form>
</div>

{{-- TIMELINE --}}
@if($riwayat->isEmpty())
    <div class="empty-state">
        <i class="fas fa-history"></i>
        @if($filter !== 'semua')
            <p><strong>Tidak ada riwayat pemeriksaan pada rentang waktu tersebut.</strong></p>
            <p style="margin-top:6px;font-size:13px;">Coba ubah filter atau pilih <a href="{{ route('riwayat.index') }}" style="color:#2d9e72;">Semua</a>.</p>
        @else
            <p><strong>Belum ada riwayat pemeriksaan terdokumentasi.</strong></p>
            <p style="margin-top:6px;font-size:13px;">Catat hasil pemeriksaan Anda untuk mulai membangun riwayat kesehatan.</p>
            <a href="{{ route('hasil-pemeriksaan.index') }}"><i class="fas fa-plus"></i> Tambah Hasil Pemeriksaan</a>
        @endif
    </div>
@else
    <div class="timeline" id="timelineWrap">
        @foreach($riwayat as $item)
        <div class="tl-item" id="item-{{ $item->id }}">
            <div class="tl-dot"></div>
            <div class="riwayat-card" id="card-{{ $item->id }}">

                {{-- HEADER --}}
                <div class="card-header" onclick="toggleDetail({{ $item->id }})">
                    <div class="card-header-left">
                        <div class="date-badge">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $item->tanggal_pemeriksaan->translatedFormat('d M Y') }}
                        </div>
                        <div class="card-title">{{ $item->jenis_pemeriksaan }}</div>
                        @if($item->nama_dokter)
                        <div class="card-doctor">{{ $item->nama_dokter }}</div>
                        @elseif($item->fasilitas_kesehatan)
                        <div class="card-doctor">{{ $item->fasilitas_kesehatan }}</div>
                        @endif
                    </div>
                    <div class="card-actions" onclick="event.stopPropagation()">
                        <button class="btn-toggle-detail" onclick="toggleDetail({{ $item->id }})" title="Lihat detail">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <button class="btn-hapus-riwayat"
                                onclick="openConfirm({{ $item->id }})"
                                title="Hapus dari riwayat">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                {{-- RINGKASAN (selalu tampil) --}}
                @if($item->hasil_pemeriksaan)
                <div class="card-summary">
                    {{ Str::limit($item->hasil_pemeriksaan, 140) }}
                </div>
                @endif

                {{-- DETAIL (expand) --}}
                <div class="card-detail">
                    <div class="detail-grid">
                        @if($item->fasilitas_kesehatan)
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-hospital-alt"></i> Fasilitas</div>
                            <div class="detail-value">{{ $item->fasilitas_kesehatan }}</div>
                        </div>
                        @endif
                        @if($item->nama_dokter)
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-user-md"></i> Dokter</div>
                            <div class="detail-value">{{ $item->nama_dokter }}</div>
                        </div>
                        @endif
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar"></i> Tanggal</div>
                            <div class="detail-value">{{ $item->tanggal_pemeriksaan->translatedFormat('l, d M Y') }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-stethoscope"></i> Jenis</div>
                            <div class="detail-value">{{ $item->jenis_pemeriksaan }}</div>
                        </div>
                    </div>

                    @if($item->hasil_pemeriksaan)
                    <div class="detail-block">
                        <div class="detail-label"><i class="fas fa-file-medical"></i> Hasil Pemeriksaan</div>
                        <div class="detail-text-box">{{ $item->hasil_pemeriksaan }}</div>
                    </div>
                    @endif

                    @if($item->catatan_tambahan)
                    <div class="detail-block">
                        <div class="detail-label"><i class="fas fa-sticky-note"></i> Catatan Tambahan</div>
                        <div class="detail-text-box">{{ $item->catatan_tambahan }}</div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>
@endif

</div>

{{-- CONFIRM MODAL --}}
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="fas fa-trash"></i></div>
        <div class="modal-title">Hapus dari Riwayat?</div>
        <div class="modal-desc">
            Catatan ini akan disembunyikan dari halaman riwayat.<br>
            Data asli tidak akan dihapus dari database.
        </div>
        <div class="modal-actions">
            <button class="btn-modal-cancel" onclick="closeConfirm()">Batal</button>
            <form id="hideForm" method="POST" style="margin:0;">
                @csrf @method('PATCH')
                <button type="submit" class="btn-modal-confirm">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── TOGGLE DETAIL ──
function toggleDetail(id) {
    const card = document.getElementById('card-' + id);
    card.classList.toggle('is-open');
}

// ── CONFIRM HAPUS ──
function openConfirm(id) {
    const url = '/riwayat/' + id + '/hide';
    document.getElementById('hideForm').action = url;
    document.getElementById('confirmModal').classList.add('open');
}
function closeConfirm() {
    document.getElementById('confirmModal').classList.remove('open');
}
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});

// ── AUTO DISMISS TOAST ──
const toast = document.getElementById('toastMsg');
if (toast) setTimeout(() => toast.style.opacity = '0', 3500);

// ── CUSTOM DATE RANGE: highlight tab jika filter custom aktif ──
@if($filter === 'custom')
document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
@endif
</script>
@endsection
