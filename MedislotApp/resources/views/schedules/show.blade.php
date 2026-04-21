@extends('layout')

@section('title', 'Detail Jadwal')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Detail Jadwal Pemeriksaan</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">{{ $schedule->examination->name }}</h4>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Pemeriksaan</label>
                                <p>{{ $schedule->schedule_date->format('d M Y') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jam Pemeriksaan</label>
                                <p>{{ $schedule->start_time }} - {{ $schedule->end_time }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p>
                                    <span class="badge bg-{{ $schedule->status === 'available' ? 'success' : ($schedule->status === 'full' ? 'warning' : 'danger') }} p-2">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kapasitas</label>
                                <p>{{ $schedule->current_capacity }} / {{ $schedule->max_capacity }} peserta</p>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Bar -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ketersediaan Tempat</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ ($schedule->current_capacity / $schedule->max_capacity) * 100 }}%"
                                 aria-valuenow="{{ $schedule->current_capacity }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $schedule->max_capacity }}">
                                {{ $schedule->current_capacity }} / {{ $schedule->max_capacity }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings List -->
            @if ($schedule->bookings->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Daftar Peserta Terdaftar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pasien</th>
                                    <th>Tanggal Booking</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schedule->bookings as $booking)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->booking_date->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <h5 class="card-title">Aksi</h5>
                    <a href="{{ route('schedules.edit', $schedule->id) }}" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Jadwal
                    </a>
                    <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Yakin?')">
                            <i class="fas fa-trash"></i> Hapus Jadwal
                        </button>
                    </form>
                </div>
            </div>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary w-100">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

@endsection
