@extends('layouts.app')

@section('title', 'Edit Target Kesehatan - MEDISLOT')
@section('page-title', 'Target Kesehatan')
@section('page-subtitle', 'Perbarui target dan progres kesehatanmu.')

@section('extra-styles')
<style>
    .form-progress {
        height: 6px; background: #2d9e72;
        border-radius: 999px; margin-bottom: 28px; width: 100%;
    }
    .form-card {
        background: #fff; border-radius: 16px;
        overflow: hidden; max-width: 700px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .form-card-header {
        display: flex; align-items: center; gap: 16px;
        padding: 20px 28px; border-bottom: 1px solid #e8eeec;
    }
    .form-card-icon {
        width: 48px; height: 48px; background: #2d9e72;
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 22px; flex-shrink: 0;
    }
    .form-card-title    { font-size: 16px; font-weight: 700; color: #1a3c34; }
    .form-card-subtitle { font-size: 13px; color: #7a9a90; margin-top: 2px; }

    .form-body { padding: 28px; display: flex; flex-direction: column; gap: 22px; }
    .form-group label {
        display: block; font-size: 12px; font-weight: 700;
        color: #1a3c34; letter-spacing: 0.5px;
        text-transform: uppercase; margin-bottom: 8px;
    }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid #d0ddd9; border-radius: 10px;
        font-size: 14px; color: #333; background: #fff;
        font-family: inherit; outline: none;
        transition: border-color 0.18s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: #2d9e72; box-shadow: 0 0 0 3px rgba(45,158,114,0.1);
    }
    .form-group textarea { resize: vertical; min-height: 76px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .form-error { color: #dc2626; font-size: 12px; margin-top: 4px; }

    /* Progress preview */
    .progress-preview {
        background: #f8fbfa; border-radius: 12px;
        padding: 16px 20px; display: flex; align-items: center; gap: 16px;
    }
    .progress-preview-pct { font-size: 28px; font-weight: 800; color: #1a3c34; min-width: 70px; }
    .progress-bar-wrap { flex: 1; }
    .progress-bar {
        height: 8px; background: #e8eeec; border-radius: 4px; overflow: hidden; margin-bottom: 6px;
    }
    .progress-fill { height: 100%; background: #2d9e72; border-radius: 4px; transition: width 0.4s; }
    .progress-fill.orange { background: #e67e22; }
    .progress-status { font-size: 13px; font-weight: 600; }
    .status-on-track        { color: #2d9e72; }
    .status-perlu-perhatian { color: #e67e22; }
    .status-tercapai        { color: #2563eb; }

    /* Icon selector */
    .icon-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .icon-option { display: none; }
    .icon-label {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 12px 8px; border-radius: 12px;
        border: 2px solid #e8eeec; cursor: pointer;
        transition: all 0.18s; background: #fff; text-align: center;
    }
    .icon-label:hover { border-color: #2d9e72; }
    .icon-option:checked + .icon-label {
        border-color: #2d9e72; background: #f0fbf6;
        box-shadow: 0 0 0 3px rgba(45,158,114,0.12);
    }
    .icon-preview {
        width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .icon-name { font-size: 11.5px; color: #5a7a70; font-weight: 500; }

    .form-footer {
        padding: 20px 28px; border-top: 1px solid #e8eeec;
        display: flex; justify-content: space-between; align-items: center;
    }
    .form-footer-right { display: flex; gap: 12px; }
    .btn-simpan {
        background: #1a3c34; color: #fff; border: none;
        padding: 12px 40px; border-radius: 999px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: background 0.18s;
    }
    .btn-simpan:hover { background: #2d9e72; }
    .btn-batal {
        background: #fff; color: #5a7a70;
        border: 1.5px solid #d0ddd9;
        padding: 12px 28px; border-radius: 999px;
        font-size: 15px; font-weight: 500;
        text-decoration: none; display: inline-flex;
        align-items: center; transition: all 0.18s;
    }
    .btn-batal:hover { border-color: #2d9e72; color: #1a3c34; }
    .btn-hapus {
        background: #fff; color: #dc2626;
        border: 1.5px solid #fca5a5;
        padding: 12px 20px; border-radius: 999px;
        font-size: 14px; font-weight: 500; cursor: pointer;
        font-family: inherit; display: inline-flex;
        align-items: center; gap: 6px; transition: all 0.18s;
    }
    .btn-hapus:hover { background: #fee2e2; }

    .hint { font-size: 12px; color: #7a9a90; margin-top: 6px; }

    /* Confirm Delete Modal */
    .confirm-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.4); z-index: 999;
        align-items: center; justify-content: center;
    }
    .confirm-overlay.active { display: flex; }
    .confirm-box {
        background: #fff; border-radius: 16px;
        padding: 32px 36px; text-align: center; max-width: 340px; width: 90%;
    }
    .confirm-box i   { font-size: 40px; color: #e74c3c; margin-bottom: 12px; display: block; }
    .confirm-box p   { font-size: 15px; color: #1a3c34; font-weight: 600; margin-bottom: 6px; }
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
</style>
@endsection

@section('content')
<div class="form-progress"></div>

{{-- Confirm Delete Modal --}}
<div id="confirmModal" class="confirm-overlay">
    <div class="confirm-box">
        <i class="fas fa-trash-alt"></i>
        <p>Hapus target ini?</p>
        <small>Tindakan ini tidak dapat dibatalkan.</small>
        <div class="confirm-actions">
            <button class="btn-batal-kecil" onclick="document.getElementById('confirmModal').classList.remove('active')">Batal</button>
            <button class="btn-hapus-confirm" onclick="document.getElementById('deleteForm').submit()">Ya, Hapus</button>
        </div>
    </div>
</div>
<form id="deleteForm" action="{{ route('target-kesehatan.destroy', $target) }}" method="POST">
    @csrf @method('DELETE')
</form>

<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-pen"></i></div>
        <div>
            <div class="form-card-title">Edit Target Kesehatan</div>
            <div class="form-card-subtitle">Perbarui detail dan progres targetmu</div>
        </div>
    </div>

    <form action="{{ route('target-kesehatan.update', $target) }}" method="POST" id="targetForm">
        @csrf @method('PUT')
        <div class="form-body">

            {{-- Progress Preview --}}
            @php
                $prog = $target->progress;
                $statusClass = match($target->status) {
                    'on-track' => 'on-track',
                    'perlu-perhatian' => 'perlu-perhatian',
                    default => 'tercapai',
                };
            @endphp
            <div class="progress-preview">
                <div class="progress-preview-pct" id="previewPct">{{ $prog }}%</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar">
                        <div class="progress-fill {{ $prog < 50 ? 'orange' : '' }}" id="previewBar"
                             style="width:{{ $prog }}%"></div>
                    </div>
                    <div class="progress-status status-{{ $statusClass }}" id="previewStatus">
                        {{ $target->status_label }}
                    </div>
                </div>
            </div>

            {{-- Nama Target --}}
            <div class="form-group">
                <label>Nama Target <span style="color:#e74c3c">*</span></label>
                <input type="text" name="nama"
                       value="{{ old('nama', $target->nama) }}"
                       placeholder="Contoh: Pemeriksaan Gigi Rutin"
                       required>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label>Deskripsi <span style="font-weight:400;color:#7a9a90">(opsional)</span></label>
                <textarea name="deskripsi" placeholder="Contoh: Lakukan pemeriksaan gigi setiap 6 bulan sekali">{{ old('deskripsi', $target->deskripsi) }}</textarea>
                @error('deskripsi') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Icon Selector --}}
            <div class="form-group">
                <label>Ikon Target <span style="color:#e74c3c">*</span></label>
                <div class="icon-grid">
                    @php
                    $icons = [
                        ['icon' => 'fas fa-tooth',      'color' => '#4a90d9', 'bg' => '#e8f4ff', 'name' => 'Gigi'],
                        ['icon' => 'fas fa-heartbeat',  'color' => '#2d9e72', 'bg' => '#e8fff4', 'name' => 'Jantung'],
                        ['icon' => 'fas fa-tint',       'color' => '#4a90d9', 'bg' => '#e8f4ff', 'name' => 'Air'],
                        ['icon' => 'fas fa-moon',       'color' => '#7c5cbf', 'bg' => '#f0ebff', 'name' => 'Tidur'],
                        ['icon' => 'fas fa-running',    'color' => '#e67e22', 'bg' => '#fff3e8', 'name' => 'Olahraga'],
                        ['icon' => 'fas fa-weight',     'color' => '#2d9e72', 'bg' => '#e8fff4', 'name' => 'Berat Badan'],
                        ['icon' => 'fas fa-apple-alt',  'color' => '#e74c3c', 'bg' => '#ffe8e8', 'name' => 'Nutrisi'],
                        ['icon' => 'fas fa-pills',      'color' => '#7c5cbf', 'bg' => '#f0ebff', 'name' => 'Obat'],
                    ];
                    $selectedIcon = old('icon', $target->icon);
                    @endphp

                    @foreach($icons as $opt)
                    <div>
                        <input type="radio" name="icon" id="icon_{{ $loop->index }}"
                               class="icon-option"
                               value="{{ $opt['icon'] }}"
                               data-color="{{ $opt['color'] }}"
                               data-bg="{{ $opt['bg'] }}"
                               {{ $selectedIcon === $opt['icon'] ? 'checked' : '' }}>
                        <label for="icon_{{ $loop->index }}" class="icon-label">
                            <div class="icon-preview" style="background:{{ $opt['bg'] }};color:{{ $opt['color'] }}">
                                <i class="{{ $opt['icon'] }}"></i>
                            </div>
                            <span class="icon-name">{{ $opt['name'] }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="icon_color" id="iconColor" value="{{ old('icon_color', $target->icon_color) }}">
                <input type="hidden" name="icon_bg"    id="iconBg"    value="{{ old('icon_bg', $target->icon_bg) }}">
                @error('icon') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Target Aktivitas, Aktivitas Dilakukan, Satuan --}}
            <div class="form-row-3">
                <div class="form-group">
                    <label>Target Aktivitas <span style="color:#e74c3c">*</span></label>
                    <input type="number" name="target_aktivitas" id="targetAktivitas"
                           value="{{ old('target_aktivitas', $target->target_aktivitas) }}"
                           placeholder="4" min="1" required>
                    @error('target_aktivitas') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Sudah Dilakukan</label>
                    <input type="number" name="aktivitas_dilakukan" id="aktivitasDilakukan"
                           value="{{ old('aktivitas_dilakukan', $target->aktivitas_dilakukan) }}"
                           placeholder="0" min="0">
                    @error('aktivitas_dilakukan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color:#e74c3c">*</span></label>
                    <select name="satuan" required>
                        @foreach(['kali', 'hari', 'menit', 'gelas', 'langkah', 'km'] as $sat)
                            <option value="{{ $sat }}"
                                {{ old('satuan', $target->satuan) === $sat ? 'selected' : '' }}>
                                {{ $sat }}
                            </option>
                        @endforeach
                    </select>
                    @error('satuan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="hint"><i class="fas fa-info-circle"></i> Progress = Sudah Dilakukan ÷ Target Aktivitas × 100%</div>

            {{-- Tanggal Target --}}
            <div class="form-group">
                <label>Tanggal Target <span style="color:#e74c3c">*</span></label>
                <input type="date" name="tanggal_target"
                       value="{{ old('tanggal_target', $target->tanggal_target->format('Y-m-d')) }}"
                       required>
                @error('tanggal_target') <div class="form-error">{{ $message }}</div> @enderror
            </div>

        </div>

        <div class="form-footer">
            <button type="button" class="btn-hapus"
                    onclick="document.getElementById('confirmModal').classList.add('active')">
                <i class="fas fa-trash"></i> Hapus Target
            </button>
            <div class="form-footer-right">
                <a href="{{ route('target-kesehatan.index') }}" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
// Icon selector
document.querySelectorAll('.icon-option').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('iconColor').value = this.dataset.color;
        document.getElementById('iconBg').value    = this.dataset.bg;
    });
});

// Live progress preview
function updatePreview() {
    const done   = parseInt(document.getElementById('aktivitasDilakukan').value) || 0;
    const target = parseInt(document.getElementById('targetAktivitas').value)    || 1;
    const pct    = Math.min(100, Math.round((done / target) * 100));

    document.getElementById('previewPct').textContent = pct + '%';
    const bar = document.getElementById('previewBar');
    bar.style.width = pct + '%';

    const statusEl = document.getElementById('previewStatus');
    if (pct >= 100) {
        bar.className = 'progress-fill';
        statusEl.className = 'progress-status status-tercapai';
        statusEl.textContent = 'Tercapai';
    } else if (pct >= 50) {
        bar.className = 'progress-fill';
        statusEl.className = 'progress-status status-on-track';
        statusEl.textContent = 'On Track';
    } else {
        bar.className = 'progress-fill orange';
        statusEl.className = 'progress-status status-perlu-perhatian';
        statusEl.textContent = 'Perlu Perhatian';
    }
}

document.getElementById('aktivitasDilakukan').addEventListener('input', updatePreview);
document.getElementById('targetAktivitas').addEventListener('input', updatePreview);

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});
</script>
@endsection
