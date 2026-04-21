@extends('layout')

@section('title', 'Edit Rekomendasi Jadwal')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Rekomendasi Jadwal Pemeriksaan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('recommendations.update', $recommendation->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="examination_id" class="form-label">Pemeriksaan *</label>
                            <select class="form-control @error('examination_id') is-invalid @enderror" 
                                    id="examination_id" name="examination_id" required>
                                @foreach ($examinations as $exam)
                                <option value="{{ $exam->id }}" {{ $recommendation->examination_id == $exam->id ? 'selected' : '' }}>
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
                                    <label for="age_min" class="form-label">Usia Minimum *</label>
                                    <input type="number" class="form-control @error('age_min') is-invalid @enderror" 
                                           id="age_min" name="age_min" value="{{ $recommendation->age_min }}" min="0" required>
                                    @error('age_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="age_max" class="form-label">Usia Maksimum *</label>
                                    <input type="number" class="form-control @error('age_max') is-invalid @enderror" 
                                           id="age_max" name="age_max" value="{{ $recommendation->age_max }}" min="0" required>
                                    @error('age_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frequency" class="form-label">Frekuensi *</label>
                                    <input type="number" class="form-control @error('frequency') is-invalid @enderror" 
                                           id="frequency" name="frequency" value="{{ $recommendation->frequency }}" min="1" required>
                                    @error('frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="frequency_unit" class="form-label">Satuan Frekuensi *</label>
                                    <select class="form-control @error('frequency_unit') is-invalid @enderror" 
                                            id="frequency_unit" name="frequency_unit" required>
                                        <option value="hari" {{ $recommendation->frequency_unit === 'hari' ? 'selected' : '' }}>Hari</option>
                                        <option value="minggu" {{ $recommendation->frequency_unit === 'minggu' ? 'selected' : '' }}>Minggu</option>
                                        <option value="bulan" {{ $recommendation->frequency_unit === 'bulan' ? 'selected' : '' }}>Bulan</option>
                                        <option value="tahun" {{ $recommendation->frequency_unit === 'tahun' ? 'selected' : '' }}>Tahun</option>
                                    </select>
                                    @error('frequency_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi *</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required>{{ $recommendation->description }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       value="1" {{ $recommendation->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Aktif
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Perbarui Rekomendasi
                            </button>
                            <a href="{{ route('recommendations.index') }}" class="btn btn-secondary">
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
