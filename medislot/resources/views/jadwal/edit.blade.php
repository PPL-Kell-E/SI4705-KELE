@extends('layouts.app')

@section('title', 'Edit Jadwal - MEDISLOT')
@section('page-title', 'Edit Jadwal Pemeriksaan')
@section('page-subtitle', 'Perbarui informasi jadwal pemeriksaan Anda')

@section('extra-styles')
<style>
    .form-card {
        background: #fff; border-radius: 16px;
        overflow: hidden; max-width: 680px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    }
    .form-card-header {
        display: flex; align-items: center; gap: 16px;
        padding: 20px 28px; border-bottom: 1px solid #e8eeec;
    }
    .form-card-icon {
        width: 48px; height: 48px; background: #2d9e72;
        border-radius: 12px; display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 22px; flex-shrink: 0;
    }
    .form-card-title { font-size: 16px; font-weight: 700; color: #1a3c34; }
    .form-card-subtitle { font-size: 13px; color: #7a9a90; margin-top: 2px; }

    .form-body { padding: 28px; display: flex; flex-direction: column; gap: 20px; }
    .form-group label {
        display: block; font-size: 12px; font-weight: 700;
        color: #1a3c34; letter-spacing: 0.5px;
        text-transform: uppercase; margin-bottom: 8px;
    }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 12px 16px;
        border: 1.5px solid #d0ddd9; border-radius: 10px;
        font-size: 14px; color: #333; background: #fff;
        font-family: inherit; outline: none; transition: border-color 0.18s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: #2d9e72; box-shadow: 0 0 0 3px rgba(45,158,114,0.1);
    }
    .form-group textarea { resize: vertical; min-height: 80px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-error { color: #dc2626; font-size: 12px; margin-top: 4px; }

    .form-footer {
        padding: 20px 28px; border-top: 1px solid #e8eeec;
        display: flex; justify-content: center; gap: 12px;
    }
    .btn-simpan {
        background: #1a3c34; color: #fff; border: none;
        padding: 12px 48px; border-radius: 999px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: background 0.18s;
    }
    .btn-simpan:hover { background: #2d9e72; }
    .btn-batal {
        background: #fff; color: #5a7a70;
        border: 1.5px solid #d0ddd9; padding: 12px 32px;
        border-radius: 999px; font-size: 15px; font-weight: 500;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: all 0.18s;
    }
    .btn-batal:hover { border-color: #2d9e72; color: #1a3c34; }
</style>
@endsection

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="form-card-icon"><i class="fas fa-pen"></i></div>
        <div>
            <div class="form-card-title">Edit Jadwal Pemeriksaan</div>
            <div class="form-card-subtitle">Perbarui informasi jadwal pemeriksaan Anda</div>
        </div>
    </div>

    <form action="{{ route('jadwal.update', $jadwal) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-body">

            <div class="form-group">
                <label>Jenis Pemeriksaan</label>
                <input type="text" name="jenis_pemeriksaan"
                       value="{{ old('jenis_pemeriksaan', $jadwal->jenis_pemeriksaan) }}" required>
                @error('jenis_pemeriksaan')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label>Fasilitas / Klinik</label>
                <input type="text" name="fasilitas_klinik"
                       value="{{ old('fasilitas_klinik', $jadwal->fasilitas_klinik) }}" required>
                @error('fasilitas_klinik')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal"
                           value="{{ old('tanggal', $jadwal->tanggal->format('Y-m-d')) }}" required>
                    @error('tanggal')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label>Waktu</label>
                    <input type="time" name="waktu"
                           value="{{ old('waktu', \Carbon\Carbon::parse($jadwal->waktu)->format('H:i')) }}" required>
                    @error('waktu')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="mendatang" {{ $jadwal->status === 'mendatang' ? 'selected' : '' }}>Mendatang</option>
                    <option value="selesai"   {{ $jadwal->status === 'selesai'   ? 'selected' : '' }}>Selesai</option>
                    <option value="batal"     {{ $jadwal->status === 'batal'     ? 'selected' : '' }}>Batal</option>
                </select>
            </div>

            <div class="form-group">
                <label>Catatan <span style="font-weight:400;color:#7a9a90">(opsional)</span></label>
                <textarea name="catatan">{{ old('catatan', $jadwal->catatan) }}</textarea>
                @error('catatan')<div class="form-error">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="form-footer">
            <a href="{{ route('jadwal.index') }}" class="btn-batal">Batal</a>
            <button type="submit" class="btn-simpan">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
