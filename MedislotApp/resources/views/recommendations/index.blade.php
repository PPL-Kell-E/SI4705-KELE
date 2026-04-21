@extends('layout')

@section('title', 'Kelola Rekomendasi Jadwal')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Kelola Rekomendasi Jadwal Pemeriksaan</h1>
    
    <a href="{{ route('recommendations.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Rekomendasi Baru
    </a>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Pemeriksaan</th>
                            <th>Usia</th>
                            <th>Frekuensi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recommendations as $rec)
                        <tr>
                            <td>{{ $rec->examination->name }}</td>
                            <td>{{ $rec->age_min }} - {{ $rec->age_max }} tahun</td>
                            <td>Setiap {{ $rec->frequency }} {{ $rec->frequency_unit }}</td>
                            <td>
                                <span class="badge bg-{{ $rec->is_active ? 'success' : 'danger' }}">
                                    {{ $rec->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('recommendations.edit', $rec->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('recommendations.destroy', $rec->id) }}" method="POST" style="display: inline;">
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
                            <td colspan="5" class="text-center text-muted">Tidak ada rekomendasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $recommendations->links() }}
</div>

@endsection
