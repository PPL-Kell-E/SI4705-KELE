@extends('layouts.app')

@section('content')
<div class="health-card">
    <div class="card-header"></div>
    <div class="card-body">
        <div class="card-title-section">
            <div class="card-icon">
                <i class="fa-solid fa-book-medical"></i>
            </div>
            <div class="card-title-text">
                <h2>Buku Catatan Medis</h2>
                <p>Lengkapi data di bawah ini untuk mendapatkan pantauan kondisi tubuh yang lebih akurat.</p>
            </div>
        </div>

        <div class="data-section">
            <div class="section-header">
                <i class="fa-solid fa-weight-scale"></i> Pengukuran Fisik
            </div>
            <div class="data-grid">
                <div class="data-item">
                    <label>Tinggi Badan (CM)</label>
                    <div class="data-value">{{ $healthData->height ?? '-' }}</div>
                </div>
                <div class="data-item">
                    <label>Berat Badan (KG)</label>
                    <div class="data-value">{{ $healthData->weight ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="data-section">
            <div class="section-header">
                <i class="fa-solid fa-circle-info"></i> Profil Kesehatan
            </div>
            <div class="data-grid">
                <div class="data-item">
                    <label>Golongan Darah</label>
                    <div class="data-value">{{ $healthData->blood_type ?? '-' }}</div>
                </div>
                <div class="data-item">
                    <label>Alergi</label>
                    <div class="data-value">{{ $healthData->allergies ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="data-section">
            <div class="section-header">
                <i class="fa-solid fa-notes-medical"></i> Riwayat & Kondisi
            </div>
            <div class="data-grid">
                <div class="data-item">
                    <label>Terakhir Check Up</label>
                    <div class="data-value">{{ $healthData->last_checkup ?? '-' }}</div>
                </div>
                <div class="data-item">
                    <label>Riwayat Penyakit</label>
                    <div class="data-value">{{ $healthData->medical_history ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="btn-container">
            @if($healthData)
                <button class="btn-primary" onclick="openModal('edit')">Edit catatan</button>
            @else
                <button class="btn-primary" onclick="openModal('input')">Buat Catatan Baru</button>
            @endif
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal-overlay" id="healthModal">
    <div class="modal">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h2 class="modal-title">Data Kesehatan</h2>
        
        <form id="healthForm" method="POST" action="{{ route('health.store') }}">
            @csrf
            <div id="methodField"></div>
            
            @if ($errors->any())
                <div style="color: red; margin-bottom: 1rem;">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="data-section">
                <div class="section-header">
                    <i class="fa-solid fa-weight-scale"></i> Pengukuran Fisik
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tinggi Badan (CM)</label>
                        <input type="number" step="0.1" name="height" id="input_height" placeholder="166">
                    </div>
                    <div class="form-group">
                        <label>Berat Badan (KG)</label>
                        <input type="number" step="0.1" name="weight" id="input_weight" placeholder="55">
                    </div>
                </div>
            </div>

            <div class="data-section">
                <div class="section-header">
                    <i class="fa-solid fa-circle-info"></i> Profil Kesehatan
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Golongan Darah</label>
                        <input type="text" name="blood_type" id="input_blood_type" placeholder="O">
                    </div>
                    <div class="form-group">
                        <label>Alergi</label>
                        <input type="text" name="allergies" id="input_allergies" placeholder="Kacang">
                    </div>
                </div>
            </div>

            <div class="data-section">
                <div class="section-header">
                    <i class="fa-solid fa-notes-medical"></i> Riwayat & Kondisi
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Terakhir Check Up</label>
                        <input type="date" name="last_checkup" id="input_last_checkup">
                    </div>
                    <div class="form-group">
                        <label>Riwayat Penyakit</label>
                        <input type="text" name="medical_history" id="input_medical_history" placeholder="Asma">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-submit">Selesai</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const modal = document.getElementById('healthModal');
    const form = document.getElementById('healthForm');
    const methodField = document.getElementById('methodField');

    const healthData = @json($healthData);

    function openModal(mode) {
        if (mode === 'edit' && healthData) {
            form.action = "/health-data/" + healthData.id;
            methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            
            document.getElementById('input_height').value = healthData.height;
            document.getElementById('input_weight').value = healthData.weight;
            document.getElementById('input_blood_type').value = healthData.blood_type;
            document.getElementById('input_allergies').value = healthData.allergies;
            document.getElementById('input_last_checkup').value = healthData.last_checkup;
            document.getElementById('input_medical_history').value = healthData.medical_history;
        } else {
            form.action = "{{ route('health.store') }}";
            methodField.innerHTML = '';
            form.reset();
        }
        modal.classList.add('active');
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    // Automatically open modal if there are errors
    @if ($errors->any())
        openModal('{{ old("_method") == "PUT" ? "edit" : "input" }}');
    @endif
</script>
@endsection
