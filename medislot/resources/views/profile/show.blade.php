@extends('layouts.app')

@section('title', 'Profil Saya - MEDISLOT')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola data diri dan informasi kontak Anda')

@section('topbar-actions')
{{-- Tombol Edit (view mode) --}}
<button id="topbarBtnEdit"
    onclick="toggleEditMode()"
    style="background:#2d9e72;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
    Edit
</button>
{{-- Tombol Simpan + Batal (edit mode, tersembunyi by default) --}}
<div id="topbarEditActions" style="display:none;gap:10px;align-items:center;display:none;">
    <button type="button" onclick="toggleEditMode()"
        style="background:#f0f4f3;color:#5a7a70;border:none;border-radius:8px;padding:9px 18px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
        Batal
    </button>
    <button form="profileForm" type="submit"
        style="background:#2d9e72;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;">
        Simpan
    </button>
</div>
@endsection

@section('extra-styles')
<style>
    .profile-wrapper {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        max-width: 960px;
    }

    /* ── LEFT CARD (avatar + name) ── */
    .profile-card-left {
        background: #fff;
        border-radius: 16px;
        padding: 32px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .avatar-wrapper {
        position: relative;
        width: 120px; height: 120px;
    }
    .avatar-img {
        width: 120px; height: 120px;
        border-radius: 12px;
        object-fit: cover;
        background: #dbeee7;
    }
    .avatar-placeholder {
        width: 120px; height: 120px;
        border-radius: 12px;
        background: #dbeee7;
        display: flex; align-items: center; justify-content: center;
        font-size: 48px; color: #2d9e72; font-weight: 700;
    }
    .avatar-edit-btn {
        position: absolute;
        bottom: -8px; right: -8px;
        width: 30px; height: 30px;
        background: #2d9e72; color: #fff;
        border-radius: 50%; border: 2px solid #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; cursor: pointer;
        transition: background 0.18s;
    }
    .avatar-edit-btn:hover { background: #1a8a5c; }

    .profile-name {
        font-size: 18px; font-weight: 700; color: #1a3c34; text-align: center;
    }
    .profile-phone {
        font-size: 14px; color: #5a7a70; text-align: center;
    }
    .profile-email {
        font-size: 13px; color: #7a9a90; text-align: center; word-break: break-all;
    }

    /* ── RIGHT CARDS ── */
    .profile-cards-right {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .info-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px 28px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .info-card-title {
        font-size: 15px; font-weight: 700; color: #1a3c34;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid #edf2f0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f4f8f6;
        gap: 16px;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 13.5px; color: #7a9a90; font-weight: 500; min-width: 120px; }
    .info-value { font-size: 13.5px; color: #1a3c34; font-weight: 600; text-align: right; }

    /* ── EDIT MODE FORM ── */
    .edit-form { display: none; }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group.full-width { grid-column: 1 / -1; }
    .form-label { font-size: 12.5px; font-weight: 600; color: #5a7a70; text-transform: uppercase; letter-spacing: 0.4px; }
    .form-input, .form-select {
        padding: 10px 14px;
        border: 1.5px solid #d5e5df;
        border-radius: 8px;
        font-size: 14px; font-family: inherit;
        color: #1a3c34; background: #f8fbfa;
        transition: border-color 0.18s, box-shadow 0.18s;
        outline: none;
    }
    .form-input:focus, .form-select:focus {
        border-color: #2d9e72;
        box-shadow: 0 0 0 3px rgba(45,158,114,0.12);
        background: #fff;
    }
    .form-input.error { border-color: #e74c3c; }

    .form-actions {
        display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px;
    }
    .btn-save {
        background: #2d9e72; color: #fff; border: none;
        border-radius: 8px; padding: 10px 28px;
        font-size: 14px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: background 0.18s;
    }
    .btn-save:hover { background: #1a8a5c; }
    .btn-cancel {
        background: #f0f4f3; color: #5a7a70; border: none;
        border-radius: 8px; padding: 10px 20px;
        font-size: 14px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: background 0.18s;
    }
    .btn-cancel:hover { background: #dbeee7; }

    /* ── ALERT MESSAGES ── */
    .alert {
        padding: 12px 18px; border-radius: 10px; font-size: 13.5px;
        font-weight: 500; margin-bottom: 20px;
        display: flex; align-items: center; gap: 10px;
    }
    .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
    .alert-error   { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

    /* ── TAG BADGES (allergies, conditions) ── */
    .tag-list { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
    .tag {
        background: #dbeee7; color: #1a6645;
        border-radius: 20px; padding: 3px 10px;
        font-size: 12px; font-weight: 500;
    }
    .tag.danger { background: #fde8e8; color: #c0392b; }
    .tag.none { background: #f0f4f3; color: #7a9a90; }
</style>
@endsection

@section('content')

{{-- Flash Messages --}}
@if(session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
@endif

<div class="profile-wrapper">

    {{-- ── LEFT: Avatar + Identity ── --}}
    <div class="profile-card-left">

        <div class="avatar-wrapper">
            @if($profile->avatar_url)
                <img src="{{ $profile->avatar_url }}" alt="Avatar" class="avatar-img">
            @else
                <div class="avatar-placeholder">
                    {{ strtoupper(substr($profile->name, 0, 1)) }}
                </div>
            @endif
            <div class="avatar-edit-btn" onclick="document.getElementById('avatarInput').click()">
                <i class="fas fa-camera"></i>
            </div>
        </div>

        <div class="profile-name">{{ $profile->name }}</div>
        <div class="profile-phone">{{ $profile->phone ?? '-' }}</div>
        <div class="profile-email">{{ Auth::user()->email }}</div>
    </div>

    {{-- ── RIGHT: Info Cards ── --}}
    <div class="profile-cards-right">

        {{-- ─── VIEW MODE ─── --}}
        <div id="viewMode">

            {{-- Informasi Pribadi --}}
            <div class="info-card" style="margin-bottom: 20px;">
                <div class="info-card-title">Informasi Pribadi</div>

                <div class="info-row">
                    <span class="info-label">Tanggal Lahir</span>
                    <span class="info-value">{{ $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d.m.Y') : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Usia</span>
                    <span class="info-value">{{ $profile->age }} tahun</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Jenis Kelamin</span>
                    <span class="info-value">{{ $profile->gender }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">No. Telepon</span>
                    <span class="info-value">{{ $profile->phone ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat</span>
                    <span class="info-value">{{ $profile->address ?? '-' }}</span>
                </div>
            </div>

            {{-- Informasi Kesehatan --}}
            <div class="info-card">
                <div class="info-card-title">Informasi Kesehatan</div>

                <div class="info-row">
                    <span class="info-label">Alergi</span>
                    <div class="tag-list">
                        @forelse($healthData->allergies ?? [] as $a)
                            <span class="tag danger">{{ $a }}</span>
                        @empty
                            <span class="tag none">Tidak ada</span>
                        @endforelse
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Penyakit Kronis</span>
                    <div class="tag-list">
                        @forelse($healthData->chronic_conditions ?? [] as $c)
                            <span class="tag danger">{{ $c }}</span>
                        @empty
                            <span class="tag none">Tidak ada</span>
                        @endforelse
                    </div>
                </div>
                <div class="info-row">
                    <span class="info-label">Golongan Darah</span>
                    <span class="info-value">{{ $healthData->blood_type ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tinggi Badan</span>
                    <span class="info-value">{{ $healthData->height_cm ? $healthData->height_cm . ' cm' : '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Berat Badan</span>
                    <span class="info-value">{{ $healthData->weight_kg ? $healthData->weight_kg . ' kg' : '-' }}</span>
                </div>
            </div>
        </div>

        {{-- ─── EDIT MODE ─── --}}
        <div id="editMode" class="edit-form">
            <form id="profileForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="file" id="avatarInput" name="avatar" style="display:none" accept="image/*">

                {{-- Informasi Pribadi (editable) --}}
                <div class="info-card" style="margin-bottom: 20px;">
                    <div class="info-card-title">Informasi Pribadi</div>
                    <div class="form-grid">

                        <div class="form-group full-width">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="name" class="form-input {{ $errors->has('name') ? 'error' : '' }}"
                                   value="{{ old('name', $profile->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="form-input"
                                   value="{{ old('birth_date', $profile->birth_date) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Usia *</label>
                            <input type="number" name="age" class="form-input {{ $errors->has('age') ? 'error' : '' }}"
                                   value="{{ old('age', $profile->age) }}" min="1" max="150" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select name="gender" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki" {{ old('gender', $profile->gender) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender', $profile->gender) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                <option value="Lainnya" {{ old('gender', $profile->gender) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="tel" name="phone" class="form-input"
                                   value="{{ old('phone', $profile->phone) }}" placeholder="+628...">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="address" class="form-input"
                                   value="{{ old('address', $profile->address) }}" placeholder="Jalan, Kota">
                        </div>
                    </div>
                </div>

                {{-- Informasi Kesehatan (editable) --}}
                <div class="info-card">
                    <div class="info-card-title">Informasi Kesehatan</div>
                    <div class="form-grid">

                        <div class="form-group">
                            <label class="form-label">Golongan Darah</label>
                            <select name="blood_type" class="form-select">
                                <option value="">-- Pilih --</option>
                                @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                    <option value="{{ $bt }}" {{ old('blood_type', $healthData->blood_type ?? '') === $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tinggi Badan (cm)</label>
                            <input type="number" name="height_cm" class="form-input" step="0.1" min="50" max="250"
                                   value="{{ old('height_cm', $healthData->height_cm ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Berat Badan (kg)</label>
                            <input type="number" name="weight_kg" class="form-input" step="0.1" min="1" max="500"
                                   value="{{ old('weight_kg', $healthData->weight_kg ?? '') }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alergi (pisahkan koma)</label>
                            <input type="text" name="allergies" class="form-input"
                                   value="{{ old('allergies', implode(', ', $healthData->allergies ?? [])) }}"
                                   placeholder="Kacang, Seafood, ...">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Penyakit Kronis (pisahkan koma)</label>
                            <input type="text" name="chronic_conditions" class="form-input"
                                   value="{{ old('chronic_conditions', implode(', ', $healthData->chronic_conditions ?? [])) }}"
                                   placeholder="Diabetes, Asma, ...">
                        </div>

                    </div>

                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleEditMode() {
    const viewMode        = document.getElementById('viewMode');
    const editMode        = document.getElementById('editMode');
    const btnEdit         = document.getElementById('topbarBtnEdit');
    const btnEditActions  = document.getElementById('topbarEditActions');
    const isEditing       = editMode.style.display === 'block';

    if (isEditing) {
        viewMode.style.display       = 'block';
        editMode.style.display       = 'none';
        btnEdit.style.display        = 'inline-block';
        btnEditActions.style.display = 'none';
    } else {
        viewMode.style.display       = 'none';
        editMode.style.display       = 'block';
        btnEdit.style.display        = 'none';
        btnEditActions.style.display = 'flex';
    }
}

// Auto-open edit mode if there were validation errors
@if($errors->any())
    toggleEditMode();
@endif

// Auto-calculate age from birth date
document.querySelector('[name="birth_date"]')?.addEventListener('change', function() {
    const birth = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    if (age > 0 && age < 150) {
        document.querySelector('[name="age"]').value = age;
    }
});

// Success notification auto-hide
const alert = document.querySelector('.alert-success');
if (alert) setTimeout(() => alert.style.opacity = '0', 3000);
</script>
@endsection
