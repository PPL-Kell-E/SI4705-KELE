@extends('layouts.admin')

@section('title', 'Katalog Pemeriksaan - Admin MEDISLOT')
@section('page-title', 'Katalog Pemeriksaan')
@section('page-subtitle', 'Kelola data jenis pemeriksaan kesehatan')

@section('topbar-actions')
    <button onclick="openModal('createModal')" class="btn-add">
        <i class="fas fa-plus"></i> Tambah Pemeriksaan
    </button>
@endsection

@section('extra-styles')
<style>
    .btn-add {
        background: #1a3c34; color: #fff; border: none;
        border-radius: 10px; padding: 10px 20px;
        font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
        cursor: pointer; display: flex; align-items: center; gap: 8px;
        transition: background 0.18s;
    }
    .btn-add:hover { background: #2d9e72; }

    /* Alert */
    .alert { padding: 12px 18px; border-radius: 10px; margin-bottom: 18px; font-size: 14px; font-weight: 500; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* Toolbar */
    .toolbar {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; margin-bottom: 20px;
    }
    .search-box {
        display: flex; align-items: center; gap: 8px;
        background: #fff; border: 1.5px solid #e0eae6; border-radius: 10px;
        padding: 0 16px; flex: 1; max-width: 400px;
        transition: border-color 0.2s;
    }
    .search-box:focus-within { border-color: #2d9e72; }
    .search-box i { color: #7a9a90; font-size: 14px; }
    .search-box input {
        flex: 1; border: none; outline: none;
        padding: 10px 0; font-size: 14px; font-family: 'Inter', sans-serif;
        color: #333; background: transparent;
    }
    .filter-select {
        border: 1.5px solid #e0eae6; border-radius: 10px;
        padding: 10px 14px; font-size: 14px; font-family: 'Inter', sans-serif;
        color: #333; background: #fff; cursor: pointer; outline: none;
        min-width: 160px;
    }
    .filter-select:focus { border-color: #2d9e72; }

    /* Table */
    .table-wrap { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: #fff; padding: 14px 18px;
        font-size: 13px; font-weight: 600; color: #5a7a70;
        text-align: left; border-bottom: 1px solid #eef2f1;
        white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid #f2f6f5; transition: background 0.15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fbfa; }
    tbody td { padding: 14px 18px; font-size: 14px; color: #333; vertical-align: middle; }

    .item-name-cell { display: flex; align-items: center; gap: 12px; }
    .item-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .item-name { font-weight: 600; color: #1a3c34; font-size: 14px; }

    .badge {
        display: inline-block; padding: 3px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
    }
    .badge-aktif    { background: #d4edda; color: #155724; }
    .badge-nonaktif { background: #fde8e8; color: #a52020; }

    .btn-icon {
        width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 13px; cursor: pointer; background: #fff;
        transition: all 0.15s;
    }
    .btn-edit  { border-color: #2d9e72; color: #2d9e72; }
    .btn-edit:hover  { background: #2d9e72; color: #fff; }
    .btn-delete{ border-color: #e05252; color: #e05252; }
    .btn-delete:hover{ background: #e05252; color: #fff; }

    /* Pagination */
    .pagination-wrap {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 18px; border-top: 1px solid #eef2f1;
        font-size: 13px; color: #7a9a90;
    }
    .pagination-links { display: flex; align-items: center; gap: 4px; }
    .pagination-links a, .pagination-links span {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 500; text-decoration: none;
        color: #5a7a70; border: 1px solid #e0eae6; background: #fff;
        transition: all 0.15s;
    }
    .pagination-links a:hover { background: #f0f4f3; }
    .pagination-links span.active { background: #1a3c34; color: #fff; border-color: #1a3c34; }
    .pagination-links span.disabled { opacity: 0.4; pointer-events: none; }

    /* Empty state */
    .empty-row td { text-align: center; padding: 48px; color: #7a9a90; }
    .empty-row i   { font-size: 36px; opacity: 0.35; display: block; margin-bottom: 10px; }

    /* Modal backdrop */
    .modal-backdrop {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.45); z-index: 1000;
        align-items: center; justify-content: center;
    }
    .modal-backdrop.open { display: flex; }

    .modal {
        background: #fff; border-radius: 16px; width: 560px; max-width: 95vw;
        max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    }
    .modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px; border-bottom: 1px solid #eef2f1;
    }
    .modal-header h3 { font-size: 17px; font-weight: 700; color: #1a3c34; }
    .modal-close {
        width: 30px; height: 30px; border-radius: 50%; border: none;
        background: #f0f4f3; color: #7a9a90; cursor: pointer; font-size: 14px;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s;
    }
    .modal-close:hover { background: #e0eae6; }
    .modal-body { padding: 22px 24px; }
    .modal-footer { padding: 16px 24px; border-top: 1px solid #eef2f1; display: flex; gap: 10px; justify-content: flex-end; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #5a7a70; margin-bottom: 6px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; border: 1.5px solid #e0eae6; border-radius: 8px;
        padding: 10px 12px; font-size: 14px; font-family: 'Inter', sans-serif;
        color: #333; background: #fff; outline: none;
        transition: border-color 0.18s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #2d9e72; }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-error { color: #e05252; font-size: 12px; margin-top: 4px; }

    .btn-primary {
        background: #1a3c34; color: #fff; border: none; border-radius: 8px;
        padding: 10px 22px; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
        cursor: pointer; transition: background 0.18s;
    }
    .btn-primary:hover { background: #2d9e72; }
    .btn-secondary {
        background: #f0f4f3; color: #5a7a70; border: none; border-radius: 8px;
        padding: 10px 22px; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
        cursor: pointer; transition: background 0.18s;
    }
    .btn-secondary:hover { background: #e0eae6; }
    .btn-danger {
        background: #e05252; color: #fff; border: none; border-radius: 8px;
        padding: 10px 22px; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
        cursor: pointer; transition: background 0.18s;
    }
    .btn-danger:hover { background: #c03e3e; }

    .color-row { display: flex; align-items: center; gap: 8px; }
    .color-row input[type=color] {
        width: 40px; height: 38px; padding: 2px; cursor: pointer; border-radius: 6px;
    }
    .color-row input[type=text] { flex: 1; }

    .icon-preview-wrap { display: flex; align-items: center; gap: 10px; }
    .icon-preview {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
</style>
@endsection

@section('content')

{{-- Alerts --}}
@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle" style="margin-right:8px;"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error"><i class="fas fa-exclamation-circle" style="margin-right:8px;"></i>{{ $errors->first() }}</div>
@endif

{{-- Toolbar --}}
<form method="GET" action="{{ route('katalog.index') }}" id="filterForm">
<div class="toolbar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari pemeriksaan..."
               onchange="document.getElementById('filterForm').submit()">
    </div>
    <select name="kategori" class="filter-select" onchange="document.getElementById('filterForm').submit()">
        <option value="">Semua Kategori</option>
        @foreach($kategoriList as $kat)
            <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
        @endforeach
    </select>
</div>
</form>

{{-- Table --}}
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:50px;">No</th>
                <th>Nama Pemeriksaan</th>
                <th>Kategori</th>
                <th>Estimasi Durasi</th>
                <th>Estimasi Biaya</th>
                <th>Status</th>
                <th style="text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($katalog as $i => $item)
            <tr>
                <td>{{ $katalog->firstItem() + $i }}</td>
                <td>
                    <div class="item-name-cell">
                        <div class="item-icon" style="background:{{ $item->bg_color }};color:{{ $item->icon_color }};">
                            <i class="fas {{ $item->icon }}"></i>
                        </div>
                        <span class="item-name">{{ $item->nama }}</span>
                    </div>
                </td>
                <td>{{ $item->kategori }}</td>
                <td>{{ $item->durasi ?? '-' }}</td>
                <td>
                    @if($item->biaya_min || $item->biaya_max)
                        Rp {{ number_format($item->biaya_min, 0, ',', '.') }} – {{ number_format($item->biaya_max, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    <span class="badge badge-{{ $item->status }}">
                        {{ $item->status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td style="text-align:center;">
                    <button class="btn-icon btn-edit" title="Edit"
                        onclick="openEdit(
                            {{ $item->id }},
                            '{{ addslashes($item->nama) }}',
                            '{{ addslashes($item->kategori) }}',
                            '{{ addslashes($item->singkat ?? '') }}',
                            '{{ addslashes($item->deskripsi ?? '') }}',
                            '{{ $item->icon }}',
                            '{{ $item->bg_color }}',
                            '{{ $item->icon_color }}',
                            '{{ addslashes($item->durasi ?? '') }}',
                            {{ $item->biaya_min }},
                            {{ $item->biaya_max }},
                            '{{ $item->status }}',
                            {{ json_encode($item->persiapan ?? []) }}
                        )">
                        <i class="fas fa-pencil"></i>
                    </button>
                    <button class="btn-icon btn-delete" title="Hapus"
                        onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->nama) }}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr class="empty-row">
                <td colspan="7">
                    <i class="fas fa-book-medical"></i>
                    <p>Belum ada data katalog pemeriksaan.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($katalog->hasPages() || $katalog->total() > 0)
    <div class="pagination-wrap">
        <span>Menampilkan {{ $katalog->firstItem() }} sampai {{ $katalog->lastItem() }} dari {{ $katalog->total() }} data</span>
        <div class="pagination-links">
            @if($katalog->onFirstPage())
                <span class="disabled"><i class="fas fa-chevron-left" style="font-size:11px;"></i></span>
            @else
                <a href="{{ $katalog->previousPageUrl() }}"><i class="fas fa-chevron-left" style="font-size:11px;"></i></a>
            @endif

            @foreach($katalog->getUrlRange(1, $katalog->lastPage()) as $page => $url)
                @if($page == $katalog->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($katalog->hasMorePages())
                <a href="{{ $katalog->nextPageUrl() }}"><i class="fas fa-chevron-right" style="font-size:11px;"></i></a>
            @else
                <span class="disabled"><i class="fas fa-chevron-right" style="font-size:11px;"></i></span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- CREATE MODAL --}}
<div class="modal-backdrop" id="createModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-plus" style="margin-right:8px;color:#2d9e72;"></i>Tambah Pemeriksaan</h3>
            <button class="modal-close" onclick="closeModal('createModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('katalog.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Nama Pemeriksaan *</label>
                        <input type="text" name="nama" required placeholder="cth. Elektrokardiogram (EKG)" value="{{ old('nama') }}">
                    </div>
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriOptions as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ikon *</label>
                        <div class="icon-preview-wrap">
                            <div class="icon-preview" id="c-icon-preview" style="background:#e8f5f0;color:#2d9e72;">
                                <i class="fas fa-stethoscope" id="c-icon-el"></i>
                            </div>
                            <select name="icon" id="c-icon" required onchange="updateIconPreview('c')">
                                @foreach($iconOptions as $val => $label)
                                    <option value="{{ $val }}" {{ old('icon', 'fa-stethoscope') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Warna Latar Ikon *</label>
                        <div class="color-row">
                            <input type="color" id="c-bg-picker" value="#e8f5f0" oninput="document.getElementById('c-bg-color').value=this.value; updateIconPreview('c')">
                            <input type="text" name="bg_color" id="c-bg-color" value="{{ old('bg_color','#e8f5f0') }}" placeholder="#e8f5f0" oninput="document.getElementById('c-bg-picker').value=this.value; updateIconPreview('c')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Warna Ikon *</label>
                        <div class="color-row">
                            <input type="color" id="c-ic-picker" value="#2d9e72" oninput="document.getElementById('c-icon-color').value=this.value; updateIconPreview('c')">
                            <input type="text" name="icon_color" id="c-icon-color" value="{{ old('icon_color','#2d9e72') }}" placeholder="#2d9e72" oninput="document.getElementById('c-ic-picker').value=this.value; updateIconPreview('c')">
                        </div>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Deskripsi Singkat</label>
                        <input type="text" name="singkat" placeholder="Ringkasan singkat pemeriksaan..." value="{{ old('singkat') }}">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Deskripsi Lengkap</label>
                        <textarea name="deskripsi" rows="3" placeholder="Penjelasan lengkap tentang pemeriksaan...">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Persiapan <span style="font-weight:400;color:#aaa;">(satu item per baris)</span></label>
                        <textarea name="persiapan" rows="4" placeholder="Puasa 8 jam sebelum pemeriksaan.&#10;Kenakan pakaian longgar.">{{ old('persiapan') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Estimasi Durasi</label>
                        <input type="text" name="durasi" placeholder="cth. 15–30 menit" value="{{ old('durasi') }}">
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="aktif" {{ old('status','aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Biaya Minimum (Rp) *</label>
                        <input type="number" name="biaya_min" min="0" step="1000" placeholder="0" value="{{ old('biaya_min', 0) }}">
                    </div>
                    <div class="form-group">
                        <label>Biaya Maksimum (Rp) *</label>
                        <input type="number" name="biaya_max" min="0" step="1000" placeholder="0" value="{{ old('biaya_max', 0) }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('createModal')">Batal</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save" style="margin-right:6px;"></i>Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-pencil" style="margin-right:8px;color:#2d9e72;"></i>Edit Pemeriksaan</h3>
            <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="editForm">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Nama Pemeriksaan *</label>
                        <input type="text" name="nama" id="e-nama" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="kategori" id="e-kategori" required>
                            @foreach($kategoriOptions as $kat)
                                <option value="{{ $kat }}">{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ikon *</label>
                        <div class="icon-preview-wrap">
                            <div class="icon-preview" id="e-icon-preview" style="background:#e8f5f0;color:#2d9e72;">
                                <i class="fas fa-stethoscope" id="e-icon-el"></i>
                            </div>
                            <select name="icon" id="e-icon" required onchange="updateIconPreview('e')">
                                @foreach($iconOptions as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Warna Latar Ikon *</label>
                        <div class="color-row">
                            <input type="color" id="e-bg-picker" oninput="document.getElementById('e-bg-color').value=this.value; updateIconPreview('e')">
                            <input type="text" name="bg_color" id="e-bg-color" oninput="document.getElementById('e-bg-picker').value=this.value; updateIconPreview('e')">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Warna Ikon *</label>
                        <div class="color-row">
                            <input type="color" id="e-ic-picker" oninput="document.getElementById('e-icon-color').value=this.value; updateIconPreview('e')">
                            <input type="text" name="icon_color" id="e-icon-color" oninput="document.getElementById('e-ic-picker').value=this.value; updateIconPreview('e')">
                        </div>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Deskripsi Singkat</label>
                        <input type="text" name="singkat" id="e-singkat">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Deskripsi Lengkap</label>
                        <textarea name="deskripsi" id="e-deskripsi" rows="3"></textarea>
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Persiapan <span style="font-weight:400;color:#aaa;">(satu item per baris)</span></label>
                        <textarea name="persiapan" id="e-persiapan" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Estimasi Durasi</label>
                        <input type="text" name="durasi" id="e-durasi">
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" id="e-status" required>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Biaya Minimum (Rp) *</label>
                        <input type="number" name="biaya_min" id="e-biaya-min" min="0" step="1000">
                    </div>
                    <div class="form-group">
                        <label>Biaya Maksimum (Rp) *</label>
                        <input type="number" name="biaya_max" id="e-biaya-max" min="0" step="1000">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save" style="margin-right:6px;"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div class="modal-backdrop" id="deleteModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h3><i class="fas fa-trash" style="margin-right:8px;color:#e05252;"></i>Hapus Pemeriksaan</h3>
            <button class="modal-close" onclick="closeModal('deleteModal')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="color:#555;font-size:14px;line-height:1.6;">
                Apakah Anda yakin ingin menghapus pemeriksaan
                <strong id="delete-name" style="color:#1a3c34;"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('deleteModal')">Batal</button>
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger"><i class="fas fa-trash" style="margin-right:6px;"></i>Hapus</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Close on backdrop click
document.querySelectorAll('.modal-backdrop').forEach(function(el) {
    el.addEventListener('click', function(e) {
        if (e.target === el) closeModal(el.id);
    });
});

function updateIconPreview(prefix) {
    var icon    = document.getElementById(prefix + '-icon').value;
    var bg      = document.getElementById(prefix + '-bg-color').value;
    var color   = document.getElementById(prefix + '-icon-color').value;
    var preview = document.getElementById(prefix + '-icon-preview');
    var el      = document.getElementById(prefix + '-icon-el');
    preview.style.background = bg;
    preview.style.color = color;
    el.className = 'fas ' + icon;
}

function openEdit(id, nama, kategori, singkat, deskripsi, icon, bgColor, iconColor, durasi, biayaMin, biayaMax, status, persiapan) {
    document.getElementById('editForm').action = '/katalog/' + id;
    document.getElementById('e-nama').value      = nama;
    document.getElementById('e-singkat').value   = singkat;
    document.getElementById('e-deskripsi').value = deskripsi;
    document.getElementById('e-durasi').value    = durasi;
    document.getElementById('e-biaya-min').value = biayaMin;
    document.getElementById('e-biaya-max').value = biayaMax;
    document.getElementById('e-bg-color').value  = bgColor;
    document.getElementById('e-icon-color').value= iconColor;
    document.getElementById('e-bg-picker').value = bgColor;
    document.getElementById('e-ic-picker').value = iconColor;

    var katSel = document.getElementById('e-kategori');
    for (var i = 0; i < katSel.options.length; i++) {
        katSel.options[i].selected = katSel.options[i].value === kategori;
    }
    var iconSel = document.getElementById('e-icon');
    for (var i = 0; i < iconSel.options.length; i++) {
        iconSel.options[i].selected = iconSel.options[i].value === icon;
    }
    var statusSel = document.getElementById('e-status');
    for (var i = 0; i < statusSel.options.length; i++) {
        statusSel.options[i].selected = statusSel.options[i].value === status;
    }

    document.getElementById('e-persiapan').value = Array.isArray(persiapan) ? persiapan.join('\n') : '';

    updateIconPreview('e');
    openModal('editModal');
}

function confirmDelete(id, nama) {
    document.getElementById('delete-name').textContent = nama;
    document.getElementById('deleteForm').action = '/katalog/' + id;
    openModal('deleteModal');
}

// Open create modal if validation errors (old input present)
@if($errors->any() && old('nama'))
    openModal('createModal');
@endif
</script>
@endsection
