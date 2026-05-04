@extends('layouts.app')

@section('title', 'Rekomendasi Pemeriksaan - MEDISLOT')
@section('page-title', 'Rekomendasi Pemeriksaan')
@section('page-subtitle', 'Klik "Buat pengingat" setelah kamu selesai melakukan pemeriksaan')

@section('extra-styles')
<style>
    .rekomen-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        max-width: 860px;
    }

    /* ── CARD ── */
    .rekomen-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        transition: box-shadow 0.2s, border-color 0.3s;
        border-left: 4px solid #e2e8e6;
        position: relative;
        overflow: hidden;
    }
    .rekomen-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,0.09); }
    .rekomen-card.active { border-left-color: #2d9e72; background: #f9fefb; }
    .rekomen-card.active.urgent { border-left-color: #e05252; background: #fffafa; }
    .rekomen-card.active.soon   { border-left-color: #f0a500; background: #fffdf5; }

    .rekomen-icon {
        width: 54px; height: 54px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .rekomen-info { flex: 1; min-width: 0; }
    .rekomen-info h3 {
        font-size: 15px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 3px;
    }
    .rekomen-info .desc {
        font-size: 12.5px;
        color: #7a9a90;
        margin: 0;
    }

    /* ── COUNTDOWN BLOCK (tampil saat aktif) ── */
    .rekomen-countdown {
        display: none;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
        flex-shrink: 0;
        min-width: 190px;
    }
    .rekomen-card.active .rekomen-countdown { display: flex; }
    .rekomen-card.active .btn-pengingat-wrap { display: none; }

    .countdown-next {
        font-size: 12px;
        color: #5a7a70;
        display: flex; align-items: center; gap: 5px;
    }
    .countdown-next i { font-size: 11px; }

    .countdown-days {
        font-size: 13px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .countdown-days.normal { background: #e8f5f0; color: #1a7a58; }
    .countdown-days.soon   { background: #fef3cd; color: #9a6700; }
    .countdown-days.urgent { background: #fde8e8; color: #c0392b; }

    .btn-cancel {
        font-size: 11.5px;
        color: #aaa;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        font-family: inherit;
        text-decoration: underline;
        margin-top: 2px;
    }
    .btn-cancel:hover { color: #e05252; }

    /* ── BUTTON (tampil saat belum aktif) ── */
    .btn-pengingat-wrap { flex-shrink: 0; }
    .btn-pengingat {
        background: #2d9e72;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.18s;
    }
    .btn-pengingat:hover { background: #258a62; }

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
        animation: slideUp 0.3s ease;
    }
    #toast.show { display: flex; }
    #toast .toast-top {
        display: flex; align-items: center; gap: 8px;
        font-weight: 700; font-size: 14px;
    }
    #toast .toast-top i { color: #2d9e72; font-size: 16px; }
    #toast .toast-sub { font-size: 12.5px; opacity: 0.8; padding-left: 24px; }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div class="rekomen-list">
    @foreach ($recommendations as $item)
    <div class="rekomen-card" id="card-{{ $item['key'] }}">

        {{-- Icon --}}
        <div class="rekomen-icon"
             style="background: {{ $item['bg_color'] }}; color: {{ $item['icon_color'] }};">
            <i class="fas {{ $item['icon'] }}"></i>
        </div>

        {{-- Info --}}
        <div class="rekomen-info">
            <h3>{{ $item['name'] }}</h3>
            <p class="desc">{{ $item['description'] }}</p>
        </div>

        {{-- Countdown (muncul setelah pengingat aktif) --}}
        <div class="rekomen-countdown" id="countdown-{{ $item['key'] }}">
            <div class="countdown-next">
                <i class="fas fa-calendar-alt"></i>
                <span id="date-{{ $item['key'] }}"></span>
            </div>
            <div class="countdown-days normal" id="days-{{ $item['key'] }}"></div>
            <button class="btn-cancel" onclick="cancelReminder('{{ $item['key'] }}')">
                Batalkan pengingat
            </button>
        </div>

        {{-- Button (hilang setelah pengingat aktif) --}}
        <div class="btn-pengingat-wrap">
            <button class="btn-pengingat"
                onclick="setReminder('{{ $item['key'] }}', '{{ $item['name'] }}', {{ $item['interval_days'] }}, '{{ $item['interval_label'] }}')">
                <i class="fas fa-bell" style="margin-right:6px;"></i>Buat pengingat
            </button>
        </div>

    </div>
    @endforeach
</div>

{{-- Toast --}}
<div id="toast">
    <div class="toast-top">
        <i class="fas fa-check-circle"></i>
        <span id="toast-title"></span>
    </div>
    <div class="toast-sub" id="toast-sub"></div>
</div>
@endsection

@section('scripts')
<script>
const STORAGE_KEY = 'medislot_reminders';

function getReminders() {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
}

function saveReminders(data) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
}

function formatDate(dateStr) {
    const months = ['Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'];
    const d = new Date(dateStr);
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function daysLeft(targetDateStr) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const target = new Date(targetDateStr);
    target.setHours(0, 0, 0, 0);
    return Math.ceil((target - today) / (1000 * 60 * 60 * 24));
}

function urgencyClass(days) {
    if (days <= 14)  return 'urgent';
    if (days <= 60)  return 'soon';
    return 'normal';
}

function countdownLabel(days) {
    if (days <= 0)  return 'Hari ini!';
    if (days === 1) return 'Besok';
    return `${days} hari lagi`;
}

function renderCard(key, targetDate) {
    const days   = daysLeft(targetDate);
    const urg    = urgencyClass(days);
    const card   = document.getElementById('card-' + key);
    const dateEl = document.getElementById('date-' + key);
    const daysEl = document.getElementById('days-' + key);

    dateEl.textContent = formatDate(targetDate);
    daysEl.textContent = countdownLabel(days);
    daysEl.className   = 'countdown-days ' + urg;

    card.classList.add('active');
    if (urg !== 'normal') card.classList.add(urg);
}

function setReminder(key, name, intervalDays, intervalLabel) {
    const today  = new Date();
    const target = new Date(today);
    target.setDate(today.getDate() + intervalDays);
    const targetStr = target.toISOString().split('T')[0];

    const reminders = getReminders();
    reminders[key]  = { targetDate: targetStr, name };
    saveReminders(reminders);

    renderCard(key, targetStr);

    const days = daysLeft(targetStr);
    showToast(name, formatDate(targetStr), days, intervalLabel);
}

function cancelReminder(key) {
    const reminders = getReminders();
    delete reminders[key];
    saveReminders(reminders);

    const card = document.getElementById('card-' + key);
    card.classList.remove('active', 'urgent', 'soon');
}

function showToast(name, date, days, intervalLabel) {
    document.getElementById('toast-title').textContent = `Pengingat "${name}" aktif!`;
    document.getElementById('toast-sub').textContent =
        `Check-up berikutnya: ${date} — ${countdownLabel(days)} (${intervalLabel} dari sekarang)`;

    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4000);
}

// Restore reminders dari localStorage saat halaman dibuka
document.addEventListener('DOMContentLoaded', () => {
    const reminders = getReminders();
    Object.entries(reminders).forEach(([key, data]) => {
        const card = document.getElementById('card-' + key);
        if (card) renderCard(key, data.targetDate);
    });
});
</script>
@endsection
