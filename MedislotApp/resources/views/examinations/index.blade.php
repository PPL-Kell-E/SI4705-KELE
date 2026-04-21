@extends('layout')

@section('title', 'Katalog Pemeriksaan')

@section('content')
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-2">Katalog Pemeriksaan Kesehatan</h1>
        <p class="lead">Temukan dan pesan pemeriksaan kesehatan terbaik untuk Anda</p>
    </div>
</div>

<div class="container py-5">
    <!-- Filter by Category -->
    <div class="mb-4">
        <h4>Kategori Pemeriksaan</h4>
        <div class="btn-group flex-wrap" role="group">
            <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                Semua
            </button>
            @foreach ($categories as $category => $exams)
            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="{{ str_replace(' ', '-', strtolower($category)) }}">
                {{ $category }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Examination Cards Grid -->
    <div class="row" id="examinationGrid">
        @forelse ($examinations as $examination)
        <div class="col-md-6 col-lg-4 mb-4 examination-card" data-category="{{ str_replace(' ', '-', strtolower($examination->category)) }}">
            <div class="card h-100 shadow-sm hover-card">
                <div class="card-body text-center">
                    <div class="examination-icon mb-3">
                        <i class="{{ $examination->icon }} fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title">{{ $examination->name }}</h5>
                    <p class="card-text text-muted">{{ Str::limit($examination->description, 100) }}</p>
                    <div class="mb-3">
                        <span class="badge bg-info">{{ $examination->category }}</span>
                        <span class="badge bg-secondary">{{ $examination->duration }} menit</span>
                    </div>
                    <h6 class="text-success fw-bold mb-3">Rp {{ number_format($examination->price, 0, ',', '.') }}</h6>
                    <a href="{{ route('examinations.show', $examination->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-info-circle"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Tidak ada pemeriksaan yang tersedia saat ini.
            </div>
        </div>
        @endforelse
    </div>
</div>

@endsection

@section('extra-css')
<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .hover-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
    }
    
    .examination-icon {
        padding: 20px;
        background: #f8f9fa;
        border-radius: 10px;
        display: inline-block;
    }
    
    .filter-btn {
        margin: 5px;
    }
</style>
@endsection

@section('extra-js')
<script>
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.examination-card').forEach(card => {
                if (filter === 'all') {
                    card.style.display = '';
                } else {
                    card.style.display = card.getAttribute('data-category') === filter ? '' : 'none';
                }
            });
        });
    });
</script>
@endsection
