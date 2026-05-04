@extends('layouts.app')

@section('title', $item['nama'] . ' - Katalog MEDISLOT')
@section('page-title', 'Detail Pemeriksaan')
@section('page-subtitle', $item['kategori'])

@section('topbar-actions')
    <a href="{{ route('katalog.index') }}"
       style="display:flex;align-items:center;gap:7px;color:#5a7a70;font-size:13.5px;font-weight:600;text-decoration:none;padding:7px 14px;background:#f0f4f3;border-radius:8px;transition:background 0.18s;"
       onmouseover="this.style.background='#dbeee7'" onmouseout="this.style.background='#f0f4f3'">
        <i class="fas fa-arrow-left"></i> Kembali ke Katalog
    </a>
@endsection

@section('extra-styles')
<style>
    .detail-wrap { max-width: 720px; }

    /* ── HEADER CARD ── */
    .detail-header {
        background: #fff;
        border-radius: 16px;
        padding: 28px 32px;
        display: flex;
        align-items: center;
        gap: 22px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        margin-bottom: 18px;
    }
    .detail-icon {
        width: 72px; height: 72px;
        border-radius: 18px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
        flex-shrink: 0;
    }
    .detail-header-info h2 {
        font-size: 22px;
        font-weight: 800;
        color: #1a3c34;
        margin-bottom: 6px;
    }
    .detail-meta { display: flex; gap: 10px; flex-wrap: wrap; }
    .meta-badge {
        display: flex; align-items: center; gap: 6px;
        font-size: 12.5px; font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }

    /* ── SECTION CARD ── */
    .detail-card {
        background: #fff;
        border-radius: 14px;
        padding: 24px 28px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        margin-bottom: 16px;
    }
    .detail-card h3 {
        font-size: 14.5px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-card h3 i { color: #2d9e72; }
    .detail-card p {
        font-size: 14px;
        color: #444;
        line-height: 1.75;
        margin: 0;
    }

    /* ── PERSIAPAN LIST ── */
    .persiapan-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .persiapan-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 14px;
        color: #444;
        line-height: 1.55;
    }
    .persiapan-num {
        width: 24px; height: 24px;
        background: #e8f5f0;
        color: #2d9e72;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 11.5px;
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* ── BIAYA ── */
    .biaya-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .biaya-range {
        font-size: 26px;
        font-weight: 800;
        color: #2d9e72;
    }
    .biaya-note {
        font-size: 12px;
        color: #7a9a90;
        margin-top: 4px;
    }
    .durasi-box {
        text-align: right;
    }
    .durasi-val {
        font-size: 16px;
        font-weight: 700;
        color: #1a3c34;
    }
    .durasi-label {
        font-size: 12px;
        color: #7a9a90;
        margin-top: 2px;
    }

    /* ── CTA BUTTON ── */
    .btn-reminder {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #2d9e72;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 13px 28px;
        font-size: 14.5px;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.18s;
        margin-top: 4px;
    }
    .btn-reminder:hover { background: #258a62; color: #fff; }

    /* ── TOAST ── */
    #toast {
        position: fixed;
        bottom: 28px; right: 28px;
        background: #1a3c34;
        color: #fff;
        padding: 14px 20px;
        border-radius: 12px;
        font-size: 13.5px;
        font-weight: 500;
        box-shadow: 0 6px 24px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        gap: 4px;
        z-index: 999;
        max-width: 320px;
    }
    #toast.show { display: flex; }
    #toast .t-top { display: flex; align-items: center; gap: 8px; font-weight: 700; }
    #toast .t-top i { color: #2d9e72; font-size: 16px; }
    #toast .t-sub { font-size: 12.5px; opacity: 0.8; padding-left: 24px; }
</style>
@endsection

@section('content')
<div class="detail-wrap">

    {{-- HEADER --}}
    <div class="detail-header">
        <div class="detail-icon"
             style="background: {{ $item['bg_color'] }}; color: {{ $item['icon_color'] }};">
            <i class="fas {{ $item['icon'] }}"></i>
        </div>
        <div class="detail-header-info">
            <h2>{{ $item['nama'] }}</h2>
            <div class="detail-meta">
                <span class="meta-badge" style="background:#e8f5f0; color:#1a7a58;">
                    <i class="fas fa-tag" style="font-size:10px;"></i>{{ $item['kategori'] }}
                </span>
                <span class="meta-badge" style="background:#f0f4f3; color:#5a7a70;">
                    <i class="fas fa-clock" style="font-size:10px;"></i>{{ $item['durasi'] }}
                </span>
            </div>
        </div>
    </div>

    {{-- DESKRIPSI --}}
    <div class="detail-card">
        <h3><i class="fas fa-info-circle"></i>Tentang Pemeriksaan Ini</h3>
        <p>{{ $item['deskripsi'] }}</p>
    </div>

    {{-- PERSIAPAN --}}
    <div class="detail-card">
        <h3><i class="fas fa-clipboard-list"></i>Persiapan Sebelum Pemeriksaan</h3>
        <div class="persiapan-list">
            @foreach($item['persiapan'] as $i => $prep)
            <div class="persiapan-item">
                <div class="persiapan-num">{{ $i + 1 }}</div>
                <span>{{ $prep }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ESTIMASI BIAYA --}}
    <div class="detail-card">
        <h3><i class="fas fa-wallet"></i>Estimasi Biaya</h3>
        <div class="biaya-wrap">
            <div>
                <div class="biaya-range">
                    Rp {{ number_format($item['biaya_min'], 0, ',', '.') }}
                    – Rp {{ number_format($item['biaya_max'], 0, ',', '.') }}
                </div>
                <div class="biaya-note">
                    * Estimasi biaya dapat berbeda tergantung fasilitas kesehatan
                </div>
            </div>
            <div class="durasi-box">
                <div class="durasi-val">{{ $item['durasi'] }}</div>
                <div class="durasi-label">Estimasi durasi</div>
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <button class="btn-reminder"
        onclick="setReminder('{{ $item['key'] }}', '{{ $item['nama'] }}')">
        <i class="fas fa-bell"></i>
        Buat Pengingat untuk Pemeriksaan Ini
    </button>

</div>

{{-- TOAST --}}
<div id="toast">
    <div class="t-top"><i class="fas fa-check-circle"></i><span id="t-title"></span></div>
    <div class="t-sub" id="t-sub"></div>
</div>
@endsection

@section('scripts')
<script>
function setReminder(key, name) {
    const STORAGE_KEY = 'medislot_reminders';
    const intervals   = {
        'ekg': 180, 'ekokardiografi': 365, 'fisik-umum': 365,
        'scaling': 180, 'endoskopi-tht': 365, 'visus-mata': 365,
        'glaukoma': 365, 'darah-lengkap': 365, 'gula-kolesterol': 180,
        'dermatologi': 365, 'usg-abdomen': 365, 'rontgen': 365,
    };
    const days   = intervals[key] || 365;
    const today  = new Date();
    const target = new Date(today);
    target.setDate(today.getDate() + days);

    const months   = ['Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];
    const dateStr  = target.toISOString().split('T')[0];
    const dateDisp = `${target.getDate()} ${months[target.getMonth()]} ${target.getFullYear()}`;

    const reminders = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    reminders[key]  = { targetDate: dateStr, name };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(reminders));

    const diffDays = Math.ceil((target - today) / (1000 * 60 * 60 * 24));
    document.getElementById('t-title').textContent = `Pengingat "${name}" aktif!`;
    document.getElementById('t-sub').textContent   =
        `Check-up berikutnya: ${dateDisp} — ${diffDays} hari lagi`;

    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}
</script>
@endsection
