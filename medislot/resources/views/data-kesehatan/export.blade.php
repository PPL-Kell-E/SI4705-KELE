<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis - {{ $user->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1a1a1a; font-size: 13px; }

        .page { max-width: 700px; margin: 0 auto; padding: 40px 48px; }

        /* Header */
        .doc-header {
            display: flex; align-items: center; justify-content: space-between;
            padding-bottom: 20px; border-bottom: 2px solid #1a3c34; margin-bottom: 28px;
        }
        .doc-brand { display: flex; align-items: center; gap: 10px; }
        .doc-brand .brand-icon {
            width: 38px; height: 38px; background: #1a3c34;
            border-radius: 8px; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 18px; font-weight: 800;
        }
        .doc-brand .brand-name { font-size: 20px; font-weight: 800; color: #1a3c34; letter-spacing: 0.5px; }
        .doc-meta { text-align: right; font-size: 11px; color: #7a9a90; line-height: 1.6; }

        /* Title */
        .doc-title { font-size: 18px; font-weight: 800; color: #1a3c34; margin-bottom: 4px; }
        .doc-subtitle { font-size: 12px; color: #7a9a90; margin-bottom: 28px; }

        /* Patient info */
        .patient-box {
            background: #f0f9f5; border-left: 4px solid #2d9e72;
            border-radius: 0 10px 10px 0; padding: 14px 18px;
            margin-bottom: 24px; display: flex; gap: 32px;
        }
        .patient-item .p-label { font-size: 10px; font-weight: 700; color: #5a7a70; text-transform: uppercase; letter-spacing: 0.5px; }
        .patient-item .p-val   { font-size: 14px; font-weight: 700; color: #1a3c34; margin-top: 2px; }

        /* BMI highlight */
        .bmi-box {
            border: 2px solid #2d9e72; border-radius: 12px;
            padding: 16px 20px; margin-bottom: 24px;
            display: flex; align-items: center; gap: 24px;
        }
        .bmi-main .bmi-num { font-size: 36px; font-weight: 800; color: #2d9e72; line-height: 1; }
        .bmi-main .bmi-label { font-size: 11px; color: #7a9a90; text-transform: uppercase; letter-spacing: 0.5px; }
        .bmi-detail { flex: 1; }
        .bmi-detail .bmi-cat { font-size: 15px; font-weight: 700; color: #1a3c34; margin-bottom: 4px; }
        .bmi-detail .bmi-desc { font-size: 12px; color: #5a7a70; line-height: 1.5; }
        .bmi-bar-bg { height: 8px; background: #e0ede9; border-radius: 4px; margin-top: 8px; overflow: hidden; }
        .bmi-bar-fill { height: 100%; background: #2d9e72; border-radius: 4px; }

        /* Sections */
        .section { margin-bottom: 22px; }
        .section-title {
            font-size: 10.5px; font-weight: 800; color: #2d9e72;
            text-transform: uppercase; letter-spacing: 0.8px;
            padding-bottom: 6px; border-bottom: 1px solid #e0ede9; margin-bottom: 14px;
        }
        .fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 24px; }
        .field-item .f-label { font-size: 10px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 3px; }
        .field-item .f-val   { font-size: 13.5px; font-weight: 600; color: #1a1a1a; }
        .field-item .f-val.empty { color: #ccc; font-style: italic; font-weight: 400; }

        /* Footer */
        .doc-footer {
            margin-top: 36px; padding-top: 16px; border-top: 1px solid #e0ede9;
            display: flex; justify-content: space-between; align-items: flex-end;
        }
        .doc-footer .note { font-size: 10.5px; color: #aaa; line-height: 1.5; max-width: 340px; }
        .doc-footer .page-num { font-size: 11px; color: #bbb; }

        /* Print button (hidden on print) */
        .print-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: #1a3c34; color: #fff;
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 32px; gap: 12px; z-index: 100;
        }
        .print-bar span { font-size: 13.5px; font-weight: 600; }
        .print-bar .btn-print {
            background: #2d9e72; color: #fff; border: none;
            border-radius: 8px; padding: 9px 22px; font-size: 13.5px;
            font-weight: 700; cursor: pointer; font-family: inherit;
        }
        .print-bar .btn-close {
            background: rgba(255,255,255,0.15); color: #fff; border: none;
            border-radius: 8px; padding: 9px 16px; font-size: 13px;
            cursor: pointer; font-family: inherit;
        }

        @media print {
            .print-bar { display: none !important; }
            body { padding-top: 0; }
            .page { padding: 24px 32px; }
        }
    </style>
</head>
<body style="padding-top: 60px;">

{{-- Print bar (hidden on print) --}}
<div class="print-bar">
    <span>Pratinjau Dokumen Rekam Medis</span>
    <div style="display:flex;gap:8px;">
        <button class="btn-close" onclick="window.close()">&#x2715; Tutup</button>
        <button class="btn-print" onclick="window.print()">&#x2399; Cetak / Simpan PDF</button>
    </div>
</div>

<div class="page">

    {{-- Document header --}}
    <div class="doc-header">
        <div class="doc-brand">
            <div class="brand-icon">M</div>
            <div class="brand-name">MEDISLOT</div>
        </div>
        <div class="doc-meta">
            <div>Tanggal Cetak: {{ now()->translatedFormat('d F Y') }}</div>
            <div>Dokumen: Rekam Data Kesehatan Dasar</div>
        </div>
    </div>

    <div class="doc-title">Rekam Data Kesehatan</div>
    <div class="doc-subtitle">Catatan medis personal yang dibuat melalui aplikasi MediSlot</div>

    {{-- Patient info --}}
    <div class="patient-box">
        <div class="patient-item">
            <div class="p-label">Nama Pasien</div>
            <div class="p-val">{{ $user->full_name ?? $user->name }}</div>
        </div>
        <div class="patient-item">
            <div class="p-label">Email</div>
            <div class="p-val">{{ $user->email }}</div>
        </div>
        <div class="patient-item">
            <div class="p-label">Tanggal Check Up</div>
            <div class="p-val">{{ $healthData?->recorded_at?->translatedFormat('d F Y') ?? '—' }}</div>
        </div>
    </div>

    {{-- BMI highlight --}}
    @if($healthData?->bmi)
    @php
        $bmi    = $healthData->bmi;
        $bmiPct = min(100, max(0, (($bmi - 10) / 35) * 100));
        $bmiDesc = match(true) {
            $bmi < 18.5 => 'Berat badan kurang dari ideal. Disarankan konsultasi gizi.',
            $bmi < 25.0 => 'Berat badan dalam rentang normal. Pertahankan gaya hidup sehat.',
            $bmi < 30.0 => 'Berat badan melebihi batas normal. Perhatikan pola makan dan aktivitas fisik.',
            default     => 'Masuk kategori obesitas. Segera konsultasikan dengan dokter.',
        };
    @endphp
    <div class="bmi-box">
        <div class="bmi-main">
            <div class="bmi-num">{{ $bmi }}</div>
            <div class="bmi-label">BMI</div>
        </div>
        <div class="bmi-detail">
            <div class="bmi-cat">{{ $healthData->bmi_category }}</div>
            <div class="bmi-desc">{{ $bmiDesc }}</div>
            <div class="bmi-bar-bg"><div class="bmi-bar-fill" style="width:{{ $bmiPct }}%;"></div></div>
        </div>
    </div>
    @endif

    {{-- Pengukuran Fisik --}}
    <div class="section">
        <div class="section-title">Pengukuran Fisik</div>
        <div class="fields-grid">
            <div class="field-item">
                <div class="f-label">Tinggi Badan</div>
                <div class="f-val {{ $healthData?->height_cm ? '' : 'empty' }}">
                    {{ $healthData?->height_cm ? number_format($healthData->height_cm, 0).' cm' : 'Belum diisi' }}
                </div>
            </div>
            <div class="field-item">
                <div class="f-label">Berat Badan</div>
                <div class="f-val {{ $healthData?->weight_kg ? '' : 'empty' }}">
                    {{ $healthData?->weight_kg ? number_format($healthData->weight_kg, 0).' kg' : 'Belum diisi' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Profil Kesehatan --}}
    <div class="section">
        <div class="section-title">Profil Kesehatan</div>
        <div class="fields-grid">
            <div class="field-item">
                <div class="f-label">Golongan Darah</div>
                <div class="f-val {{ $healthData?->blood_type ? '' : 'empty' }}">
                    {{ $healthData?->blood_type ?? 'Belum diisi' }}
                </div>
            </div>
            <div class="field-item">
                <div class="f-label">Alergi</div>
                <div class="f-val {{ !empty($healthData?->allergies) ? '' : 'empty' }}">
                    {{ !empty($healthData?->allergies) ? implode(', ', $healthData->allergies) : 'Tidak ada' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat & Kondisi --}}
    <div class="section">
        <div class="section-title">Riwayat & Kondisi</div>
        <div class="fields-grid">
            <div class="field-item">
                <div class="f-label">Terakhir Check Up</div>
                <div class="f-val {{ $healthData?->recorded_at ? '' : 'empty' }}">
                    {{ $healthData?->recorded_at?->translatedFormat('d F Y') ?? 'Belum diisi' }}
                </div>
            </div>
            <div class="field-item">
                <div class="f-label">Riwayat Penyakit</div>
                <div class="f-val {{ !empty($healthData?->chronic_conditions) ? '' : 'empty' }}">
                    {{ !empty($healthData?->chronic_conditions) ? implode(', ', $healthData->chronic_conditions) : 'Tidak ada' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="doc-footer">
        <div class="note">
            Dokumen ini digenerate secara otomatis oleh MediSlot.<br>
            Bukan merupakan dokumen medis resmi. Konsultasikan kondisi kesehatan Anda dengan tenaga medis profesional.
        </div>
        <div class="page-num">MediSlot &copy; {{ now()->year }}</div>
    </div>

</div>
</body>
</html>
