@extends('layouts.app')

@section('title', 'Dashboard - MEDISLOT')
@section('page-title')Hello, {{ Auth::user()->full_name ?? Auth::user()->name }}@endsection
@section('page-subtitle', 'Pantau kondisi kesehatanmu hari ini')

@section('extra-styles')
<style>
    /* ── ACTION BUTTONS ── */
    .dash-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-bottom: 24px;
    }
    .btn-dash {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: opacity 0.18s, transform 0.15s;
    }
    .btn-dash:hover { opacity: 0.88; transform: translateY(-1px); }
    .btn-dash-primary { background: #1a3c34; color: #fff; }
    .btn-dash-outline { background: transparent; color: #1a3c34; border: 2px solid #1a3c34; }

    /* ── ALERT EMPTY STATE ── */
    .alert-empty {
        background: #fff8e1;
        border: 1px solid #ffe082;
        border-radius: 14px;
        padding: 20px 24px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .alert-empty i { font-size: 24px; color: #f59e0b; }
    .alert-empty-text { flex: 1; }
    .alert-empty-text strong { display: block; color: #92400e; font-size: 14px; margin-bottom: 4px; }
    .alert-empty-text span { font-size: 13px; color: #78350f; }
    .btn-fill-data {
        background: #f59e0b; color: #fff;
        padding: 8px 16px; border-radius: 8px;
        text-decoration: none; font-size: 13px; font-weight: 600;
        white-space: nowrap;
    }
    .btn-fill-data:hover { opacity: 0.88; }

    /* ── SUMMARY CARDS ── */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .summary-card {
        border-radius: 16px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        cursor: default;
    }
    .summary-card.card-jadwal    { background: #d4ede4; }
    .summary-card.card-selesai   { background: #b8ddd0; }
    .summary-card.card-target    { background: #c9dff0; }
    .summary-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .card-jadwal  .summary-icon { background: rgba(255,255,255,0.55); color: #1a7a54; }
    .card-selesai .summary-icon { background: rgba(255,255,255,0.55); color: #1a7a54; }
    .card-target  .summary-icon { background: rgba(255,255,255,0.55); color: #2563ab; }
    .summary-info .sum-val {
        font-size: 22px;
        font-weight: 800;
        color: #1a3c34;
        line-height: 1.1;
        margin-bottom: 4px;
    }
    .card-target .summary-info .sum-val { color: #1e3a5f; }
    .summary-info .sum-label {
        font-size: 12px;
        font-weight: 700;
        color: #1a3c34;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }
    .summary-info .sum-sub {
        font-size: 12px;
        color: #3d7060;
    }
    .card-target .summary-info .sum-sub { color: #2a4a7f; }

    /* ── SECTION TITLE ── */
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 14px;
    }

    /* ── JADWAL TERDEKAT CARD ── */
    .jadwal-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        margin-bottom: 28px;
    }
    .jadwal-row {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .jadwal-date-box {
        background: #f0f4f3;
        border-radius: 12px;
        padding: 12px 16px;
        text-align: center;
        min-width: 68px;
        flex-shrink: 0;
    }
    .jadwal-date-box .date-day {
        font-size: 28px;
        font-weight: 800;
        color: #1a3c34;
        line-height: 1;
    }
    .jadwal-date-box .date-mon {
        font-size: 12px;
        font-weight: 700;
        color: #2d9e72;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .jadwal-date-box .date-year {
        font-size: 11px;
        color: #7a9a90;
        font-weight: 600;
    }
    .jadwal-info { flex: 1; }
    .jadwal-info .ji-name {
        font-size: 16px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 3px;
    }
    .jadwal-info .ji-klinik {
        font-size: 13px;
        color: #5a7a70;
        margin-bottom: 3px;
    }
    .jadwal-info .ji-waktu {
        font-size: 13px;
        color: #7a9a90;
    }
    .jadwal-btns {
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-shrink: 0;
    }
    .btn-ubah {
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: 1.5px solid #2d9e72;
        background: transparent;
        color: #2d9e72;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
    }
    .btn-ubah:hover { background: #2d9e72; color: #fff; }
    .btn-selesai {
        padding: 8px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        background: #1a3c34;
        color: #fff;
        cursor: pointer;
        transition: opacity 0.18s;
    }
    .btn-selesai:hover { opacity: 0.85; }

    /* ── EMPTY JADWAL ── */
    .jadwal-empty {
        text-align: center;
        padding: 28px 0;
        color: #7a9a90;
        font-size: 13.5px;
    }
    .jadwal-empty i { font-size: 36px; margin-bottom: 10px; display: block; color: #b0cec5; }

    /* ── BOTTOM GRID ── */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }
    .bottom-card {
        background: #fff;
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    /* ── STREAK ── */
    .streak-body {
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .streak-icon-wrap {
        width: 64px; height: 64px;
        background: #fff3e0;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 32px;
        flex-shrink: 0;
    }
    .streak-text .st-label {
        font-size: 12px;
        font-weight: 600;
        color: #7a9a90;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }
    .streak-text .st-val {
        font-size: 28px;
        font-weight: 800;
        color: #1a3c34;
        line-height: 1;
        margin-bottom: 4px;
    }
    .streak-text .st-sub {
        font-size: 13px;
        color: #5a7a70;
    }

    /* ── REMINDER LIST ── */
    .reminder-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f4f3;
    }
    .reminder-item:last-child { border-bottom: none; }
    .reminder-icon {
        width: 34px; height: 34px;
        background: #e8f5f0;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #2d9e72;
        font-size: 14px;
        flex-shrink: 0;
    }
    .reminder-text .rt-title {
        font-size: 13.5px;
        font-weight: 600;
        color: #1a3c34;
        margin-bottom: 2px;
    }
    .reminder-text .rt-time {
        font-size: 12px;
        color: #7a9a90;
    }
    .reminder-empty {
        text-align: center;
        padding: 20px 0;
        color: #b0cec5;
        font-size: 13px;
    }
    .reminder-empty i { display: block; font-size: 28px; margin-bottom: 8px; }

    /* ── SUCCESS ALERT ── */
    .flash-success {
        background: #e8f5f0;
        border: 1px solid #2d9e72;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 20px;
        color: #1a5c3a;
        font-size: 13.5px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endsection

@section('content')

{{-- Flash success --}}
@if(session('success'))
<div class="flash-success">
    <i class="fas fa-check-circle" style="color:#2d9e72;"></i>
    {{ session('success') }}
</div>
@endif

{{-- ACTION BUTTONS --}}
<div class="dash-actions">
    <a href="{{ route('jadwal.create') }}" class="btn-dash btn-dash-primary">
        <i class="fas fa-plus"></i> Tambah Jadwal
    </a>
    <a href="{{ route('hasil-pemeriksaan.index') }}" class="btn-dash btn-dash-primary">
        <i class="fas fa-clipboard-list"></i> Input Hasil
    </a>
</div>

{{-- EMPTY HEALTH DATA WARNING --}}
@if(!$hasHealthData)
<div class="alert-empty">
    <i class="fas fa-exclamation-triangle"></i>
    <div class="alert-empty-text">
        <strong>Dashboard belum tersedia sepenuhnya</strong>
        <span>Silakan lengkapi Data Kesehatan Dasar terlebih dahulu agar ringkasan kesehatanmu dapat ditampilkan.</span>
    </div>
    <a href="{{ route('data-kesehatan.index') }}" class="btn-fill-data">Lengkapi Sekarang</a>
</div>
@endif

{{-- SUMMARY CARDS --}}
<div class="summary-grid">
    {{-- Jadwal Terdekat --}}
    <div class="summary-card card-jadwal">
        <div class="summary-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="summary-info">
            <div class="sum-label">Jadwal Terdekat</div>
            @if($jadwalTerdekat)
                <div class="sum-val">{{ $jadwalTerdekat->tanggal->translatedFormat('d F Y') }}</div>
                <div class="sum-sub">{{ $jadwalTerdekat->jenis_pemeriksaan }} {{ \Carbon\Carbon::parse($jadwalTerdekat->waktu)->format('H.i') }}</div>
            @else
                <div class="sum-val" style="font-size:15px;">Belum ada</div>
                <div class="sum-sub">Tambahkan jadwal pemeriksaan</div>
            @endif
        </div>
    </div>

    {{-- Pemeriksaan Selesai --}}
    <div class="summary-card card-selesai">
        <div class="summary-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="summary-info">
            <div class="sum-label">Pemeriksaan Selesai</div>
            <div class="sum-val">{{ $selesaiBulanIni }} bulan ini</div>
            <div class="sum-sub">Dari target {{ $totalJadwalBulanIni }} pemeriksaan</div>
        </div>
    </div>

    {{-- Target Tercapai --}}
    <div class="summary-card card-target">
        <div class="summary-icon">
            <i class="fas fa-bullseye"></i>
        </div>
        <div class="summary-info">
            <div class="sum-label">Target Tercapai</div>
            <div class="sum-val">{{ $persentaseTarget }} %</div>
            <div class="sum-sub">{{ $selesaiBulanIni }} dari {{ $totalJadwalBulanIni }} target</div>
        </div>
    </div>
</div>

{{-- JADWAL TERDEKAT SECTION --}}
<div class="section-title">Jadwal Terdekat</div>
<div class="jadwal-card">
    @if($jadwalTerdekat)
    <div class="jadwal-row">
        <div class="jadwal-date-box">
            <div class="date-day">{{ $jadwalTerdekat->tanggal->format('d') }}</div>
            <div class="date-mon">{{ $jadwalTerdekat->tanggal->translatedFormat('M') }}</div>
            <div class="date-year">{{ $jadwalTerdekat->tanggal->format('Y') }}</div>
        </div>
        <div class="jadwal-info">
            <div class="ji-name">{{ $jadwalTerdekat->jenis_pemeriksaan }}</div>
            <div class="ji-klinik">{{ $jadwalTerdekat->fasilitas_klinik }}</div>
            <div class="ji-waktu">
                {{ $jadwalTerdekat->tanggal->translatedFormat('l, d F Y') }}
                | {{ \Carbon\Carbon::parse($jadwalTerdekat->waktu)->format('H.i') }}
                – {{ \Carbon\Carbon::parse($jadwalTerdekat->waktu)->addHour()->format('H.i') }}
            </div>
        </div>
        <div class="jadwal-btns">
            <a href="{{ route('jadwal.edit', $jadwalTerdekat->id) }}" class="btn-ubah">Ubah Jadwal</a>
            <form action="{{ route('jadwal.selesai', $jadwalTerdekat->id) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn-selesai" style="width:100%;">Tandai Selesai</button>
            </form>
        </div>
    </div>
    @else
    <div class="jadwal-empty">
        <i class="fas fa-calendar-times"></i>
        Belum ada jadwal pemeriksaan mendatang.<br>
        <a href="{{ route('jadwal.create') }}" style="color:#2d9e72; font-weight:600; text-decoration:none;">+ Tambah jadwal baru</a>
    </div>
    @endif
</div>

{{-- BOTTOM: STREAK + REMINDER --}}
<div class="bottom-grid">

    {{-- Streak & Achievement --}}
    <div class="bottom-card">
        <div class="section-title">Streak &amp; Achievement</div>
        <div class="streak-body">
            <div class="streak-icon-wrap">🔥</div>
            <div class="streak-text">
                <div class="st-label">Streak Saat Ini</div>
                <div class="st-val">
                    {{ $streak }}
                    <span style="font-size:16px; font-weight:600; color:#5a7a70;">bulan</span>
                </div>
                <div class="st-sub">
                    @if($streak >= 3)
                        Luar biasa! Pertahankan terus! 🎉
                    @elseif($streak >= 1)
                        Terus pertahankan!
                    @else
                        Mulai streak-mu dengan menyelesaikan pemeriksaan pertama!
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Reminder --}}
    <div class="bottom-card">
        <div class="section-title">Reminder</div>
        @forelse($reminders as $reminder)
        <div class="reminder-item">
            <div class="reminder-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="reminder-text">
                <div class="rt-title">Reminder: {{ $reminder->jenis_pemeriksaan }}
                    @if($reminder->tanggal->isToday()) hari ini
                    @elseif($reminder->tanggal->isTomorrow()) besok
                    @endif
                </div>
                <div class="rt-time">
                    {{ $reminder->tanggal->translatedFormat('d F Y') }},
                    {{ \Carbon\Carbon::parse($reminder->waktu)->format('H.i') }}
                </div>
            </div>
        </div>
        @empty
        <div class="reminder-empty">
            <i class="fas fa-bell-slash"></i>
            Tidak ada reminder dalam 7 hari ke depan
        </div>
        @endforelse
    </div>

</div>

@endsection
