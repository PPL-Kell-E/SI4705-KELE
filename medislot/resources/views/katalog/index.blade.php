@extends('layouts.app')

@section('title', 'Katalog Pemeriksaan - MEDISLOT')
@section('page-title', 'Katalog Pemeriksaan')
@section('page-subtitle', 'Temukan informasi lengkap jenis-jenis pemeriksaan kesehatan')

@section('extra-styles')
<style>
    .search-wrap {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        max-width: 860px;
    }
    .search-box {
        flex: 1;
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 10px;
        padding: 0 16px;
        gap: 10px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        border: 1.5px solid transparent;
        transition: border-color 0.2s;
    }
    .search-box:focus-within { border-color: #2d9e72; }
    .search-box i { color: #7a9a90; font-size: 15px; }
    .search-box input {
        flex: 1;
        border: none;
        outline: none;
        padding: 12px 0;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        color: #333;
        background: transparent;
    }
    .search-box input::placeholder { color: #a0b8b0; }
    .btn-search {
        background: #2d9e72;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0 24px;
        font-size: 14px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: background 0.18s;
        white-space: nowrap;
    }
    .btn-search:hover { background: #258a62; }

    .filter-chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 22px;
        max-width: 860px;
    }
    .chip {
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid #d0e4de;
        background: #fff;
        color: #5a7a70;
        transition: all 0.18s;
        text-decoration: none;
        display: inline-block;
    }
    .chip:hover, .chip.active {
        background: #2d9e72;
        border-color: #2d9e72;
        color: #fff;
    }

    .result-info {
        font-size: 13px;
        color: #7a9a90;
        margin-bottom: 16px;
    }
    .result-info span { font-weight: 700; color: #1a3c34; }

    .katalog-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
        max-width: 860px;
    }

    .katalog-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        text-decoration: none;
        color: inherit;
        transition: box-shadow 0.18s, transform 0.18s;
        border: 1.5px solid transparent;
    }
    .katalog-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        border-color: #2d9e72;
    }
    .card-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .card-body { flex: 1; min-width: 0; }
    .card-body h3 {
        font-size: 14.5px;
        font-weight: 700;
        color: #1a3c34;
        margin-bottom: 5px;
        line-height: 1.3;
    }
    .card-body p {
        font-size: 12.5px;
        color: #7a9a90;
        line-height: 1.55;
        margin: 0;
    }
    .card-kategori {
        display: inline-block;
        font-size: 10.5px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 10px;
        background: #f0f4f3;
        color: #5a7a70;
        margin-bottom: 5px;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 48px 0;
        color: #7a9a90;
    }
    .empty-state i { font-size: 40px; margin-bottom: 12px; opacity: 0.4; }
    .empty-state p { font-size: 14px; }
    .empty-state a { color: #2d9e72; font-weight: 600; text-decoration: none; }
</style>
@endsection

@section('content')

<form action="{{ route('katalog.index') }}" method="GET">
    <div class="search-wrap">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="q" placeholder="Cari pemeriksaan... (contoh: Gigi, Mata, Jantung)"
                   value="{{ $query }}" autocomplete="off">
            @if($query)
                <a href="{{ route('katalog.index') }}" style="color:#aaa; font-size:13px; text-decoration:none;">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </div>
        <button type="submit" class="btn-search">
            <i class="fas fa-search" style="margin-right:6px;"></i>Cari
        </button>
    </div>

    <div class="filter-chips">
        <a href="{{ route('katalog.index') }}" class="chip {{ !$query && !$kategori ? 'active' : '' }}">Semua</a>
        @foreach($kategoriList as $kat)
            <a href="{{ route('katalog.index', ['kategori' => $kat]) }}"
               class="chip {{ $kategori === $kat ? 'active' : '' }}">{{ $kat }}</a>
        @endforeach
    </div>
</form>

<div class="result-info">
    @if($query || $kategori)
        Menampilkan <span>{{ $katalog->count() }}</span> hasil
        @if($query) untuk "<span>{{ $query }}</span>" @endif
        @if($kategori) dalam kategori "<span>{{ $kategori }}</span>" @endif
    @else
        Menampilkan <span>{{ $katalog->count() }}</span> jenis pemeriksaan
    @endif
</div>

<div class="katalog-grid">
    @forelse($katalog as $item)
        <a href="{{ route('katalog.show', $item->slug) }}" class="katalog-card">
            <div class="card-icon"
                 style="background: {{ $item->bg_color }}; color: {{ $item->icon_color }};">
                <i class="fas {{ $item->icon }}"></i>
            </div>
            <div class="card-body">
                <div class="card-kategori">{{ $item->kategori }}</div>
                <h3>{{ $item->nama }}</h3>
                <p>{{ $item->singkat }}</p>
            </div>
        </a>
    @empty
        <div class="empty-state">
            <div><i class="fas fa-search-minus"></i></div>
            <p>Tidak ada pemeriksaan yang cocok.<br>
            <a href="{{ route('katalog.index') }}">Lihat semua pemeriksaan</a></p>
        </div>
    @endforelse
</div>

@endsection
