@extends('layouts.app')

@section('title', 'Pengingat - MEDISLOT')
@section('page-title', 'Pengingat')
@section('page-subtitle', 'Atur notifikasi agar tidak melewatkan jadwal penting')

@section('extra-styles')
<style>
    /* ── Toggle Switch ── */
    .toggle-wrap { display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .toggle { position: relative; width: 52px; height: 28px; cursor: pointer; }
    .toggle input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0; border-radius: 999px;
        background: #d0ddd9; transition: background 0.2s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        width: 22px; height: 22px; border-radius: 50%;
        left: 3px; top: 3px; background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.18);
        transition: transform 0.2s;
    }
    .toggle input:checked + .toggle-slider { background: #2d9e72; }
    .toggle input:checked + .toggle-slider::before { transform: translateX(24px); }
    .toggle-label { font-size: 11px; color: #7a9a90; font-weight: 500; }

    /* ── Reminder Card ── */
    .reminder-list { display: flex; flex-direction: column; gap: 16px; }
    .reminder-card {
        background: #fff; border-radius: 16px;
        padding: 20px 28px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        display: flex; align-items: center; gap: 20px;
        transition: box-shadow 0.18s;
    }
    .reminder-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.09); }
    .reminder-card.inactive { opacity: 0.55; }

    .bell-icon-wrap {
        width: 56px; height: 56px; border-radius: 14px;
        background: #e8f5f0;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .bell-icon-wrap i { font-size: 24px; color: #2d9e72; }

    .reminder-info { flex: 1; min-width: 0; }
    .reminder-name { font-size: 15px; font-weight: 700; color: #1a3c34; margin-bottom: 8px; }
    .reminder-meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }

    .chip {
        display: inline-flex; align-items: center; gap: 5px;
        background: #f0f7f4; border: 1px solid #c8e6d8;
        border-radius: 6px; padding: 3px 9px;
        font-size: 12px; font-weight: 600; color: #1a7a54;
    }
    .chip i { font-size: 11px; }

    .meta-time {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; color: #5a7a70;
    }
    .meta-time i { color: #2d9e72; font-size: 13px; }

    .reminder-actions { display: flex; flex-direction: column; align-items: center; gap: 6px; flex-shrink: 0; }
    .btn-edit {
        font-size: 12.5px; font-weight: 600; color: #1a3c34;
        text-decoration: underline; background: none; border: none;
        cursor: pointer; font-family: inherit; padding: 0;
        transition: color 0.15s;
    }
    .btn-edit:hover { color: #2d9e72; }

    /* ── Top bar ── */
    .page-top {
        display: flex; justify-content: flex-end;
        margin-bottom: 24px;
    }
    .btn-tambah {
        display: inline-flex; align-items: center; gap: 8px;
        background: #1a3c34; color: #fff;
        padding: 10px 20px; border-radius: 10px;
        font-size: 14px; font-weight: 600; font-family: inherit;
        border: none; cursor: pointer;
        text-decoration: none;
        transition: background 0.18s;
    }
    .btn-tambah:hover { background: #2d9e72; color: #fff; }

    /* ── Empty state ── */
    .empty-state {
        background: #fff; border-radius: 16px;
        padding: 60px 32px; text-align: center;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }
    .empty-state i { font-size: 48px; color: #c8ddd7; margin-bottom: 16px; }
    .empty-state h3 { font-size: 16px; font-weight: 600; color: #1a3c34; margin-bottom: 6px; }
    .empty-state p { font-size: 13.5px; color: #7a9a90; }

    /* ── Notification Cards ── */
    .notif-section { margin-top: 28px; }
    .notif-section-title { font-size: 14px; font-weight: 700; color: #1a3c34; margin-bottom: 12px; }
    .notif-card {
        background: #fff; border-radius: 12px;
        border-left: 4px solid #1a3c34;
        padding: 16px 20px; margin-bottom: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        cursor: pointer; transition: box-shadow 0.18s;
    }
    .notif-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .notif-card.read { opacity: 0.6; border-left-color: #d0ddd9; }
    .notif-card-head {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 4px;
    }
    .notif-sender { font-size: 12.5px; font-weight: 700; color: #1a3c34; }
    .notif-time   { font-size: 12px; color: #7a9a90; }
    .notif-title  { font-size: 13.5px; font-weight: 700; color: #1a3c34; margin-bottom: 3px; }
    .notif-pesan  { font-size: 13px; color: #5a7a70; line-height: 1.45; }

    /* ── Alert ── */
    .alert-success, .alert-error {
        padding: 13px 18px; border-radius: 10px;
        font-size: 13.5px; margin-bottom: 20px;
        display: flex; align-items: center; gap: 10px;
    }
    .alert-success { background: #d1fae5; color: #065f46; }
    .alert-error   { background: #fee2e2; color: #b91c1c; }

    /* ── Modal ── */
    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 500;
        background: rgba(0,0,0,0.45);
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: #fff; border-radius: 18px;
        width: 720px; max-width: 95vw;
        max-height: 90vh; overflow-y: auto;
        box-shadow: 0 16px 48px rgba(0,0,0,0.18);
        animation: slideUp 0.22s ease;
    }
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 22px 28px 0;
    }
    .modal-head h2 { font-size: 18px; font-weight: 700; color: #1a3c34; }
    .modal-close {
        width: 32px; height: 32px; border-radius: 50%;
        background: #f0f4f3; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #5a7a70; font-size: 15px; transition: background 0.15s;
    }
    .modal-close:hover { background: #dbeee7; }

    /* Modal preview card */
    .modal-preview-card {
        margin: 18px 28px 0;
        background: #f8fbfa; border-radius: 12px;
        padding: 14px 18px;
        display: flex; align-items: center; gap: 16px;
    }
    .modal-preview-card .bell-sm {
        width: 44px; height: 44px; border-radius: 10px;
        background: #e8f5f0; display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .modal-preview-card .bell-sm i { font-size: 20px; color: #2d9e72; }
    .modal-preview-card .pv-info { flex: 1; }
    .modal-preview-card .pv-name { font-size: 14px; font-weight: 700; color: #1a3c34; margin-bottom: 5px; }
    .modal-preview-card .pv-meta { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .modal-preview-card .toggle-wrap { flex-direction: row; gap: 8px; align-items: center; }
    .modal-preview-card .toggle-label { font-size: 12px; color: #1a3c34; font-weight: 600; white-space: nowrap; }

    /* Modal body */
    .modal-body {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: 0; padding: 20px 28px;
    }
    .modal-col { padding: 0 12px; }
    .modal-col:first-child { padding-left: 0; border-right: 1px solid #eef2f1; padding-right: 24px; }
    .modal-col:last-child  { padding-right: 0; padding-left: 24px; }
    .modal-col-title { font-size: 14px; font-weight: 700; color: #1a3c34; margin-bottom: 4px; }
    .modal-col-sub   { font-size: 12.5px; color: #7a9a90; margin-bottom: 16px; line-height: 1.4; }

    /* Waktu row */
    .waktu-list { display: flex; flex-direction: column; gap: 10px; }
    .waktu-row {
        display: flex; align-items: center; gap: 8px;
    }
    .drag-handle { color: #c8ddd7; cursor: grab; font-size: 13px; }
    .waktu-row i.fa-clock { color: #2d9e72; font-size: 14px; }
    .waktu-select {
        flex: 1; padding: 8px 10px; border-radius: 8px;
        border: 1.5px solid #d0ddd9; font-size: 13px;
        color: #333; font-family: inherit; outline: none;
        background: #f8fbfa; cursor: pointer;
        transition: border-color 0.18s;
    }
    .waktu-select:focus { border-color: #2d9e72; }
    .btn-del-waktu {
        width: 30px; height: 30px; border-radius: 7px;
        background: #fee2e2; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: #e05252; font-size: 12px; transition: background 0.15s;
        flex-shrink: 0;
    }
    .btn-del-waktu:hover { background: #fecaca; }
    .btn-tambah-waktu {
        display: inline-flex; align-items: center; gap: 7px;
        margin-top: 12px; font-size: 13px; font-weight: 600;
        color: #2d9e72; background: none; border: none;
        cursor: pointer; font-family: inherit; padding: 0;
        transition: color 0.15s;
    }
    .btn-tambah-waktu:hover { color: #1a7a54; }

    /* Preview list */
    .preview-list { display: flex; flex-direction: column; gap: 10px; }
    .preview-item {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 10px 12px; background: #f8fbfa; border-radius: 8px;
    }
    .preview-item i { color: #2d9e72; margin-top: 2px; font-size: 14px; flex-shrink: 0; }
    .preview-date { font-size: 13.5px; font-weight: 700; color: #1a3c34; }
    .preview-time { font-size: 12.5px; color: #7a9a90; }
    .preview-info {
        display: flex; align-items: flex-start; gap: 8px;
        margin-top: 10px; padding: 10px 12px;
        background: #fffbeb; border-radius: 8px; border: 1px solid #fde68a;
    }
    .preview-info i { color: #f59e0b; font-size: 13px; flex-shrink: 0; margin-top: 2px; }
    .preview-info span { font-size: 12px; color: #92400e; line-height: 1.4; }

    /* Modal footer */
    .modal-footer {
        display: flex; align-items: center; justify-content: flex-end; gap: 12px;
        padding: 16px 28px 22px;
        border-top: 1px solid #eef2f1;
    }
    .btn-batal {
        padding: 10px 22px; border-radius: 8px;
        border: 1.5px solid #d0ddd9; background: #fff;
        font-size: 13.5px; font-weight: 600; color: #5a7a70;
        cursor: pointer; font-family: inherit;
        transition: border-color 0.15s;
    }
    .btn-batal:hover { border-color: #1a3c34; color: #1a3c34; }
    .btn-simpan {
        padding: 10px 22px; border-radius: 8px;
        background: #1a3c34; border: none;
        font-size: 13.5px; font-weight: 600; color: #fff;
        cursor: pointer; font-family: inherit;
        transition: background 0.15s;
    }
    .btn-simpan:hover { background: #2d9e72; }

    /* Error inline */
    .inline-error {
        font-size: 12px; color: #e05252; margin-top: 8px;
        display: none;
    }
    .inline-error.show { display: block; }

    /* Loading skeleton */
    @keyframes shimmer {
        0%   { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }
    .skeleton {
        background: linear-gradient(90deg,#f0f4f3 25%,#e0ebe8 50%,#f0f4f3 75%);
        background-size: 600px 100%; animation: shimmer 1.4s infinite;
        border-radius: 8px;
    }
    .skeleton-card {
        height: 86px; border-radius: 16px; margin-bottom: 16px;
    }
</style>
@endsection

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
@endif
@if(isset($error) && $error)
<div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
@endif

{{-- Top action bar --}}
<div class="page-top">
    <button class="btn-tambah" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Tambah Pengingat
    </button>
</div>

{{-- ── REMINDER LIST ── --}}
@if($pengingat->isEmpty())
<div class="empty-state">
    <i class="fas fa-bell-slash"></i>
    <h3>Belum ada jadwal pemeriksaan aktif.</h3>
    <p>Tambahkan jadwal pemeriksaan terlebih dahulu, kemudian atur pengingat di sini.</p>
</div>
@else
<div class="reminder-list" id="reminderList">
    @foreach($pengingat as $p)
    @php
        $firstWaktu = $p->waktu->first();
        $jadwal     = $p->jadwal;
        $waktuJam   = $jadwal ? substr($jadwal->waktu, 0, 5) : '00:00';
    @endphp
    <div class="reminder-card {{ $p->is_active ? '' : 'inactive' }}" id="card-{{ $p->id }}">
        <div class="bell-icon-wrap">
            <i class="fas fa-bell"></i>
        </div>
        <div class="reminder-info">
            <div class="reminder-name">{{ $jadwal->jenis_pemeriksaan ?? '-' }}</div>
            <div class="reminder-meta">
                @if($firstWaktu)
                <span class="chip">
                    <i class="fas fa-calendar-plus"></i>
                    {{ $firstWaktu->chipLabel() }}
                    Pengingat {{ $firstWaktu->offsetLabel() }}
                </span>
                @endif
                <span class="meta-time">
                    <i class="fas fa-clock"></i>
                    {{ $waktuJam }} WIB
                </span>
                @if($p->waktu->count() > 1)
                <span style="font-size:12px;color:#7a9a90;">+{{ $p->waktu->count() - 1 }} lainnya</span>
                @endif
            </div>
        </div>
        <div class="reminder-actions">
            <label class="toggle" title="{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}">
                <input type="checkbox"
                    {{ $p->is_active ? 'checked' : '' }}
                    onchange="toggleReminder({{ $p->id }}, this)">
                <span class="toggle-slider"></span>
            </label>
            <button class="btn-edit" onclick="openEditModal({{ $p->id }})">Edit</button>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── NOTIFICATION PREVIEW ── --}}
@if($notifikasi->isNotEmpty())
<div class="notif-section">
    <div class="notif-section-title">Notifikasi Terbaru</div>
    @foreach($notifikasi as $n)
    <div class="notif-card {{ $n->is_read ? 'read' : '' }}"
         onclick="readNotif({{ $n->id }}, {{ $n->jadwal_id ?? 'null' }}, this)"
         id="notif-{{ $n->id }}">
        <div class="notif-card-head">
            <span class="notif-sender">MEDISLOT</span>
            <span class="notif-time">{{ $n->created_at->diffForHumans() }}</span>
        </div>
        <div class="notif-title">{{ $n->judul }}</div>
        <div class="notif-pesan">{{ $n->pesan }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════
     MODAL: Tambah Pengingat
═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Tambah Pengingat</h2>
            <button class="modal-close" onclick="closeAddModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        @if($jadwalTanpaReminder->isEmpty())
        {{-- No jadwal available --}}
        <div style="padding: 32px 28px; text-align: center;">
            <i class="fas fa-calendar-times" style="font-size:40px;color:#c8ddd7;display:block;margin-bottom:14px;"></i>
            <div style="font-size:15px;font-weight:700;color:#1a3c34;margin-bottom:6px;">
                Tidak ada jadwal tersedia
            </div>
            <div style="font-size:13.5px;color:#7a9a90;margin-bottom:20px;line-height:1.5;">
                Semua jadwal pemeriksaan mendatang sudah memiliki pengingat,<br>
                atau belum ada jadwal aktif. Buat jadwal baru terlebih dahulu.
            </div>
            <a href="{{ route('jadwal.create') }}" class="btn-simpan" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-plus"></i> Buat Jadwal Baru
            </a>
        </div>
        <div class="modal-footer" style="border-top:1px solid #eef2f1;">
            <button type="button" class="btn-batal" onclick="closeAddModal()">Tutup</button>
        </div>
        @else
        <form method="POST" action="{{ route('pengingat.store') }}" id="addForm">
            @csrf

            <div style="padding: 18px 28px 0;">
                <label style="font-size:13px;font-weight:600;color:#1a3c34;display:block;margin-bottom:6px;">
                    Pilih Jadwal Pemeriksaan
                </label>
                <select name="jadwal_id" id="addJadwalSelect" class="waktu-select"
                        style="width:100%;margin-bottom:0;" required
                        onchange="updateAddPreview()">
                    <option value="">-- Pilih Jadwal --</option>
                    @foreach($jadwalTanpaReminder as $j)
                    <option value="{{ $j->id }}"
                            data-tanggal="{{ $j->tanggal->format('Y-m-d') }}"
                            data-waktu="{{ substr($j->waktu, 0, 5) }}"
                            data-nama="{{ $j->jenis_pemeriksaan }}"
                            data-klinik="{{ $j->fasilitas_klinik }}">
                        {{ $j->jenis_pemeriksaan }} — {{ $j->tanggal->format('d M Y') }} {{ substr($j->waktu,0,5) }} WIB
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="modal-body">
                {{-- Waktu --}}
                <div class="modal-col">
                    <div class="modal-col-title">Waktu Pengingat</div>
                    <div class="modal-col-sub">Atur kapan pengingat akan dikirim sebelum jadwal pemeriksaan.</div>
                    <div class="waktu-list" id="addWaktuList">
                        <div class="waktu-row">
                            <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
                            <i class="fas fa-clock"></i>
                            <select name="offset_menit[]" class="waktu-select" onchange="updateAddPreview()">
                                @foreach($offsetOptions as $val => $label)
                                <option value="{{ $val }}" {{ $val == 1440 ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-del-waktu" onclick="removeWaktuRow(this)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <button type="button" class="btn-tambah-waktu" onclick="addWaktuRow('addWaktuList')">
                        <i class="fas fa-plus"></i> Tambah Waktu Pengingat
                    </button>
                    <div class="inline-error" id="addError"></div>
                </div>

                {{-- Preview --}}
                <div class="modal-col">
                    <div class="modal-col-title">Preview Pengingat</div>
                    <div class="modal-col-sub">Pengingat akan dikirim pada:</div>
                    <div class="preview-list" id="addPreviewList">
                        <div style="font-size:13px;color:#7a9a90;">Pilih jadwal terlebih dahulu.</div>
                    </div>
                    <div class="preview-info" style="margin-top:12px;">
                        <i class="fas fa-info-circle"></i>
                        <span>Waktu dapat berubah jika ada perubahan pada jadwal pemeriksaan.</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-batal" onclick="closeAddModal()">Batal</button>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: Edit Pengingat
═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <div class="modal-head">
            <h2>Edit Pengingat</h2>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Preview card (header of modal) --}}
        <div class="modal-preview-card">
            <div class="bell-sm"><i class="fas fa-bell"></i></div>
            <div class="pv-info">
                <div class="pv-name" id="editJadwalNama">-</div>
                <div class="pv-meta">
                    <span class="chip" id="editChipLabel">
                        <i class="fas fa-calendar-plus"></i> H-1
                    </span>
                    <span class="meta-time">
                        <i class="fas fa-clock"></i>
                        <span id="editJadwalWaktu">--:--</span> WIB
                    </span>
                </div>
            </div>
            <div class="toggle-wrap" style="flex-direction:row;gap:8px;align-items:center;">
                <label class="toggle">
                    <input type="checkbox" id="editIsActive" checked>
                    <span class="toggle-slider"></span>
                </label>
                <span class="toggle-label" style="color:#1a3c34;font-size:12px;font-weight:600;">Pengingat Aktif</span>
            </div>
        </div>

        <div class="modal-body">
            {{-- Waktu --}}
            <div class="modal-col">
                <div class="modal-col-title">Waktu Pengingat</div>
                <div class="modal-col-sub">Atur kapan pengingat akan dikirim sebelum jadwal pemeriksaan.</div>
                <div class="waktu-list" id="editWaktuList"></div>
                <button type="button" class="btn-tambah-waktu" onclick="addWaktuRow('editWaktuList')">
                    <i class="fas fa-plus"></i> Tambah Waktu Pengingat
                </button>
                <div class="inline-error" id="editError"></div>
            </div>

            {{-- Preview --}}
            <div class="modal-col">
                <div class="modal-col-title">Preview Pengingat</div>
                <div class="modal-col-sub">Pengingat akan dikirim pada:</div>
                <div class="preview-list" id="editPreviewList"></div>
                <div class="preview-info" style="margin-top:12px;">
                    <i class="fas fa-info-circle"></i>
                    <span>Waktu dapat berubah jika ada perubahan pada jadwal pemeriksaan.</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-batal" onclick="closeEditModal()">Batal</button>
            <button type="button" class="btn-simpan" id="editSimpanBtn" onclick="submitEdit()">Simpan Perubahan</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── Data from server ──
const pengingatData = @json($pengingatJson);

const offsetOptions = @json($offsetOptions);
let currentEditId = null;

// ── CSRF ──
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

// ── Toggle Reminder ──
function toggleReminder(id, checkbox) {
    fetch(`/pengingat/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const card = document.getElementById(`card-${id}`);
        if (data.is_active) {
            card.classList.remove('inactive');
        } else {
            card.classList.add('inactive');
        }
    })
    .catch(() => { checkbox.checked = !checkbox.checked; });
}

// ── Offset select HTML ──
function buildOffsetSelect(selectedVal, onchange = '') {
    let html = `<select name="offset_menit[]" class="waktu-select" onchange="${onchange}">`;
    for (const [val, label] of Object.entries(offsetOptions)) {
        html += `<option value="${val}" ${parseInt(val) === parseInt(selectedVal) ? 'selected' : ''}>${label}</option>`;
    }
    html += `</select>`;
    return html;
}

function waktuRow(selectedVal, onchange = '') {
    const div = document.createElement('div');
    div.className = 'waktu-row';
    div.innerHTML = `
        <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
        <i class="fas fa-clock"></i>
        ${buildOffsetSelect(selectedVal, onchange)}
        <button type="button" class="btn-del-waktu" onclick="removeWaktuRow(this)">
            <i class="fas fa-trash-alt"></i>
        </button>`;
    return div;
}

function addWaktuRow(listId) {
    const list = document.getElementById(listId);
    const onchange = listId === 'editWaktuList' ? 'updateEditPreview()' : 'updateAddPreview()';
    list.appendChild(waktuRow(1440, onchange));
    if (listId === 'editWaktuList') updateEditPreview();
    else updateAddPreview();
}

function removeWaktuRow(btn) {
    const row  = btn.closest('.waktu-row');
    const list = row.parentElement;
    if (list.querySelectorAll('.waktu-row').length <= 1) return;
    row.remove();
    if (list.id === 'editWaktuList') updateEditPreview();
    else updateAddPreview();
}

// ── Preview calculation ──
function calcPreviewItems(tanggal, waktu, offsets) {
    if (!tanggal || !waktu) return [];
    const [year, month, day] = tanggal.split('-').map(Number);
    const [hour, min]        = waktu.split(':').map(Number);
    const jadwalMs           = new Date(year, month - 1, day, hour, min).getTime();

    return offsets.map(offset => {
        const reminderMs = jadwalMs - offset * 60 * 1000;
        const d          = new Date(reminderMs);
        const months     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        const dateStr    = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        const hh         = String(d.getHours()).padStart(2, '0');
        const mm         = String(d.getMinutes()).padStart(2, '0');
        const label      = offsetOptions[offset] ?? `${offset} menit sebelum`;
        return { dateStr, timeStr: `${hh}.${mm} WIB`, label };
    });
}

function renderPreview(items, containerId) {
    const el = document.getElementById(containerId);
    if (!items.length) {
        el.innerHTML = '<div style="font-size:13px;color:#7a9a90;">Pilih jadwal terlebih dahulu.</div>';
        return;
    }
    el.innerHTML = items.map(item => `
        <div class="preview-item">
            <i class="fas fa-calendar-check"></i>
            <div>
                <div class="preview-date">${item.dateStr}</div>
                <div class="preview-time">${item.timeStr} (${item.label})</div>
            </div>
        </div>`).join('');
}

function getSelectedOffsets(listId) {
    return [...document.getElementById(listId).querySelectorAll('select')]
        .map(s => parseInt(s.value))
        .filter(v => !isNaN(v));
}

// ── Add modal ──
function openAddModal() {
    document.getElementById('addModal').classList.add('open');
    document.getElementById('addJadwalSelect').value = '';
    document.getElementById('addPreviewList').innerHTML = '<div style="font-size:13px;color:#7a9a90;">Pilih jadwal terlebih dahulu.</div>';
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('open');
}

function updateAddPreview() {
    const sel = document.getElementById('addJadwalSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.dataset.tanggal) {
        document.getElementById('addPreviewList').innerHTML = '<div style="font-size:13px;color:#7a9a90;">Pilih jadwal terlebih dahulu.</div>';
        return;
    }
    const offsets = getSelectedOffsets('addWaktuList');
    const items   = calcPreviewItems(opt.dataset.tanggal, opt.dataset.waktu, offsets);
    renderPreview(items, 'addPreviewList');
}

// ── Edit modal ──
function openEditModal(id) {
    currentEditId = id;
    const p = pengingatData.find(x => x.id === id);
    if (!p) return;

    document.getElementById('editJadwalNama').textContent  = p.jadwal.nama ?? '-';
    document.getElementById('editJadwalWaktu').textContent = p.jadwal.waktu ?? '--:--';
    document.getElementById('editIsActive').checked        = !!p.is_active;

    const firstWaktu = p.waktu[0];
    document.getElementById('editChipLabel').innerHTML = firstWaktu
        ? `<i class="fas fa-calendar-plus"></i> ${firstWaktu.chip} Pengingat ${firstWaktu.label}`
        : '<i class="fas fa-calendar-plus"></i> -';

    const list = document.getElementById('editWaktuList');
    list.innerHTML = '';
    (p.waktu.length ? p.waktu : [{ offset_menit: 1440 }]).forEach(w => {
        list.appendChild(waktuRow(w.offset_menit, 'updateEditPreview()'));
    });

    updateEditPreview();
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
    document.getElementById('editError').classList.remove('show');
    currentEditId = null;
}

function updateEditPreview() {
    if (!currentEditId) return;
    const p       = pengingatData.find(x => x.id === currentEditId);
    const offsets = getSelectedOffsets('editWaktuList');
    const items   = calcPreviewItems(p?.jadwal.tanggal, p?.jadwal.waktu, offsets);
    renderPreview(items, 'editPreviewList');
}

function submitEdit() {
    if (!currentEditId) return;
    const offsets  = getSelectedOffsets('editWaktuList');
    const isActive = document.getElementById('editIsActive').checked ? 1 : 0;

    const body = new URLSearchParams();
    body.append('_method', 'PUT');
    body.append('is_active', isActive);
    offsets.forEach(o => body.append('offset_menit[]', o));

    document.getElementById('editError').classList.remove('show');
    document.getElementById('editSimpanBtn').disabled = true;

    fetch(`/pengingat/${currentEditId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('editSimpanBtn').disabled = false;
        if (data.error) {
            const el = document.getElementById('editError');
            el.textContent = data.error;
            el.classList.add('show');
            return;
        }
        closeEditModal();
        showToast('Reminder berhasil diperbarui.', 'success');
        setTimeout(() => location.reload(), 900);
    })
    .catch(() => {
        document.getElementById('editSimpanBtn').disabled = false;
        const el = document.getElementById('editError');
        el.textContent = 'Reminder gagal disimpan.';
        el.classList.add('show');
    });
}

// ── Notifikasi ──
function readNotif(id, jadwalId, el) {
    fetch(`/notifikasi/${id}/read`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    el.classList.add('read');
    if (jadwalId) window.location.href = '/jadwal';
}

// ── Toast ──
function showToast(msg, type = 'success') {
    const toast = document.createElement('div');
    toast.className = type === 'success' ? 'alert-success' : 'alert-error';
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;padding:14px 20px;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.12);display:flex;align-items:center;gap:10px;min-width:260px;';
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ── Close modal on overlay click ──
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// ── CSRF meta tag fallback ──
if (!document.querySelector('meta[name="csrf-token"]')) {
    const m = document.createElement('meta');
    m.name = 'csrf-token'; m.content = '{{ csrf_token() }}';
    document.head.appendChild(m);
}
</script>
@endsection
