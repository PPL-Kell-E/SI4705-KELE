@extends('layouts.app')

@section('title', 'Hasil Pemeriksaan - MEDISLOT')
@section('page-title', 'Pencatatan Hasil Pemeriksaan')
@section('page-subtitle', '')

@section('topbar-actions')
    <button onclick="openModal('createModal')" class="btn-tambah">
        <i class="fas fa-plus"></i> Tambah Hasil
    </button>
@endsection

@section('extra-styles')
<style>
    .btn-tambah {
        display: inline-flex; align-items: center; gap: 8px;
        background: #1a3c34; color: #fff;
        padding: 10px 20px; border-radius: 8px;
        font-size: 13.5px; font-weight: 600;
        border: none; cursor: pointer; font-family: inherit;
        transition: background 0.18s;
    }
    .btn-tambah:hover { background: #2d9e72; }

    /* Result Cards */
    .hasil-list { display: flex; flex-direction: column; gap: 0; }

    .hasil-card {
        background: #fff;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 20px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        transition: box-shadow 0.18s;
    }
    .hasil-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }

    .hasil-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .hasil-title {
        font-size: 20px; font-weight: 700; color: #111;
        margin-bottom: 10px;
    }
    .hasil-meta {
        display: flex; gap: 24px; align-items: center; flex-wrap: wrap;
    }
    .hasil-meta-item {
        display: flex; align-items: center; gap: 7px;
        font-size: 14px; color: #555;
    }
    .hasil-meta-item i { color: #555; font-size: 14px; }

    .hasil-quote {
        background: #f5f5f5;
        border-radius: 12px;
        padding: 16px 20px;
        margin-top: 16px;
        font-size: 14px;
        color: #333;
        line-height: 1.6;
    }

    .hasil-catatan {
        margin-top: 10px;
        font-size: 13px; color: #7a9a90;
        font-style: italic;
    }

    .card-actions {
        display: flex; gap: 8px; flex-shrink: 0;
    }
    .btn-card-edit {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 16px; border-radius: 8px;
        border: 1.5px solid #d0ddd9; background: #fff;
        color: #1a3c34; font-size: 13px; font-weight: 500;
        cursor: pointer; font-family: inherit; transition: all 0.18s;
    }
    .btn-card-edit:hover { background: #f0f4f3; }
    .btn-card-delete {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 16px; border-radius: 8px;
        border: 1.5px solid #fca5a5; background: #fff;
        color: #dc2626; font-size: 13px; font-weight: 500;
        cursor: pointer; font-family: inherit; transition: all 0.18s;
    }
    .btn-card-delete:hover { background: #fee2e2; }

    /* Empty State */
    .empty-state {
        text-align: center; padding: 80px 20px; color: #7a9a90;
    }
    .empty-state i { font-size: 52px; color: #c8ddd7; display: block; margin-bottom: 20px; }
    .empty-state h3 { font-size: 18px; font-weight: 600; color: #1a3c34; margin-bottom: 8px; }
    .empty-state p  { font-size: 14px; margin-bottom: 24px; }

    /* ── MODAL ── */
    .modal-backdrop {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.5); z-index: 900;
        align-items: center; justify-content: center;
        padding: 20px;
    }
    .modal-backdrop.active { display: flex; }

    .modal-container {
        background: #fff; border-radius: 20px;
        width: 100%; max-width: 620px;
        max-height: 90vh; overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        animation: modalIn 0.22s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-16px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-top-bar {
        height: 8px; background: #2d9e72;
        border-radius: 20px 20px 0 0;
    }

    .modal-header {
        display: flex; align-items: flex-start; gap: 18px;
        padding: 28px 32px 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    .modal-icon {
        width: 56px; height: 56px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-icon img, .modal-icon svg { width: 56px; height: 56px; }
    .modal-title { font-size: 18px; font-weight: 700; color: #111; margin-bottom: 4px; }
    .modal-subtitle { font-size: 13px; color: #7a9a90; line-height: 1.5; }

    .modal-body { padding: 24px 32px; display: flex; flex-direction: column; gap: 18px; }

    .form-group label {
        display: block; font-size: 14px; font-weight: 600;
        color: #111; margin-bottom: 8px;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid #e0e0e0; border-radius: 10px;
        font-size: 14px; color: #333; background: #fff;
        font-family: inherit; outline: none;
        transition: border-color 0.18s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #2d9e72;
        box-shadow: 0 0 0 3px rgba(45,158,114,0.1);
    }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-error { color: #dc2626; font-size: 12px; margin-top: 4px; }

    .modal-footer {
        padding: 20px 32px 28px;
        display: flex; justify-content: center; gap: 12px;
    }
    .btn-simpan {
        background: #1a3c34; color: #fff; border: none;
        padding: 14px 60px; border-radius: 12px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: background 0.18s;
        width: 100%;
    }
    .btn-simpan:hover { background: #2d9e72; }
    .btn-modal-batal {
        background: #fff; color: #555;
        border: 1.5px solid #d0ddd9;
        padding: 14px 32px; border-radius: 12px;
        font-size: 15px; font-weight: 500;
        cursor: pointer; font-family: inherit;
        transition: all 0.18s; white-space: nowrap;
    }
    .btn-modal-batal:hover { border-color: #2d9e72; color: #1a3c34; }

    /* Success Modal */
    .success-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 999;
        align-items: center; justify-content: center;
    }
    .success-overlay.active { display: flex; }
    .success-box {
        background: #fff; border-radius: 20px;
        padding: 40px 48px; text-align: center;
        max-width: 360px; width: 90%;
    }
    .success-icon {
        width: 90px; height: 90px; background: #2d9e72;
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; margin: 0 auto 20px;
    }
    .success-icon i { color: #fff; font-size: 42px; }
    .success-msg { font-size: 17px; font-weight: 600; color: #1a3c34; margin-bottom: 24px; }
    .btn-ok {
        background: #1a3c34; color: #fff; border: none;
        padding: 12px 40px; border-radius: 999px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        transition: background 0.18s;
    }
    .btn-ok:hover { background: #2d9e72; }

    /* Confirm Delete */
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
    .confirm-box .ci { font-size: 40px; color: #e74c3c; margin-bottom: 12px; display: block; }
    .confirm-box h3  { font-size: 16px; color: #1a3c34; font-weight: 600; margin-bottom: 6px; }
    .confirm-box p   { font-size: 13px; color: #7a9a90; margin-bottom: 24px; }
    .confirm-actions { display: flex; gap: 12px; justify-content: center; }
    .btn-cancel-sm {
        padding: 10px 24px; border-radius: 999px;
        border: 1.5px solid #d0ddd9; background: #fff;
        color: #5a7a70; font-size: 14px; font-weight: 500;
        cursor: pointer; font-family: inherit;
    }
    .btn-delete-sm {
        padding: 10px 24px; border-radius: 999px;
        border: none; background: #e74c3c;
        color: #fff; font-size: 14px; font-weight: 600;
        cursor: pointer; font-family: inherit;
    }
</style>
@endsection

@section('content')

{{-- Success Notification --}}
@if(session('success'))
<div id="successModal" class="success-overlay active">
    <div class="success-box">
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <p class="success-msg">{{ session('success') }}</p>
        <button class="btn-ok" onclick="document.getElementById('successModal').classList.remove('active')">OK</button>
    </div>
</div>
@endif

{{-- ── CREATE MODAL ── --}}
<div id="createModal" class="modal-backdrop">
    <div class="modal-container">
        <div class="modal-top-bar"></div>
        <div class="modal-header">
            <div class="modal-icon">
                <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="56" height="56" rx="14" fill="#e8fff4"/>
                    <path d="M28 14C28 14 18 20 18 30C18 35.5 22.5 40 28 40C33.5 40 38 35.5 38 30C38 20 28 14Z" stroke="#2d9e72" stroke-width="2" fill="none"/>
                    <circle cx="28" cy="30" r="4" fill="#2d9e72"/>
                    <path d="M28 26V20" stroke="#2d9e72" stroke-width="2" stroke-linecap="round"/>
                    <path d="M36 22L32 26" stroke="#2d9e72" stroke-width="2" stroke-linecap="round"/>
                    <path d="M36 38C36 38 39 35 40 32" stroke="#1a3c34" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="40" cy="30" r="3" fill="#1a3c34"/>
                </svg>
            </div>
            <div>
                <div class="modal-title">Pencatatan Hasil Pemeriksaan</div>
                <div class="modal-subtitle">Catat hasil pemeriksaan yang telah dilakukan untuk dipantau di riwayat kesehatan Anda</div>
            </div>
        </div>

        <form action="{{ route('hasil-pemeriksaan.store') }}" method="POST" id="createForm">
            @csrf
            <div class="modal-body">

                <div class="form-group">
                    <label>Jenis pemeriksaan</label>
                    <input type="text" name="jenis_pemeriksaan"
                           value="{{ old('jenis_pemeriksaan') }}"
                           placeholder="Contoh: Cek Darah Lengkap"
                           required>
                    @error('jenis_pemeriksaan') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Tanggal pemeriksaan</label>
                    <input type="date" name="tanggal_pemeriksaan"
                           value="{{ old('tanggal_pemeriksaan') }}"
                           required>
                    @error('tanggal_pemeriksaan') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Klinik / RS</label>
                        <input type="text" name="fasilitas_kesehatan"
                               value="{{ old('fasilitas_kesehatan') }}"
                               placeholder="Contoh: RS Medina">
                        @error('fasilitas_kesehatan') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Nama Dokter</label>
                        <input type="text" name="nama_dokter"
                               value="{{ old('nama_dokter') }}"
                               placeholder="Contoh: dr. Herman">
                        @error('nama_dokter') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Hasil Pemeriksaan</label>
                    <textarea name="hasil_pemeriksaan"
                              placeholder="Tulis ringkasan hasil pemeriksaan..."
                              rows="3" required>{{ old('hasil_pemeriksaan') }}</textarea>
                    @error('hasil_pemeriksaan') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Catatan Tambahan <span style="font-weight:400;color:#7a9a90">(opsional)</span></label>
                    <textarea name="catatan_tambahan"
                              placeholder="Rekomendasi dokter, saran tindak lanjut, dll."
                              rows="2">{{ old('catatan_tambahan') }}</textarea>
                    @error('catatan_tambahan') <div class="form-error">{{ $message }}</div> @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-batal" onclick="closeModal('createModal')">Batal</button>
                <button type="submit" class="btn-simpan" style="width:auto;padding:14px 40px;">Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT MODAL ── --}}
<div id="editModal" class="modal-backdrop">
    <div class="modal-container">
        <div class="modal-top-bar"></div>
        <div class="modal-header">
            <div class="modal-icon">
                <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="56" height="56" rx="14" fill="#e8fff4"/>
                    <path d="M28 14C28 14 18 20 18 30C18 35.5 22.5 40 28 40C33.5 40 38 35.5 38 30C38 20 28 14Z" stroke="#2d9e72" stroke-width="2" fill="none"/>
                    <circle cx="28" cy="30" r="4" fill="#2d9e72"/>
                    <path d="M28 26V20" stroke="#2d9e72" stroke-width="2" stroke-linecap="round"/>
                    <path d="M36 22L32 26" stroke="#2d9e72" stroke-width="2" stroke-linecap="round"/>
                    <path d="M36 38C36 38 39 35 40 32" stroke="#1a3c34" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="40" cy="30" r="3" fill="#1a3c34"/>
                </svg>
            </div>
            <div>
                <div class="modal-title">Edit Hasil Pemeriksaan</div>
                <div class="modal-subtitle">Perbarui data hasil pemeriksaan yang sudah dicatat</div>
            </div>
        </div>

        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="modal-body">

                <div class="form-group">
                    <label>Jenis pemeriksaan</label>
                    <input type="text" name="jenis_pemeriksaan" id="edit_jenis" required>
                </div>
                <div class="form-group">
                    <label>Tanggal pemeriksaan</label>
                    <input type="date" name="tanggal_pemeriksaan" id="edit_tanggal" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Klinik / RS</label>
                        <input type="text" name="fasilitas_kesehatan" id="edit_fasilitas" placeholder="Contoh: RS Medina">
                    </div>
                    <div class="form-group">
                        <label>Nama Dokter</label>
                        <input type="text" name="nama_dokter" id="edit_dokter" placeholder="Contoh: dr. Herman">
                    </div>
                </div>
                <div class="form-group">
                    <label>Hasil Pemeriksaan</label>
                    <textarea name="hasil_pemeriksaan" id="edit_hasil" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Catatan Tambahan <span style="font-weight:400;color:#7a9a90">(opsional)</span></label>
                    <textarea name="catatan_tambahan" id="edit_catatan" rows="2"></textarea>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-batal" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-simpan" style="width:auto;padding:14px 40px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── CONFIRM DELETE ── --}}
<div id="confirmModal" class="confirm-overlay">
    <div class="confirm-box">
        <i class="fas fa-trash-alt ci"></i>
        <h3>Hapus hasil pemeriksaan ini?</h3>
        <p>Tindakan ini tidak dapat dibatalkan.</p>
        <div class="confirm-actions">
            <button class="btn-cancel-sm" onclick="closeModal('confirmModal')">Batal</button>
            <button class="btn-delete-sm" onclick="submitDelete()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="deleteForm" method="POST" style="display:none">
    @csrf @method('DELETE')
</form>

{{-- ── CONTENT ── --}}
@if($hasilList->isEmpty())
    <div class="empty-state">
        <i class="fas fa-file-medical"></i>
        <h3>Belum ada hasil pemeriksaan</h3>
        <p>Catat hasil pemeriksaan kesehatan Anda untuk memantau kondisi kesehatan secara rutin.</p>
        <button onclick="openModal('createModal')" class="btn-tambah">
            <i class="fas fa-plus"></i> Tambah Hasil Pemeriksaan
        </button>
    </div>
@else
    <div class="hasil-list">
        @foreach($hasilList as $hasil)
        <div class="hasil-card">
            <div class="hasil-card-header">
                <div>
                    <div class="hasil-title">{{ $hasil->jenis_pemeriksaan }}</div>
                    <div class="hasil-meta">
                        <span class="hasil-meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $hasil->tanggal_pemeriksaan->locale('id')->isoFormat('D MMMM YYYY') }}
                        </span>
                        @if($hasil->nama_dokter)
                        <span class="hasil-meta-item">
                            <i class="fas fa-user-md"></i>
                            {{ $hasil->nama_dokter }}
                        </span>
                        @endif
                        @if($hasil->fasilitas_kesehatan)
                        <span class="hasil-meta-item">
                            <i class="fas fa-hospital"></i>
                            {{ $hasil->fasilitas_kesehatan }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="card-actions">
                    <button class="btn-card-edit"
                            onclick="openEdit(
                                {{ $hasil->id }},
                                {{ json_encode($hasil->jenis_pemeriksaan) }},
                                {{ json_encode($hasil->tanggal_pemeriksaan->format('Y-m-d')) }},
                                {{ json_encode($hasil->fasilitas_kesehatan ?? '') }},
                                {{ json_encode($hasil->nama_dokter ?? '') }},
                                {{ json_encode($hasil->hasil_pemeriksaan) }},
                                {{ json_encode($hasil->catatan_tambahan ?? '') }}
                            )">
                        <i class="fas fa-pen"></i> Edit
                    </button>
                    <button class="btn-card-delete"
                            onclick="confirmDelete({{ $hasil->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <div class="hasil-quote">
                "{{ $hasil->hasil_pemeriksaan }}"
            </div>

            @if($hasil->catatan_tambahan)
            <div class="hasil-catatan">
                <i class="fas fa-sticky-note"></i> {{ $hasil->catatan_tambahan }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
@endif

@endsection

@section('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on backdrop click
document.querySelectorAll('.modal-backdrop, .confirm-overlay').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// Edit modal
function openEdit(id, jenis, tanggal, fasilitas, dokter, hasil, catatan) {
    document.getElementById('editForm').action = '/hasil-pemeriksaan/' + id;
    document.getElementById('edit_jenis').value     = jenis;
    document.getElementById('edit_tanggal').value   = tanggal;
    document.getElementById('edit_fasilitas').value = fasilitas;
    document.getElementById('edit_dokter').value    = dokter;
    document.getElementById('edit_hasil').value     = hasil;
    document.getElementById('edit_catatan').value   = catatan;
    openModal('editModal');
}

// Delete
let deleteId = null;
function confirmDelete(id) {
    deleteId = id;
    openModal('confirmModal');
}
function submitDelete() {
    if (!deleteId) return;
    const form = document.getElementById('deleteForm');
    form.action = '/hasil-pemeriksaan/' + deleteId;
    form.submit();
}

// Auto-open create modal if validation failed
@if($errors->any() && old('_from') === 'create')
    openModal('createModal');
@endif
</script>
@endsection
