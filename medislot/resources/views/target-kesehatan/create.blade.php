@extends('layouts.app')

@section('title', 'Buat Target Kesehatan - MEDISLOT')
@section('page-title', 'Target Kesehatan')
@section('page-subtitle', 'Tetapkan target kesehatanmu dan pantau pencapaiannya secara rutin.')

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
    .form-card-title   { font-size: 16px; font-weight: 700; color: #1a3c34; }
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
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .form-error { color: #dc2626; font-size: 12px; margin-top: 4px; }

    /* Icon selector */
    .icon-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;
    }
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
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .icon-name { font-size: 11.5px; color: #5a7a70; font-weight: 500; }

    .form-footer {
        padding: 20px 28px; border-top: 1px solid #e8eeec;
        display: flex; justify-content: center; gap: 12px;
    }
    .btn-simpan {
        background: #1a3c34; color: #fff; border: none;
        padding: 12px 48px; border-radius: 999px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: background 0.18s;
    }
    .btn-simpan:hover { background: #2d9e72; }
    .btn-batal {
        background: #fff; color: #5a7a70;
        border: 1.5px solid #d0ddd9;
        padding: 12px 32px; border-radius: 999px;
        font-size: 15px; font-weight: 500;
        text-decoration: none; display: inline-flex;
        align-items: center; transition: all 0.18s;
    }
    .btn-batal:hover { border-color: #2d9e72; color: #1a3c34; }

    .hint { font-size: 12px; color: #7a9a90; margin-top: 6px; }
</style>
@endsection

@section('content')
<div class="form-progress"></div>

<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-bullseye"></i></div>
        <div>
            <div class="form-card-title">Buat Target Kesehatan Baru</div>
            <div class="form-card-subtitle">Tetapkan target dan pantau perkembanganmu secara rutin</div>
        </div>
    </div>

    <form action="{{ route('target-kesehatan.store') }}" method="POST" id="targetForm">
        @csrf
        <div class="form-body">

            {{-- Nama Target --}}
            <div class="form-group">
                <label>Nama Target <span style="color:#e74c3c">*</span></label>
                <input type="text" name="nama"
                       value="{{ old('nama') }}"
                       placeholder="Contoh: Pemeriksaan Gigi Rutin"
                       required>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label>Deskripsi <span style="font-weight:400;color:#7a9a90">(opsional)</span></label>
                <textarea name="deskripsi" placeholder="Contoh: Lakukan pemeriksaan gigi setiap 6 bulan sekali">{{ old('deskripsi') }}</textarea>
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
                    $selectedIcon = old('icon', 'fas fa-heartbeat');
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
                <input type="hidden" name="icon_color" id="iconColor" value="{{ old('icon_color', '#2d9e72') }}">
                <input type="hidden" name="icon_bg"    id="iconBg"    value="{{ old('icon_bg', '#e8fff4') }}">
                @error('icon') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Target Aktivitas, Aktivitas Dilakukan, Satuan --}}
            <div class="form-row-3">
                <div class="form-group">
                    <label>Target Aktivitas <span style="color:#e74c3c">*</span></label>
                    <input type="number" name="target_aktivitas"
                           value="{{ old('target_aktivitas') }}"
                           placeholder="Contoh: 4"
                           min="1" required>
                    @error('target_aktivitas') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Sudah Dilakukan</label>
                    <input type="number" name="aktivitas_dilakukan"
                           value="{{ old('aktivitas_dilakukan', 0) }}"
                           placeholder="0"
                           min="0">
                    @error('aktivitas_dilakukan') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color:#e74c3c">*</span></label>
                    <select name="satuan" required>
                        @foreach(['kali', 'hari', 'menit', 'gelas', 'langkah', 'km'] as $sat)
                            <option value="{{ $sat }}" {{ old('satuan', 'kali') === $sat ? 'selected' : '' }}>
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
                       value="{{ old('tanggal_target') }}"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       required>
                @error('tanggal_target') <div class="form-error">{{ $message }}</div> @enderror
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('target-kesehatan.index') }}" class="btn-batal">Batal</a>
            <button type="submit" class="btn-simpan">Simpan Target</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.icon-option').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('iconColor').value = this.dataset.color;
        document.getElementById('iconBg').value    = this.dataset.bg;
    });
});
// Set initial values
(function() {
    const checked = document.querySelector('.icon-option:checked');
    if (checked) {
        document.getElementById('iconColor').value = checked.dataset.color;
        document.getElementById('iconBg').value    = checked.dataset.bg;
    }
})();
</script>
@endsection
