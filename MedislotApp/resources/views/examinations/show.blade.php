@extends('layout')

@section('title', $examination->name)

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Examination Details -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="examination-icon mb-4" style="font-size: 80px;">
                        <i class="{{ $examination->icon }} text-primary"></i>
                    </div>
                    <h1 class="mb-3">{{ $examination->name }}</h1>
                    <p class="text-muted mb-4">{{ $examination->description }}</p>
                    
                    <div class="info-section mb-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="info-box">
                                    <h5>Harga</h5>
                                    <p class="text-success h4">Rp {{ number_format($examination->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="info-box">
                                    <h5>Durasi</h5>
                                    <p class="h4">{{ $examination->duration }} Menit</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <span class="badge bg-info p-2">{{ $examination->category }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedules & Recommendations -->
        <div class="col-md-6">
            <!-- Available Schedules -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Jadwal Tersedia</h5>
                </div>
                <div class="card-body">
                    @forelse ($schedules as $schedule)
                    <div class="schedule-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-calendar"></i> 
                                    {{ $schedule->schedule_date->format('d M Y') }}
                                </h6>
                                <p class="mb-1 text-muted">
                                    <i class="fas fa-clock"></i> 
                                    {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-users"></i> 
                                    Kapasitas: {{ $schedule->current_capacity }}/{{ $schedule->max_capacity }}
                                </p>
                            </div>
                            <div class="text-end">
                                @if ($schedule->isAvailable())
                                <span class="badge bg-success">Tersedia</span>
                                <br><br>
                                <a href="#" class="btn btn-sm btn-primary">Pesan</a>
                                @else
                                <span class="badge bg-danger">Penuh</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Jadwal tidak tersedia saat ini
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recommendations -->
            @if ($recommendations->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Rekomendasi Jadwal Pemeriksaan</h5>
                </div>
                <div class="card-body">
                    @foreach ($recommendations as $rec)
                    <div class="recommendation-item mb-3 p-3 border-start border-4 border-info">
                        <h6 class="mb-2">Usia {{ $rec->age_min }}-{{ $rec->age_max }} Tahun</h6>
                        <p class="mb-2">
                            <strong>Frekuensi:</strong> Setiap {{ $rec->frequency }} {{ $rec->frequency_unit }}
                        </p>
                        <p class="text-muted mb-0">{{ $rec->description }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('examinations.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Katalog
        </a>
    </div>
</div>

@endsection

@section('extra-css')
<style>
    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    
    .schedule-item {
        transition: all 0.3s ease;
    }
    
    .schedule-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
