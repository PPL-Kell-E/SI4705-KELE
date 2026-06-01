@extends('layouts.app')

@section('title', 'Target Kesehatan - MEDISLOT')
@section('page-title', 'Target Kesehatan')
@section('page-subtitle', 'Tetapkan target kesehatanmu dan pantau pencapaiannya secara rutin.')

@section('topbar-actions')
    <a href="{{ route('target-kesehatan.create') }}" class="btn-tambah">
        <i class="fas fa-plus"></i> Buat Target Baru
    </a>
@endsection

@section('extra-styles')
<style>
    .btn-tambah {
        display: inline-flex; align-items: center; gap: 8px;
        background: #1a3c34; color: #fff;
        padding: 10px 20px; border-radius: 8px;
        font-size: 13.5px; font-weight: 600;
        text-decoration: none; transition: background 0.18s;
    }
    .btn-tambah:hover { background: #2d9e72; }

    /* Stat Cards */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: #fff; border-radius: 14px;
        padding: 20px 22px;
        display: flex; align-items: center; gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .stat-icon {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .stat-icon.green  { background: #e8fff4; color: #2d9e72; }
    .stat-icon.blue   { background: #e8f4ff; color: #4a90d9; }
    .stat-icon.orange { background: #fff3e8; color: #e67e22; }
    .stat-icon.teal   { background: #e8f8f5; color: #1a3c34; }
    .stat-value { font-size: 28px; font-weight: 800; color: #1a3c34; line-height: 1; }
    .stat-label { font-size: 12.5px; color: #7a9a90; margin-top: 4px; }

    /* Two-column layout */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 24px;
        align-items: start;
    }

    /* Filter Tabs */
    .filter-tabs {
        display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .filter-tab {
        padding: 8px 20px; border-radius: 999px;
        font-size: 13.5px; font-weight: 500;
        border: 1.5px solid #d0ddd9; background: #fff;
        color: #5a7a70; cursor: pointer; text-decoration: none;
        transition: all 0.18s;
    }
    .filter-tab.active  { background: #1a3c34; color: #fff; border-color: #1a3c34; }
    .filter-tab:hover:not(.active) { border-color: #2d9e72; color: #1a3c34; }

    /* Target List */
    .target-list { display: flex; flex-direction: column; gap: 14px; }

    .target-card {
        background: #fff; border-radius: 14px;
        padding: 18px 20px;
        display: flex; align-items: center; gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        transition: box-shadow 0.18s;
    }
    .target-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

    .target-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }

    .target-body { flex: 1; min-width: 0; }
    .target-top  { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; flex-wrap: wrap; }
    .target-name { font-size: 14.5px; font-weight: 600; color: #1a3c34; }
    .target-desc { font-size: 12.5px; color: #7a9a90; margin-bottom: 8px; }
    .target-date { font-size: 12px; color: #7a9a90; display: flex; align-items: center; gap: 5px; margin-top: 6px; }
    .target-date i { color: #2d9e72; }

    .progress-wrap { margin: 8px 0 4px; }
    .progress-bar {
        height: 6px; background: #e8eeec; border-radius: 4px; overflow: hidden;
    }
    .progress-fill {
        height: 100%; border-radius: 4px; transition: width 0.4s ease;
    }
    .progress-fill.green  { background: #2d9e72; }
    .progress-fill.orange { background: #e67e22; }

    .target-right {
        display: flex; flex-direction: column;
        align-items: flex-end; gap: 8px;
        flex-shrink: 0; min-width: 90px;
    }
    .progress-pct { font-size: 20px; font-weight: 800; color: #1a3c34; }
    .progress-detail { font-size: 12px; color: #7a9a90; }
    .progress-success { font-size: 12.5px; color: #2d9e72; font-weight: 500; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 600;
    }
    .badge-on-track        { background: #d4ede3; color: #2d9e72; }
    .badge-perlu-perhatian { background: #fff3e8; color: #e67e22; }
    .badge-tercapai        { background: #e8f4ff; color: #2563eb; }

    .chevron-btn {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #7a9a90; font-size: 13px;
        text-decoration: none; transition: all 0.18s;
        flex-shrink: 0;
    }
    .chevron-btn:hover { background: #f0f4f3; color: #1a3c34; }

    /* Right column - Pencapaian */
    .section-card {
        background: #fff; border-radius: 16px; padding: 22px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .section-header {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 16px;
    }
    .section-title { font-size: 15px; font-weight: 700; color: #1a3c34; }
    .section-link  { font-size: 13px; color: #2d9e72; text-decoration: none; font-weight: 500; }
    .section-link:hover { text-decoration: underline; }

    .pencapaian-card {
        display: flex; gap: 14px; align-items: flex-start;
        padding: 14px; background: #f8fbfa; border-radius: 12px;
    }
    .pencapaian-badge {
        width: 54px; height: 54px; border-radius: 50%;
        background: #2d9e72; display: flex; align-items: center;
        justify-content: center; flex-shrink: 0;
    }
    .pencapaian-badge i { color: #fff; font-size: 24px; }
    .pencapaian-name { font-size: 14px; font-weight: 700; color: #1a3c34; margin-bottom: 4px; }
    .pencapaian-desc { font-size: 12.5px; color: #5a7a70; margin-bottom: 8px; }
    .pencapaian-date { font-size: 12px; color: #7a9a90; display: flex; align-items: center; gap: 5px; }
    .pencapaian-date i { color: #2d9e72; }

    .empty-state {
        text-align: center; padding: 50px 20px; color: #7a9a90;
        display: flex; flex-direction: column; align-items: center;
    }
    .empty-state i { font-size: 44px; margin-bottom: 16px; color: #c8ddd7; display: block; }
    .empty-state p { font-size: 14.5px; margin-bottom: 0; }
    .empty-pencapaian { text-align: center; padding: 20px 0; color: #b0c5bf; font-size: 13px; }

    /* Success Modal */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.35); z-index: 999;
        align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex; }
    .modal-box {
        background: #fff; border-radius: 20px;
        padding: 40px 48px; text-align: center;
        max-width: 360px; width: 90%;
    }
    .modal-icon {
        width: 90px; height: 90px; background: #2d9e72;
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; margin: 0 auto 20px;
    }
    .modal-icon i { color: #fff; font-size: 42px; }
    .modal-msg { font-size: 17px; font-weight: 600; color: #1a3c34; margin-bottom: 24px; }
    .btn-continue {
        background: #1a3c34; color: #fff; border: none;
        padding: 12px 40px; border-radius: 999px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        transition: background 0.18s;
    }
    .btn-continue:hover { background: #2d9e72; }

    /* Confirm Delete Modal */
    .confirm-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 999;
        align-items: center; justify-content: center;
    }
    .confirm-overlay.active { display: flex; }
    .confirm-box {
        background: #fff; border-radius: 16px;
        padding: 32px 36px; text-align: center;
        max-width: 340px; width: 90%;
    }
    .confirm-box i { font-size: 40px; color: #e74c3c; margin-bottom: 12px; display: block; }
    .confirm-box p  { font-size: 15px; color: #1a3c34; font-weight: 600; margin-bottom: 6px; }
    .confirm-box small { font-size: 13px; color: #7a9a90; display: block; margin-bottom: 24px; }
    .confirm-actions { display: flex; gap: 12px; justify-content: center; }
    .btn-batal-kecil {
        padding: 10px 24px; border-radius: 999px;
        border: 1.5px solid #d0ddd9; background: #fff;
        color: #5a7a70; font-size: 14px; font-weight: 500;
        cursor: pointer; font-family: inherit;
    }
    .btn-hapus-confirm {
        padding: 10px 24px; border-radius: 999px;
        border: none; background: #e74c3c;
        color: #fff; font-size: 14px; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }

    .data-timestamp {
        font-size: 12px; color: #a0b8b0; margin-top: 28px;
        display: flex; align-items: center; gap: 5px;
    }
</style>
@endsection

@section('content')

{{-- Success Modal --}}
@if(session('success'))
    <div id="successModal" class="modal-overlay active">
        <div class="modal-box">
            <div class="modal-icon"><i class="fas fa-check"></i></div>
            <p class="modal-msg">{{ session('success') }}</p>
            <button onclick="document.getElementById('successModal').classList.remove('active')" class="btn-continue">Tutup</button>
        </div>
    </div>
@endif

{{-- Confirm Delete Modal --}}
<div id="confirmModal" class="confirm-overlay">
    <div class="confirm-box">
        <i class="fas fa-trash-alt"></i>
        <p>Hapus target ini?</p>
        <small>Tindakan ini tidak dapat dibatalkan.</small>
        <div class="confirm-actions">
            <button class="btn-batal-kecil" onclick="closeConfirm()">Batal</button>
            <button class="btn-hapus-confirm" onclick="submitDelete()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="deleteForm" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>

{{-- Stat Cards --}}
<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-bullseye"></i></div>
        <div>
            <div class="stat-value">{{ $total }}</div>
            <div class="stat-label">Total Target<br>Target aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value">{{ $onTrack }}</div>
            <div class="stat-label">On Track<br>Sesuai rencana</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
        <div>
            <div class="stat-value">{{ $perluPerhatian }}</div>
            <div class="stat-label">Perlu Perhatian<br>Butuh peningkatan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-trophy"></i></div>
        <div>
            <div class="stat-value">{{ $tercapai }}</div>
            <div class="stat-label">Tercapai<br>{{ $tercapai > 0 ? 'Selamat! 🎉' : 'Terus semangat!' }}</div>
        </div>
    </div>
</div>

{{-- Two Column --}}
<div class="content-grid">

    {{-- Left: Target List --}}
    <div>
        <div class="filter-tabs">
            <a href="{{ route('target-kesehatan.index') }}"
               class="filter-tab {{ $filter === 'semua' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('target-kesehatan.index', ['filter' => 'on-track']) }}"
               class="filter-tab {{ $filter === 'on-track' ? 'active' : '' }}">On Track</a>
            <a href="{{ route('target-kesehatan.index', ['filter' => 'perlu-perhatian']) }}"
               class="filter-tab {{ $filter === 'perlu-perhatian' ? 'active' : '' }}">Perlu Perhatian</a>
            <a href="{{ route('target-kesehatan.index', ['filter' => 'tercapai']) }}"
               class="filter-tab {{ $filter === 'tercapai' ? 'active' : '' }}">Tercapai</a>
        </div>

        @if($targets->isEmpty())
            <div class="empty-state">
                <i class="fas fa-bullseye"></i>
                @if($filter !== 'semua')
                    <p>Tidak ada target dengan status <strong>{{ ucfirst(str_replace('-', ' ', $filter)) }}</strong>.</p>
                    <a href="{{ route('target-kesehatan.index') }}" style="color:#2d9e72;font-size:14px;">Lihat Semua</a>
                @else
                    <p>Belum ada target kesehatan. Mulai perjalanan sehatmu!</p>
                    <a href="{{ route('target-kesehatan.create') }}" class="btn-tambah" style="margin-top:16px;">
                        <i class="fas fa-plus"></i> Buat Target Baru
                    </a>
                @endif
            </div>
        @else
            <div class="target-list">
                @foreach($targets as $target)
                @php
                    $progress = $target->progress;
                    $isOrange = $target->status === 'perlu-perhatian';
                @endphp
                <div class="target-card">
                    <div class="target-icon" style="background:{{ $target->icon_bg }};color:{{ $target->icon_color }}">
                        <i class="{{ $target->icon }}"></i>
                    </div>

                    <div class="target-body">
                        <div class="target-top">
                            <span class="target-name">{{ $target->nama }}</span>
                            <span class="status-badge badge-{{ $target->status }}">
                                @if($target->status === 'on-track')
                                    On Track
                                @elseif($target->status === 'perlu-perhatian')
                                    Perlu Perhatian
                                @else
                                    Tercapai
                                @endif
                            </span>
                        </div>
                        @if($target->deskripsi)
                            <div class="target-desc">{{ $target->deskripsi }}</div>
                        @endif
                        <div class="progress-wrap">
                            <div class="progress-bar">
                                <div class="progress-fill {{ $isOrange ? 'orange' : 'green' }}"
                                     style="width:{{ $progress }}%"></div>
                            </div>
                        </div>
                        <div class="target-date">
                            <i class="fas fa-calendar-alt"></i>
                            Target: {{ $target->tanggal_target->locale('id')->isoFormat('D MMM YYYY') }}
                        </div>
                    </div>

                    <div class="target-right">
                        <div class="progress-pct">{{ $progress }}%</div>
                        @if($target->status === 'tercapai')
                            <div class="progress-success">Target tercapai! 🎉</div>
                        @else
                            <div class="progress-detail">
                                {{ $target->aktivitas_dilakukan }}/{{ $target->target_aktivitas }} {{ $target->satuan }}
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('target-kesehatan.edit', $target) }}" class="chevron-btn">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                @endforeach
            </div>

            {{-- Buat Target Baru Banner --}}
            <div style="background:#f8fbfa;border-radius:14px;padding:18px 20px;display:flex;align-items:center;justify-content:space-between;margin-top:20px;gap:16px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:36px;height:36px;border-radius:50%;border:2px dashed #2d9e72;display:flex;align-items:center;justify-content:center;color:#2d9e72;font-size:16px;">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1a3c34;">Ingin membuat target baru?</div>
                        <div style="font-size:12.5px;color:#7a9a90;">Mulai perjalanan sehatmu dengan menetapkan target kesehatan baru.</div>
                    </div>
                </div>
                <a href="{{ route('target-kesehatan.create') }}" class="btn-tambah" style="white-space:nowrap;flex-shrink:0;">
                    <i class="fas fa-plus"></i> Buat Target Baru
                </a>
            </div>
        @endif
    </div>

    {{-- Right: Pencapaian Terbaru --}}
    <div>
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">Pencapaian Terbaru</div>
                <a href="{{ route('target-kesehatan.index', ['filter' => 'tercapai']) }}" class="section-link">
                    Lihat Semua <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </div>

            @if($pencapaianTerbaru)
                <div class="pencapaian-card">
                    <div class="pencapaian-badge">
                        <i class="fas fa-star"></i>
                    </div>
                    <div>
                        <div class="pencapaian-name">Target Tercapai!</div>
                        <div class="pencapaian-desc">Selamat! Kamu telah mencapai target {{ $pencapaianTerbaru->nama }}.</div>
                        <div class="pencapaian-date">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $pencapaianTerbaru->tercapai_at->locale('id')->isoFormat('D MMM YYYY') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-pencapaian">
                    <i class="fas fa-medal" style="font-size:32px;display:block;margin-bottom:10px;color:#c8ddd7;"></i>
                    Belum ada pencapaian.<br>Terus semangat! 💪
                </div>
            @endif
        </div>
    </div>

</div>

<div class="data-timestamp">
    <i class="fas fa-info-circle"></i>
    Data terakhir diperbarui: {{ now()->locale('id')->isoFormat('D MMM YYYY, HH.mm') }} WIB
</div>

@endsection

@section('scripts')
<script>
let deleteTargetId = null;

function confirmDelete(id) {
    deleteTargetId = id;
    document.getElementById('confirmModal').classList.add('active');
}
function closeConfirm() {
    document.getElementById('confirmModal').classList.remove('active');
    deleteTargetId = null;
}
function submitDelete() {
    if (!deleteTargetId) return;
    const form = document.getElementById('deleteForm');
    form.action = '/target-kesehatan/' + deleteTargetId;
    form.submit();
}
document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});
</script>
@endsection
