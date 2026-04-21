@extends('layout')

@section('title', 'Detail Rekomendasi')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Detail Rekomendasi Jadwal Pemeriksaan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h4 class="mb-3">{{ $recommendation->examination->name }}</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rentang Usia</label>
                                <p>{{ $recommendation->age_min }} - {{ $recommendation->age_max }} tahun</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <p>
                                    <span class="badge bg-{{ $recommendation->is_active ? 'success' : 'danger' }}">
                                        {{ $recommendation->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Frekuensi Pemeriksaan</label>
                            <p>Setiap {{ $recommendation->frequency }} {{ $recommendation->frequency_unit }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <p>{{ $recommendation->description }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('recommendations.edit', $recommendation->id) }}" class="btn btn-warning w-100">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('recommendations.destroy', $recommendation->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Yakin?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('recommendations.index') }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

@endsection
