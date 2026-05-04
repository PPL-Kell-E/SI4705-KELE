@extends('layouts.app')

@section('title', 'Data Kesehatan - MEDISLOT')
@section('page-title', 'Data Kesehatan')
@section('page-subtitle', 'Catatan personal premium yang peduli pada kondisi tubuh Anda.')

@php
    $bmi         = $healthData?->bmi;
    $bmiCategory = $healthData?->bmi_category ?? '-';
    $bmiColor    = match(true) {
        !$bmi              => '#7a9a90',
        $bmi < 18.5        => '#3a7abf',
        $bmi < 25.0        => '#2d9e72',
        $bmi < 30.0        => '#f0a500',
        default            => '#e05252',
    };
    $bmiPercent = $bmi ? min(100, max(0, (($bmi - 10) / 35) * 100)) : 0;

    $fields      = ['height_cm','weight_kg','blood_type','allergies','chronic_conditions','recorded_at'];
    $filled      = collect($fields)->filter(fn($f) => !empty($healthData?->$f))->count();
    $completePct = $healthData ? round(($filled / count($fields)) * 100) : 0;

    $daysSince   = $healthData?->recorded_at ? now()->diffInDays($healthData->recorded_at) : null;
    $checkupAlert = $daysSince !== null && $daysSince > 180;
@endphp

@section('extra-styles')
<style>
    .dk-wrap { max-width: 760px; }

    /* ── TOP STATS ROW ── */
    .dk-stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
        margin-bottom: 18px;
    }
    .dk-stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .dk-stat-label {
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: #7a9a90; margin-bottom: 8px;
    }
    .dk-stat-val {
        font-size: 28px; font-weight: 800; line-height: 1;
        margin-bottom: 4px;
    }
    .dk-stat-sub { font-size: 12px; color: #7a9a90; }

    /* BMI bar */
    .bmi-bar-bg {
        height: 6px; border-radius: 3px;
        background: #f0f4f3; margin-top: 10px; overflow: hidden;
    }
    .bmi-bar-fill { height: 100%; border-radius: 3px; transition: width 0.6s ease; }

    /* Completeness ring */
    .complete-ring-wrap {
        display: flex; align-items: center; gap: 14px; margin-top: 6px;
    }
    .ring-svg { flex-shrink: 0; }
    .ring-info .ring-pct { font-size: 22px; font-weight: 800; color: #1a3c34; }
    .ring-info .ring-sub { font-size: 12px; color: #7a9a90; }

    /* Last checkup stat */
    .checkup-days { font-size: 26px; font-weight: 800; }

    /* ── ALERT ── */
    .dk-alert {
        border-radius: 12px; padding: 14px 18px;
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 16px; font-size: 13.5px; font-weight: 500;
    }
    .dk-alert i { font-size: 16px; flex-shrink: 0; }
    .dk-alert.warn  { background: #fef3cd; color: #9a6700; border-left: 4px solid #f0a500; }
    .dk-alert.info  { background: #e8f0f5; color: #1a4d80; border-left: 4px solid #3a7abf; }
    .dk-alert.danger{ background: #fde8e8; color: #8b0000; border-left: 4px solid #e05252; }

    /* ── MAIN CARD ── */
    .dk-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.07);
        overflow: hidden; margin-bottom: 20px;
    }
    .dk-card-top { height: 6px; background: linear-gradient(90deg, #1a3c34, #2d9e72); }
    .dk-card-header {
        display: flex; align-items: center; gap: 18px;
        padding: 22px 28px 18px;
        border-bottom: 1px solid #f0f4f3;
    }
    .dk-card-header .hdr-icon {
        width: 52px; height: 52px; background: #e8f5f0;
        border-radius: 12px; display: flex; align-items: center;
        justify-content: center; color: #2d9e72; font-size: 22px; flex-shrink: 0;
    }
    .dk-card-header h3 { font-size: 17px; font-weight: 800; color: #1a1a1a; margin-bottom: 3px; }
    .dk-card-header p  { font-size: 13px; color: #7a9a90; margin: 0; }

    .dk-section { padding: 20px 28px; border-bottom: 1px solid #f0f4f3; }
    .dk-section:last-child { border-bottom: none; }
    .dk-section-title {
        display: flex; align-items: center; gap: 9px;
        font-size: 12px; font-weight: 800; color: #2d9e72;
        letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 16px;
    }
    .dk-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; }
    .dk-field label {
        display: block; font-size: 11px; font-weight: 800;
        color: #333; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 7px;
    }
    .dk-display {
        background: #f0f4f3; border-radius: 10px;
        padding: 13px 16px; font-size: 14px; color: #1a3c34;
        font-weight: 600; min-height: 46px;
        display: flex; align-items: center; gap: 8px;
    }
    .dk-display.empty { color: #bbb; font-weight: 400; }
    .dk-display i { color: #7a9a90; font-size: 15px; }

    /* ── ACTION ROW ── */
    .dk-actions {
        display: flex; align-items: center; justify-content: center;
        gap: 12px; flex-wrap: wrap;
    }
    .btn-dk {
        background: #1a3c34; color: #fff; border: none;
        border-radius: 30px; padding: 13px 44px;
        font-size: 14.5px; font-weight: 700;
        font-family: 'Inter', sans-serif; cursor: pointer; transition: background 0.18s;
    }
    .btn-dk:hover { background: #2d9e72; }
    .btn-export {
        background: #fff; color: #1a3c34;
        border: 2px solid #d0e4de; border-radius: 30px;
        padding: 11px 24px; font-size: 13.5px; font-weight: 700;
        font-family: 'Inter', sans-serif; cursor: pointer;
        transition: all 0.18s; text-decoration: none;
        display: flex; align-items: center; gap: 7px;
    }
    .btn-export:hover { border-color: #2d9e72; color: #2d9e72; }

    /* ── MODAL OVERLAY ── */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.45); z-index: 200;
        align-items: center; justify-content: center;
    }
    .modal-overlay.show { display: flex; }

    .modal-box {
        background: #fff; border-radius: 20px;
        width: 100%; max-width: 600px; padding: 32px 36px;
        position: relative; max-height: 90vh; overflow-y: auto;
        animation: fadeUp 0.22s ease;
    }
    @keyframes fadeUp {
        from { opacity:0; transform:translateY(16px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .modal-box h2 { font-size: 24px; font-weight: 800; color: #1a1a1a; margin-bottom: 24px; }
    .modal-close {
        position: absolute; top: 20px; right: 24px;
        background: none; border: none; font-size: 22px;
        color: #777; cursor: pointer; line-height: 1; transition: color 0.15s;
    }
    .modal-close:hover { color: #333; }

    .modal-section { margin-bottom: 22px; }
    .modal-section-title {
        display: flex; align-items: center; gap: 8px;
        font-size: 11.5px; font-weight: 800; color: #2d9e72;
        letter-spacing: 0.6px; text-transform: uppercase; margin-bottom: 14px;
    }
    .modal-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
    .modal-field label {
        display: block; font-size: 11px; font-weight: 800;
        color: #333; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 7px;
    }
    .modal-field input, .modal-field select {
        width: 100%; background: #f5f7f6;
        border: 1.5px solid #e0e8e5; border-radius: 10px;
        padding: 13px 14px; font-size: 14px;
        font-family: 'Inter', sans-serif; color: #1a1a1a;
        outline: none; transition: border-color 0.18s;
    }
    .modal-field input:focus, .modal-field select:focus {
        border-color: #2d9e72; background: #fff;
    }
    .modal-field .input-icon-wrap { position: relative; }
    .modal-field .input-icon-wrap input { padding-right: 40px; }
    .modal-field .input-icon-wrap i {
        position: absolute; right: 13px; top: 50%;
        transform: translateY(-50%); color: #7a9a90; pointer-events: none;
    }
    .modal-actions {
        display: flex; gap: 12px; margin-top: 28px; justify-content: center;
    }
    .btn-modal-cancel {
        flex: 1; padding: 14px; background: #fff;
        border: 2px solid #e0e8e5; border-radius: 30px;
        font-size: 14.5px; font-weight: 700;
        font-family: 'Inter', sans-serif; cursor: pointer;
        color: #333; transition: border-color 0.18s;
    }
    .btn-modal-cancel:hover { border-color: #2d9e72; color: #2d9e72; }
    .btn-modal-submit {
        flex: 1; padding: 14px; background: #1a3c34; border: none;
        border-radius: 30px; font-size: 14.5px; font-weight: 700;
        font-family: 'Inter', sans-serif; cursor: pointer;
        color: #fff; transition: background 0.18s;
    }
    .btn-modal-submit:hover { background: #2d9e72; }

    /* ── SUCCESS MODAL ── */
    .success-box {
        background: #fff; border-radius: 20px;
        width: 100%; max-width: 340px; padding: 36px 32px 28px;
        text-align: center; position: relative; animation: fadeUp 0.22s ease;
    }
    .success-box .s-close {
        position: absolute; top: 16px; right: 20px;
        background: none; border: none; font-size: 20px; color: #aaa; cursor: pointer;
    }
    .success-icon {
        width: 56px; height: 56px; background: #e8f5f0; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px; font-size: 24px; color: #2d9e72;
    }
    .success-box p { font-size: 15px; font-weight: 600; color: #1a1a1a; margin-bottom: 24px; }
    .btn-continue {
        width: 100%; padding: 14px; background: #2d9e72; color: #fff; border: none;
        border-radius: 30px; font-size: 14.5px; font-weight: 700;
        font-family: 'Inter', sans-serif; cursor: pointer; transition: background 0.18s;
    }
    .btn-continue:hover { background: #1a7a58; }
</style>
@endsection

@section('content')
<div class="dk-wrap">

    {{-- ── TOP STATS (hanya jika data ada) ── --}}
    @if($healthData)
    <div class="dk-stats-row">

        {{-- BMI Card --}}
        <div class="dk-stat-card">
            <div class="dk-stat-label">Indeks Massa Tubuh</div>
            @if($bmi)
                <div class="dk-stat-val" style="color: {{ $bmiColor }};">{{ $bmi }}</div>
                <div class="dk-stat-sub">{{ $bmiCategory }}</div>
                <div class="bmi-bar-bg">
                    <div class="bmi-bar-fill"
                         style="width: {{ $bmiPercent }}%; background: {{ $bmiColor }};"></div>
                </div>
            @else
                <div class="dk-stat-val" style="color:#ccc;">—</div>
                <div class="dk-stat-sub">Isi tinggi & berat badan</div>
            @endif
        </div>

        {{-- Kelengkapan Data --}}
        <div class="dk-stat-card">
            <div class="dk-stat-label">Kelengkapan Data</div>
            <div class="complete-ring-wrap">
                <svg class="ring-svg" width="52" height="52" viewBox="0 0 52 52">
                    <circle cx="26" cy="26" r="22" fill="none" stroke="#f0f4f3" stroke-width="5"/>
                    <circle cx="26" cy="26" r="22" fill="none" stroke="#2d9e72" stroke-width="5"
                        stroke-dasharray="{{ round(2 * 3.14159 * 22) }}"
                        stroke-dashoffset="{{ round(2 * 3.14159 * 22 * (1 - $completePct/100)) }}"
                        stroke-linecap="round"
                        transform="rotate(-90 26 26)"/>
                </svg>
                <div class="ring-info">
                    <div class="ring-pct">{{ $completePct }}%</div>
                    <div class="ring-sub">{{ $filled }}/{{ count($fields) }} field terisi</div>
                </div>
            </div>
        </div>

        {{-- Terakhir Check Up --}}
        <div class="dk-stat-card">
            <div class="dk-stat-label">Terakhir Check Up</div>
            @if($daysSince !== null)
                <div class="checkup-days" style="color: {{ $daysSince > 180 ? '#e05252' : '#2d9e72' }};">
                    {{ $daysSince }} hari
                </div>
                <div class="dk-stat-sub">yang lalu ({{ $healthData->recorded_at->format('d M Y') }})</div>
            @else
                <div class="dk-stat-val" style="color:#ccc;">—</div>
                <div class="dk-stat-sub">Belum ada data</div>
            @endif
        </div>

    </div>

    {{-- ── HEALTH ALERTS ── --}}
    @if($bmi && $bmi < 18.5)
        <div class="dk-alert info">
            <i class="fas fa-circle-info"></i>
            <span>BMI kamu di bawah normal (<strong>{{ $bmi }}</strong>). Pertimbangkan konsultasi gizi untuk program penambahan berat badan yang sehat.</span>
        </div>
    @elseif($bmi && $bmi >= 25 && $bmi < 30)
        <div class="dk-alert warn">
            <i class="fas fa-triangle-exclamation"></i>
            <span>BMI kamu masuk kategori <strong>Kelebihan Berat Badan</strong> ({{ $bmi }}). Disarankan pemeriksaan kesehatan jantung dan pola makan seimbang.</span>
        </div>
    @elseif($bmi && $bmi >= 30)
        <div class="dk-alert danger">
            <i class="fas fa-heart-pulse"></i>
            <span>BMI kamu masuk kategori <strong>Obesitas</strong> ({{ $bmi }}). Segera konsultasikan dengan dokter untuk program penurunan berat badan.</span>
        </div>
    @endif

    @if($checkupAlert)
        <div class="dk-alert warn">
            <i class="fas fa-calendar-xmark"></i>
            <span>Sudah <strong>{{ $daysSince }} hari</strong> sejak check up terakhirmu. Disarankan melakukan pemeriksaan rutin minimal setiap 6 bulan.</span>
        </div>
    @endif
    @endif

    {{-- ── MAIN CARD ── --}}
    <div class="dk-card">
        <div class="dk-card-top"></div>

        <div class="dk-card-header">
            <div class="hdr-icon"><i class="fas fa-book-medical"></i></div>
            <div>
                <h3>Buku Catatan Medis</h3>
                <p>Lengkapi data di bawah ini untuk mendapatkan pantauan kondisi tubuh yang lebih akurat.</p>
            </div>
        </div>

        <div class="dk-section">
            <div class="dk-section-title"><i class="fas fa-ruler-combined"></i> Pengukuran Fisik</div>
            <div class="dk-fields">
                <div class="dk-field">
                    <label>Tinggi Badan (CM)</label>
                    <div class="dk-display {{ $healthData?->height_cm ? '' : 'empty' }}">
                        {{ $healthData?->height_cm ? number_format($healthData->height_cm, 0) : '' }}
                    </div>
                </div>
                <div class="dk-field">
                    <label>Berat Badan (KG)</label>
                    <div class="dk-display {{ $healthData?->weight_kg ? '' : 'empty' }}">
                        {{ $healthData?->weight_kg ? number_format($healthData->weight_kg, 0) : '' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="dk-section">
            <div class="dk-section-title"><i class="fas fa-circle-info"></i> Profil Kesehatan</div>
            <div class="dk-fields">
                <div class="dk-field">
                    <label>Golongan Darah</label>
                    <div class="dk-display {{ $healthData?->blood_type ? '' : 'empty' }}">
                        {{ $healthData?->blood_type ?? '' }}
                    </div>
                </div>
                <div class="dk-field">
                    <label>Alergi</label>
                    <div class="dk-display {{ !empty($healthData?->allergies) ? '' : 'empty' }}">
                        {{ !empty($healthData?->allergies) ? implode(', ', $healthData->allergies) : '' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="dk-section">
            <div class="dk-section-title"><i class="fas fa-clock-rotate-left"></i> Riwayat & Kondisi</div>
            <div class="dk-fields">
                <div class="dk-field">
                    <label>Terakhir Check Up</label>
                    <div class="dk-display {{ $healthData?->recorded_at ? '' : 'empty' }}">
                        @if($healthData?->recorded_at)
                            <i class="fas fa-calendar-alt"></i>
                            {{ $healthData->recorded_at->format('d/m/Y') }}
                        @else
                            <i class="fas fa-calendar-alt"></i>
                        @endif
                    </div>
                </div>
                <div class="dk-field">
                    <label>Riwayat Penyakit</label>
                    <div class="dk-display {{ !empty($healthData?->chronic_conditions) ? '' : 'empty' }}">
                        {{ !empty($healthData?->chronic_conditions) ? implode(', ', $healthData->chronic_conditions) : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="dk-actions">
        <button class="btn-dk" onclick="openModal()">
            {{ $healthData ? 'Edit catatan' : 'Buat Catatan Baru' }}
        </button>
        @if($healthData)
            <a href="{{ route('data-kesehatan.export') }}" target="_blank" class="btn-export">
                <i class="fas fa-file-arrow-down"></i> Export PDF
            </a>
        @endif
    </div>

</div>

{{-- ── INPUT MODAL ── --}}
<div class="modal-overlay" id="inputModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">&#x2715;</button>
        <h2>Data Kesehatan</h2>
        <form id="healthForm">
            @csrf
            <div class="modal-section">
                <div class="modal-section-title"><i class="fas fa-ruler-combined"></i> Pengukuran Fisik</div>
                <div class="modal-fields">
                    <div class="modal-field">
                        <label>Tinggi Badan (CM)</label>
                        <input type="number" name="height_cm" placeholder="Contoh: 165"
                               min="50" max="250" step="0.1"
                               value="{{ $healthData?->height_cm ? number_format($healthData->height_cm, 0) : '' }}">
                    </div>
                    <div class="modal-field">
                        <label>Berat Badan (KG)</label>
                        <input type="number" name="weight_kg" placeholder="Contoh: 60"
                               min="1" max="500" step="0.1"
                               value="{{ $healthData?->weight_kg ? number_format($healthData->weight_kg, 0) : '' }}">
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title"><i class="fas fa-circle-info"></i> Profil Kesehatan</div>
                <div class="modal-fields">
                    <div class="modal-field">
                        <label>Golongan Darah</label>
                        <select name="blood_type">
                            <option value="">Pilih golongan darah</option>
                            @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                <option value="{{ $bt }}" {{ ($healthData?->blood_type === $bt) ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-field">
                        <label>Alergi</label>
                        <input type="text" name="allergies" placeholder="Contoh: Kacang, Debu"
                               value="{{ !empty($healthData?->allergies) ? implode(', ', $healthData->allergies) : '' }}">
                    </div>
                </div>
            </div>

            <div class="modal-section">
                <div class="modal-section-title"><i class="fas fa-clock-rotate-left"></i> Riwayat & Kondisi</div>
                <div class="modal-fields">
                    <div class="modal-field">
                        <label>Terakhir Check Up</label>
                        <div class="input-icon-wrap">
                            <input type="date" name="recorded_at"
                                   value="{{ $healthData?->recorded_at?->format('Y-m-d') ?? '' }}">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>Riwayat Penyakit</label>
                        <input type="text" name="chronic_conditions" placeholder="Contoh: Asma, Diabetes"
                               value="{{ !empty($healthData?->chronic_conditions) ? implode(', ', $healthData->chronic_conditions) : '' }}">
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeModal()">Edit Data</button>
                <button type="submit" class="btn-modal-submit">Selesai</button>
            </div>
        </form>
    </div>
</div>

{{-- ── SUCCESS MODAL ── --}}
<div class="modal-overlay" id="successModal">
    <div class="success-box">
        <button class="s-close" onclick="closeSuccess()">&#x2715;</button>
        <div class="success-icon"><i class="fas fa-check"></i></div>
        <p id="successMsg">Data berhasil disimpan</p>
        <button class="btn-continue" onclick="continueAfterSuccess()">Continue</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const STORE_URL = '{{ route("data-kesehatan.store") }}';
    const CSRF      = '{{ csrf_token() }}';

    function openModal()  { document.getElementById('inputModal').classList.add('show'); document.body.style.overflow = 'hidden'; }
    function closeModal() { document.getElementById('inputModal').classList.remove('show'); document.body.style.overflow = ''; }
    function closeSuccess(){ document.getElementById('successModal').classList.remove('show'); document.body.style.overflow = ''; }
    function continueAfterSuccess() { closeSuccess(); window.location.reload(); }

    document.getElementById('healthForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('.btn-modal-submit');
        btn.disabled = true; btn.textContent = 'Menyimpan...';
        const formData = new FormData(this);
        formData.append('_token', CSRF);
        try {
            const res  = await fetch(STORE_URL, { method: 'POST', body: formData });
            const data = await res.json();
            if (res.ok && data.status === 'ok') {
                closeModal();
                document.getElementById('successMsg').textContent = data.message;
                document.getElementById('successModal').classList.add('show');
                document.body.style.overflow = 'hidden';
            } else { alert('Terjadi kesalahan. Silakan coba lagi.'); }
        } catch { alert('Gagal menghubungi server.'); }
        finally { btn.disabled = false; btn.textContent = 'Selesai'; }
    });

    document.getElementById('inputModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    document.getElementById('successModal').addEventListener('click', function(e) { if (e.target === this) closeSuccess(); });
</script>
@endsection
