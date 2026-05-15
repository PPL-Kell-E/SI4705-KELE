@extends('layouts.app')

@section('title', 'Insight & Pencapaian - MEDISLOT')
@section('page-title', 'Insight & Pencapaian')
@section('page-subtitle', 'Dapatkan insight kesehatan, pantau progres, dan raih lebih banyak pencapaian!')

@section('extra-styles')
<style>
/* ── STREAK BANNER ── */
.streak-banner {
    background: linear-gradient(120deg, #f47c20 0%, #f5a623 100%);
    border-radius: 16px;
    padding: 28px 32px;
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 28px;
    color: #fff;
}
.streak-main {
    display: flex;
    align-items: center;
    gap: 18px;
    flex: 0 0 auto;
    padding-right: 32px;
    border-right: 1px solid rgba(255,255,255,0.3);
}
.streak-fire-icon {
    width: 64px; height: 64px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px;
    flex-shrink: 0;
}
.streak-count { font-size: 42px; font-weight: 800; line-height: 1; }
.streak-label { font-size: 16px; font-weight: 600; margin-top: 2px; }
.streak-sublabel { font-size: 12px; opacity: 0.85; margin-top: 4px; }
.streak-msg {
    flex: 1;
    padding: 0 32px;
    border-right: 1px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    gap: 14px;
}
.streak-msg-icon { font-size: 22px; opacity: 0.9; }
.streak-msg h3 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.streak-msg p { font-size: 13px; opacity: 0.88; }
.streak-badge {
    padding-left: 32px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.streak-badge-icon { font-size: 22px; opacity: 0.9; }
.streak-badge h3 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.streak-badge p { font-size: 13px; opacity: 0.88; }
.streak-zero {
    width: 100%;
    text-align: center;
    padding: 8px 0;
}
.streak-zero h3 { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
.streak-zero p { font-size: 13px; opacity: 0.88; }

/* ── TWO COLUMN LAYOUT ── */
.insight-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    margin-bottom: 28px;
}

/* ── SECTION CARD ── */
.section-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
}
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}
.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #1a3c34;
}
.section-title i { color: #2d9e72; }
.section-link {
    font-size: 13px;
    color: #2d9e72;
    text-decoration: none;
    font-weight: 500;
    display: flex; align-items: center; gap: 4px;
}
.section-link:hover { text-decoration: underline; }

/* ── ACHIEVEMENT CARDS ── */
.achievement-group-label {
    font-size: 13.5px;
    font-weight: 600;
    color: #4a6a60;
    margin-bottom: 14px;
}
.achievement-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 22px;
}
.achievement-card {
    background: #f7faf9;
    border-radius: 12px;
    padding: 16px 12px;
    text-align: center;
    border: 1px solid #e8eeec;
}
.achievement-card.achieved { border-color: transparent; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.achievement-icon {
    width: 48px; height: 48px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 10px;
    font-size: 20px;
}
.achievement-icon.locked {
    background: #e8eeec;
    color: #9ab0a8;
}
.achievement-name { font-size: 11.5px; font-weight: 600; color: #1a3c34; margin-bottom: 3px; }
.achievement-desc { font-size: 10.5px; color: #7a9a90; margin-bottom: 8px; line-height: 1.4; }
.achievement-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #e8fff4;
    color: #2d9e72;
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 20px;
}
.achievement-badge i { font-size: 9px; }
.achievement-progress-bar-wrap { margin-top: 4px; }
.achievement-fraction { font-size: 10.5px; color: #7a9a90; margin-bottom: 4px; font-weight: 600; }
.achievement-progress-bar {
    height: 4px;
    background: #e8eeec;
    border-radius: 4px;
    overflow: hidden;
}
.achievement-progress-bar-fill {
    height: 100%;
    background: #2d9e72;
    border-radius: 4px;
    transition: width 0.4s ease;
}

/* ── SMART INSIGHTS ── */
.insight-list { display: flex; flex-direction: column; gap: 12px; }
.insight-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px;
    background: #f7faf9;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
}
.insight-item:hover { background: #eef5f2; }
.insight-item-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.insight-item-body { flex: 1; }
.insight-item-title { font-size: 13px; font-weight: 600; color: #1a3c34; margin-bottom: 3px; }
.insight-item-desc { font-size: 12px; color: #7a9a90; line-height: 1.45; }
.insight-item-arrow { color: #b0c8c0; font-size: 12px; align-self: center; }

/* ── DAILY TIPS ── */
.tips-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px 28px;
    display: flex;
    align-items: center;
    gap: 32px;
}
.tips-icon-wrap {
    width: 80px; height: 80px;
    background: #e8f4ff;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px;
    flex-shrink: 0;
}
.tips-body { flex: 1; }
.tips-section-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: #1a3c34;
    margin-bottom: 12px;
}
.tips-section-title i { color: #2d9e72; }
.tips-title { font-size: 18px; font-weight: 700; color: #1a3c34; margin-bottom: 6px; }
.tips-desc { font-size: 13.5px; color: #7a9a90; line-height: 1.55; }
.tips-recommendation {
    background: #f7faf9;
    border-radius: 12px;
    padding: 14px 18px;
    min-width: 220px;
    flex-shrink: 0;
}
.tips-rec-label { font-size: 12px; color: #e67e22; font-weight: 600; margin-bottom: 6px; }
.tips-rec-value {
    font-size: 14px; font-weight: 600; color: #1a3c34;
    display: flex; align-items: center; gap: 6px;
}
.tips-rec-value i { color: #2d9e72; font-size: 12px; }

/* ── ERROR / EMPTY STATES ── */
.empty-state {
    text-align: center;
    padding: 24px 16px;
    color: #7a9a90;
    font-size: 13px;
}
.empty-state i { font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.4; }
.error-alert {
    background: #fff3f3;
    border: 1px solid #f5c6c6;
    color: #c0392b;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 13.5px;
    display: flex; align-items: center; gap: 10px;
}
.last-updated {
    font-size: 11.5px;
    color: #b0c8c0;
    text-align: right;
    margin-top: 8px;
}

@media (max-width: 1024px) {
    .insight-grid { grid-template-columns: 1fr; }
    .achievement-grid { grid-template-columns: repeat(2, 1fr); }
    .tips-card { flex-wrap: wrap; }
}
</style>
@endsection

@section('content')

@if($error)
    <div class="error-alert">
        <i class="fas fa-exclamation-circle"></i>
        {{ $error }}
    </div>
@endif

{{-- STREAK BANNER --}}
@if($streak > 0)
<div class="streak-banner">
    <div class="streak-main">
        <div class="streak-fire-icon">🔥</div>
        <div>
            <div class="streak-count">{{ $streak }}</div>
            <div class="streak-label">Hari Streak</div>
            <div class="streak-sublabel">Anda aktif memantau kesehatan berturut-turut!</div>
        </div>
    </div>
    <div class="streak-msg">
        <div class="streak-msg-icon">🔥</div>
        <div>
            <h3>Pertahankan streak-mu!</h3>
            <p>Terus lakukan pemeriksaan dan hidup sehat setiap hari.</p>
        </div>
    </div>
    <div class="streak-badge">
        <div class="streak-badge-icon">✨</div>
        <div>
            <h3>Luar biasa!</h3>
            <p>Konsistensimu membuatmu lebih sehat setiap hari.</p>
        </div>
    </div>
</div>
@else
<div class="streak-banner">
    <div class="streak-zero">
        <h3>Mulai streak kesehatanmu hari ini! 🔥</h3>
        <p>Mulai aktivitas kesehatan rutin untuk mendapatkan streak. Selesaikan pemeriksaan pertamamu!</p>
    </div>
</div>
@endif

{{-- MAIN GRID --}}
<div class="insight-grid">

    {{-- LEFT: PENCAPAIAN --}}
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-trophy"></i>
                Pencapaian
            </div>
            <a href="#" class="section-link">Lihat Semua <i class="fas fa-chevron-right"></i></a>
        </div>

        {{-- Tercapai --}}
        @if(count($achievements['achieved']) > 0)
            <div class="achievement-group-label">
                Pencapaian Tercapai ({{ count($achievements['achieved']) }})
            </div>
            <div class="achievement-grid">
                @foreach($achievements['achieved'] as $ach)
                <div class="achievement-card achieved">
                    <div class="achievement-icon" style="background: {{ $ach['bg'] }}; color: {{ $ach['color'] }};">
                        <i class="{{ $ach['icon'] }}"></i>
                    </div>
                    <div class="achievement-name">{{ $ach['name'] }}</div>
                    <div class="achievement-desc">{{ $ach['desc'] }}</div>
                    <span class="achievement-badge"><i class="fas fa-check-circle"></i> Tercapai</span>
                </div>
                @endforeach
            </div>
        @else
            <div class="achievement-group-label">Pencapaian Tercapai (0)</div>
            <div class="empty-state" style="margin-bottom:18px;">
                <i class="fas fa-trophy"></i>
                Belum ada pencapaian yang diperoleh.
            </div>
        @endif

        {{-- Belum Tercapai --}}
        @if(count($achievements['unachieved']) > 0)
            <div class="achievement-group-label">
                Pencapaian Belum Tercapai ({{ count($achievements['unachieved']) }})
            </div>
            <div class="achievement-grid">
                @foreach($achievements['unachieved'] as $ach)
                <div class="achievement-card">
                    <div class="achievement-icon locked">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="achievement-name">{{ $ach['name'] }}</div>
                    <div class="achievement-desc">{{ $ach['desc'] }}</div>
                    <div class="achievement-progress-bar-wrap">
                        <div class="achievement-fraction">{{ $ach['progress'] }}/{{ $ach['target'] }}</div>
                        <div class="achievement-progress-bar">
                            <div class="achievement-progress-bar-fill"
                                style="width: {{ $ach['target'] > 0 ? min(100, round($ach['progress'] / $ach['target'] * 100)) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- RIGHT: INSIGHT PINTAR --}}
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-lightbulb"></i>
                Insight Pintar
            </div>
            <a href="#" class="section-link">Lihat Semua <i class="fas fa-chevron-right"></i></a>
        </div>

        @if(count($smartInsights) > 0)
        <div class="insight-list">
            @foreach($smartInsights as $insight)
            <div class="insight-item">
                <div class="insight-item-icon"
                    style="background: {{ $insight['icon_bg'] }}; color: {{ $insight['icon_color'] }};">
                    <i class="{{ $insight['icon'] }}"></i>
                </div>
                <div class="insight-item-body">
                    <div class="insight-item-title">{{ $insight['title'] }}</div>
                    <div class="insight-item-desc">{{ $insight['desc'] }}</div>
                </div>
                <div class="insight-item-arrow"><i class="fas fa-chevron-right"></i></div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <i class="fas fa-lightbulb"></i>
            Insight kesehatan belum tersedia. Tambahkan lebih banyak aktivitas kesehatan.
        </div>
        @endif
    </div>

</div>

{{-- TIPS KESEHATAN HARIAN --}}
@if($dailyTip)
<div class="section-card" style="margin-bottom: 8px;">
    <div class="section-title" style="margin-bottom: 18px;">
        <i class="fas fa-leaf"></i>
        Tips Kesehatan Hari Ini
    </div>
    <div class="tips-card" style="padding: 0; background: transparent;">
        <div class="tips-icon-wrap">
            {{ $dailyTip['emoji'] }}
        </div>
        <div class="tips-body">
            <div class="tips-title">{{ $dailyTip['title'] }}</div>
            <div class="tips-desc">{{ $dailyTip['desc'] }}</div>
        </div>
        <div class="tips-recommendation">
            <div class="tips-rec-label">Rekomendasi:</div>
            <div class="tips-rec-value">
                {{ $dailyTip['recommendation'] }}
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </div>
</div>
@else
<div class="section-card" style="margin-bottom: 8px;">
    <div class="section-title" style="margin-bottom: 12px;">
        <i class="fas fa-leaf"></i> Tips Kesehatan Hari Ini
    </div>
    <div class="empty-state">
        <i class="fas fa-leaf"></i>
        Tips kesehatan gagal dimuat.
    </div>
</div>
@endif

@if($lastUpdated)
<div class="last-updated">Terakhir diperbarui: {{ $lastUpdated }}</div>
@endif

@endsection
