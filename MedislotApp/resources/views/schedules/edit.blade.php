@extends('layout')

@section('title', 'Edit Jadwal Pemeriksaan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Jadwal Pemeriksaan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="examination_id" class="form-label">Pemeriksaan *</label>
                            <select class="form-control @error('examination_id') is-invalid @enderror" 
                                    id="examination_id" name="examination_id" required>
                                @foreach ($examinations as $exam)
                                <option value="{{ $exam->id }}" {{ $schedule->examination_id == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('examination_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="schedule_date" class="form-label">Tanggal *</label>
                                    <input type="date" class="form-control @error('schedule_date') is-invalid @enderror" 
                                           id="schedule_date" name="schedule_date" value="{{ $schedule->schedule_date->format('Y-m-d') }}" required>
                                    @error('schedule_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Jam Mulai *</label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" value="{{ $schedule->start_time }}" required>
                                    @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Jam Selesai *</label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" value="{{ $schedule->end_time }}" required>
                                    @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="max_capacity" class="form-label">Kapasitas Maksimal *</label>
                            <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" 
                                   id="max_capacity" name="max_capacity" value="{{ $schedule->max_capacity }}" min="1" required>
                            @error('max_capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="available" {{ $schedule->status === 'available' ? 'selected' : '' }}>Tersedia</option>
                                <option value="full" {{ $schedule->status === 'full' ? 'selected' : '' }}>Penuh</option>
                                <option value="cancelled" {{ $schedule->status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Perbarui Jadwal
                            </button>
                            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
