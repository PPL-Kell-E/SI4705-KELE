<?php $__env->startSection('title', 'Jadwal Saya - Medislot'); ?>

<?php $__env->startSection('content'); ?>
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
    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="schedule-card">
            <div class="date-badge">
                <span class="day"><?php echo e(\Carbon\Carbon::parse($schedule->date)->format('d')); ?></span>
                <span class="month"><?php echo e(strtoupper(\Carbon\Carbon::parse($schedule->date)->format('M'))); ?></span>
                <span class="year"><?php echo e(\Carbon\Carbon::parse($schedule->date)->format('Y')); ?></span>
            </div>
            <div class="card-content">
                <div class="card-main">
                    <div class="card-header">
                        <h3><?php echo e($schedule->checkup_type); ?></h3>
                        <span class="status-badge <?php echo e($schedule->status); ?>"><?php echo e(ucfirst($schedule->status)); ?></span>
                    </div>
                    <div class="card-info">
                        <div class="info-item">
                            <i class="far fa-clock"></i> <?php echo e(\Carbon\Carbon::parse($schedule->time)->format('H.i')); ?> WIB
                        </div>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i> <?php echo e($schedule->facility); ?>

                        </div>
                    </div>
                    <?php if($schedule->notes): ?>
                        <div class="card-notes">
                            "<?php echo e($schedule->notes); ?>"
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-actions">
                    <button class="btn-outline-edit"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn-danger"><i class="fas fa-trash"></i> Hapus</button>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>Belum ada jadwal pemeriksaan.</p>
        </div>
    <?php endif; ?>
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
            <?php echo csrf_field(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/prisaayudianingtiyas/SI4705-KELE/resources/views/schedules/index.blade.php ENDPATH**/ ?>