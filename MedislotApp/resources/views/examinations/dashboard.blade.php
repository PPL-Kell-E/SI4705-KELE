@extends('layout')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-4">Dashboard Admin</h1>
            <a href="{{ route('examinations.create') }}" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> Tambah Pemeriksaan Baru
            </a>
            <a href="{{ route('schedules.create') }}" class="btn btn-success mb-3">
                <i class="fas fa-calendar-plus"></i> Tambah Jadwal
            </a>
            <a href="{{ route('recommendations.create') }}" class="btn btn-info mb-3">
                <i class="fas fa-lightbulb"></i> Tambah Rekomendasi
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Pemeriksaan</h6>
                    <h2>{{ $totalExaminations }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Pemeriksaan Aktif</h6>
                    <h2>{{ $activeExaminations }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Examination List -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Daftar Pemeriksaan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($examinations as $exam)
                        <tr>
                            <td>
                                <i class="{{ $exam->icon }}"></i> {{ $exam->name }}
                            </td>
                            <td>{{ $exam->category }}</td>
                            <td>Rp {{ number_format($exam->price, 0, ',', '.') }}</td>
                            <td>{{ $exam->duration }} menit</td>
                            <td>
                                @if ($exam->is_active)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('examinations.edit', $exam->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('examinations.destroy', $exam->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
