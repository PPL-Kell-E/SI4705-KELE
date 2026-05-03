@extends('layout')

@section('title', 'Tambah Jadwal Pemeriksaan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-plus"></i> Tambah Jadwal Pemeriksaan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('schedules.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="examination_id" class="form-label">Pemeriksaan *</label>
                            <select class="form-control @error('examination_id') is-invalid @enderror" 
                                    id="examination_id" name="examination_id" required>
                                <option value="">-- Pilih Pemeriksaan --</option>
                                @foreach ($examinations as $exam)
                                <option value="{{ $exam->id }}" {{ old('examination_id') == $exam->id ? 'selected' : '' }}>
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
                                           id="schedule_date" name="schedule_date" value="{{ old('schedule_date') }}" required>
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
                                           id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Jam Selesai *</label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="max_capacity" class="form-label">Kapasitas Maksimal *</label>
                            <input type="number" class="form-control @error('max_capacity') is-invalid @enderror" 
                                   id="max_capacity" name="max_capacity" value="{{ old('max_capacity') }}" min="1" required>
                            @error('max_capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan Jadwal
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
