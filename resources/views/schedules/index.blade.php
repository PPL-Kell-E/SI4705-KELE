@extends('layouts.app')

@section('title', 'Jadwal Saya - Medislot')

@section('content')
<div class="schedule-header">
    <div class="header-text">
        <h2>Jadwal Saya</h2>
        <p>Kelola janji temu dan jadwal pemeriksaan medis</p>
    </div>
    <button id="btnTambahJadwal" class="btn-primary">
        <i class="fas fa-plus"></i> Tambah Jadwal
    </button>
</div>

<div class="filter-tabs">
    <button class="tab-btn active">Semua</button>
    <button class="tab-btn">Mendatang</button>
    <button class="tab-btn">Selesai / Batal</button>
</div>

<div class="schedule-list" id="scheduleList">
    @forelse($schedules as $schedule)
        <div class="schedule-card">
            <div class="date-badge">
                <span class="day">{{ \Carbon\Carbon::parse($schedule->date)->format('d') }}</span>
                <span class="month">{{ strtoupper(\Carbon\Carbon::parse($schedule->date)->format('M')) }}</span>
                <span class="year">{{ \Carbon\Carbon::parse($schedule->date)->format('Y') }}</span>
            </div>
            <div class="card-content">
                <div class="card-main">
                    <div class="card-header">
                        <h3>{{ $schedule->checkup_type }}</h3>
                        <span class="status-badge {{ $schedule->status }}">{{ ucfirst($schedule->status) }}</span>
                    </div>
                    <div class="card-info">
                        <div class="info-item">
                            <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($schedule->time)->format('H.i') }} WIB
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i> {{ $schedule->facility }}
                        </div>
                    </div>
                    @if($schedule->notes)
                        <div class="card-notes">
                            "{{ $schedule->notes }}"
                        </div>
                    @endif
                </div>
                <div class="card-actions">
                    <button class="btn-outline-edit"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Belum ada jadwal pemeriksaan.</p>
        </div>
    @endforelse
</div>

<!-- Modal Form (Mockup 2) -->
<div id="modalTambahJadwal" class="modal">
    <div class="modal-content">
        <header class="modal-header">
            <div class="modal-icon">
                <i class="fas fa-plus"></i>
            </div>
            <div class="modal-title">
                <h3>Tambah Jadwal Pemeriksaan</h3>
                <p>Buat jadwal pemeriksaan kesehatan sesuai preferensi Anda</p>
            </div>
            <button class="close-modal">&times;</button>
        </header>
        
        <form id="formTambahJadwal" class="modal-body">
            @csrf
            <div class="form-group">
                <label>JENIS PEMERIKSAAN</label>
                <input type="text" name="checkup_type" placeholder="Contoh: Pemeriksaan Gigi Rutin" required>
            </div>
            
            <div class="form-group">
                <label>FASILITAS / KLINIK</label>
                <input type="text" name="facility" placeholder="Contoh: Klinik Gigi" required>
            </div>
            
            <div class="form-group">
                <label>TANGGAL</label>
                <input type="date" name="date" required id="inputDate">
            </div>
            
            <div class="form-group">
                <label>WAKTU</label>
                <select name="time" id="inputTime" required>
                    <option value="" disabled selected>Pilih Waktu</option>
                    <option value="08:00">08.00 WIB</option>
                    <option value="10:00">10.00 WIB</option>
                    <option value="13:00">13.00 WIB</option>
                    <option value="15:00">15.00 WIB</option>
                </select>
                <small id="availabilityStatus"></small>
            </div>

            <div class="form-group">
                <label>CATATAN (OPSIONAL)</label>
                <input type="text" name="notes" placeholder="Contoh: Puasa 10 jam sebelum periksa">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal (Mockup 3) -->
<div id="modalSuccess" class="modal-mini">
    <div class="modal-mini-content">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h3>Berhasil menambahkan jadwal</h3>
        <button id="btnContinue" class="btn-continue">Continue</button>
    </div>
</div>
@endsection
