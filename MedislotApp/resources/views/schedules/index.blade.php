@extends('layout')

@section('title', 'Kelola Jadwal Pemeriksaan')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Kelola Jadwal Pemeriksaan</h1>
    
    <a href="{{ route('schedules.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Jadwal Baru
    </a>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Pemeriksaan</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->examination->name }}</td>
                            <td>{{ $schedule->schedule_date->format('d M Y') }}</td>
                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                            <td>{{ $schedule->current_capacity }}/{{ $schedule->max_capacity }}</td>
                            <td>
                                <span class="badge bg-{{ $schedule->status === 'available' ? 'success' : ($schedule->status === 'full' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('schedules.show', $schedule->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('schedules.edit', $schedule->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('schedules.destroy', $schedule->id) }}" method="POST" style="display: inline;">
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
                            <td colspan="6" class="text-center text-muted">Tidak ada jadwal</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $schedules->links() }}
</div>

@endsection
