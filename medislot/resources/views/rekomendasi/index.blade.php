@extends('layouts.app')

@section('title', 'Rekomendasi Pemeriksaan - MEDISLOT')
@section('page-title', 'Rekomendasi Pemeriksaan')
@section('page-subtitle', 'Kelola dan tambahkan rekomendasi pemeriksaan kesehatanmu sendiri.')

@section('extra-styles')
<style>
.rec-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.btn-add {
    display:inline-flex; align-items:center; gap:8px;
    background:#1a3c34; color:#fff; border:none; border-radius:10px;
    padding:10px 20px; font-size:13.5px; font-weight:600; cursor:pointer;
    font-family:inherit; text-decoration:none; transition:background 0.18s;
}
.btn-add:hover { background:#2d9e72; }

.rec-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; }
.rec-card {
    background:#fff; border-radius:14px; padding:22px;
    box-shadow:0 1px 4px rgba(0,0,0,0.06);
    display:flex; flex-direction:column; gap:14px;
}
.rec-card-top { display:flex; align-items:flex-start; gap:14px; }
.rec-icon {
    width:50px; height:50px; border-radius:13px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.rec-name { font-size:14.5px; font-weight:700; color:#1a3c34; margin-bottom:4px; }
.rec-desc { font-size:12.5px; color:#7a9a90; line-height:1.45; }
.rec-interval {
    display:inline-flex; align-items:center; gap:6px;
    background:#f0f4f3; color:#4a6a60; font-size:12px; font-weight:600;
    padding:4px 10px; border-radius:20px;
}
.rec-interval i { font-size:11px; color:#2d9e72; }
.rec-actions { display:flex; gap:8px; margin-top:auto; }
.btn-sm {
    flex:1; display:inline-flex; align-items:center; justify-content:center; gap:5px;
    padding:8px 12px; border-radius:8px; font-size:12.5px; font-weight:600;
    cursor:pointer; border:none; font-family:inherit; text-decoration:none; transition:opacity 0.15s;
}
.btn-sm:hover { opacity:0.85; }
.btn-jadwal { background:#e8fff4; color:#2d9e72; }
.btn-edit   { background:#e8f4ff; color:#4a90d9; }
.btn-delete { background:#fde8e8; color:#e74c3c; }
.rec-default-badge {
    font-size:10.5px; color:#7a9a90; font-weight:500;
    padding:2px 8px; background:#f0f4f3; border-radius:10px;
}

/* MODAL */
.modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4);
    z-index:500; align-items:center; justify-content:center;
}
.modal-overlay.open { display:flex; }
.modal-box {
    background:#fff; border-radius:16px; padding:28px;
    width:100%; max-width:460px; box-shadow:0 8px 32px rgba(0,0,0,0.15);
}
.modal-title { font-size:16px; font-weight:700; color:#1a3c34; margin-bottom:20px; }
.form-group { margin-bottom:16px; }
.form-label { font-size:13px; font-weight:600; color:#4a6a60; margin-bottom:6px; display:block; }
.form-input, .form-textarea {
    width:100%; border:1.5px solid #dce8e4; border-radius:9px;
    padding:10px 14px; font-size:13.5px; color:#1a3c34; font-family:inherit;
    outline:none; transition:border-color 0.18s; background:#fff;
}
.form-input:focus, .form-textarea:focus { border-color:#2d9e72; }
.form-textarea { resize:vertical; min-height:80px; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.modal-footer { display:flex; gap:10px; margin-top:20px; justify-content:flex-end; }
.btn-cancel {
    padding:10px 20px; border-radius:9px; background:#f0f4f3; color:#4a6a60;
    font-size:13.5px; font-weight:600; border:none; cursor:pointer; font-family:inherit;
}
.btn-save {
    padding:10px 20px; border-radius:9px; background:#1a3c34; color:#fff;
    font-size:13.5px; font-weight:600; border:none; cursor:pointer; font-family:inherit;
    transition:background 0.18s;
}
.btn-save:hover { background:#2d9e72; }

.alert-success {
    background:#e8fff4; border:1px solid #a8dfc4; color:#1a7a4e;
    border-radius:10px; padding:12px 16px; margin-bottom:20px;
    display:flex; align-items:center; gap:8px; font-size:13.5px; font-weight:500;
}
</style>
@endsection

@section('topbar-actions')
<button class="btn-add" onclick="openAddModal()">
    <i class="fas fa-plus"></i> Tambah Rekomendasi
</button>
@endsection

@section('content')

@if(session('success'))
<div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif

<div class="rec-grid">
    @forelse($recommendations as $rec)
    <div class="rec-card">
        <div class="rec-card-top">
            <div class="rec-icon" style="background:{{ $rec->bg_color }};color:{{ $rec->icon_color }};">
                <i class="fas {{ $rec->icon }}"></i>
            </div>
            <div>
                <div class="rec-name">
                    {{ $rec->nama }}
                    @if($rec->is_default)
                        <span class="rec-default-badge">Default</span>
                    @endif
                </div>
                <div class="rec-desc">{{ $rec->deskripsi ?? '-' }}</div>
            </div>
        </div>

        <div>
            <div class="rec-interval">
                <i class="fas fa-clock"></i> Setiap {{ $rec->interval_label }}
            </div>
        </div>

        <div class="rec-actions">
            <a href="{{ route('jadwal.create') }}" class="btn-sm btn-jadwal">
                <i class="fas fa-calendar-plus"></i> Jadwalkan
            </a>
            <button class="btn-sm btn-edit" onclick="openEditModal({{ $rec->id }}, '{{ addslashes($rec->nama) }}', '{{ addslashes($rec->deskripsi ?? '') }}', {{ $rec->interval_days }}, '{{ addslashes($rec->interval_label) }}')">
                <i class="fas fa-pen"></i> Edit
            </button>
            <form method="POST" action="{{ route('rekomendasi.destroy', $rec) }}" onsubmit="return confirm('Hapus rekomendasi ini?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-sm btn-delete">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:48px;color:#7a9a90;">
        <i class="fas fa-clipboard-list" style="font-size:36px;opacity:0.3;display:block;margin-bottom:12px;"></i>
        Belum ada rekomendasi. Tambahkan rekomendasi pertamamu!
    </div>
    @endforelse
</div>

{{-- MODAL TAMBAH --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-plus-circle" style="color:#2d9e72;"></i> Tambah Rekomendasi</div>
        <form method="POST" action="{{ route('rekomendasi.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Nama Pemeriksaan *</label>
                <input type="text" name="nama" class="form-input" placeholder="contoh: Pemeriksaan Ginjal" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-textarea" placeholder="Keterangan singkat..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Interval (hari) *</label>
                    <input type="number" name="interval_days" class="form-input" placeholder="180" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Label Interval *</label>
                    <input type="text" name="interval_label" class="form-input" placeholder="6 bulan" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-pen" style="color:#4a90d9;"></i> Edit Rekomendasi</div>
        <form method="POST" id="editForm" action="">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Nama Pemeriksaan *</label>
                <input type="text" name="nama" id="editNama" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="editDeskripsi" class="form-textarea"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Interval (hari) *</label>
                    <input type="number" name="interval_days" id="editDays" class="form-input" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Label Interval *</label>
                    <input type="text" name="interval_label" id="editLabel" class="form-input" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('open');
}
function openEditModal(id, nama, deskripsi, days, label) {
    document.getElementById('editForm').action = '/rekomendasi/' + id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editDeskripsi').value = deskripsi;
    document.getElementById('editDays').value = days;
    document.getElementById('editLabel').value = label;
    document.getElementById('editModal').classList.add('open');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === this) this.classList.remove('open'); });
});
</script>
@endsection
