<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Medislot - Janji Temu Medis'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/medislot.css')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <div class="logo-icon">
                    <i class="fas fa-notes-medical"></i>
                </div>
                <h1 class="brand-name">MEDISLOT</h1>
            </div>
            
            <nav class="nav-menu">
                <a href="#" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="#" class="nav-item"><i class="fas fa-bell"></i> Pengingat</a>
                <a href="#" class="nav-item"><i class="fas fa-lightbulb"></i> Rekomendasi</a>
                <a href="#" class="nav-item"><i class="fas fa-check-square"></i> Data Kesehatan</a>
                <a href="#" class="nav-item"><i class="fas fa-book-medical"></i> Katalog Pemeriksaan</a>
                <a href="<?php echo e(route('schedules.index')); ?>" class="nav-item active"><i class="fas fa-calendar-alt"></i> Jadwal Saya</a>
                <a href="#" class="nav-item"><i class="fas fa-history"></i> Riwayat</a>
                <a href="#" class="nav-item"><i class="fas fa-chart-line"></i> Insight</a>
                <a href="#" class="nav-item"><i class="fas fa-file-medical-alt"></i> Hasil Pemeriksaan</a>
                <a href="#" class="nav-item"><i class="fas fa-user"></i> Profile</a>
            </nav>

            <div class="logout-section">
                <a href="#" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari di sini...">
                </div>
                <div class="header-actions">
                    <button class="action-btn"><i class="fas fa-search"></i></button>
                    <button class="action-btn"><i class="fas fa-bell"></i></button>
                    <div class="user-profile">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Felix" alt="User Profile">
                    </div>
                </div>
            </header>

            <div class="page-content">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </main>
    </div>

    <script src="<?php echo e(asset('js/medislot.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/prisaayudianingtiyas/SI4705-KELE/resources/views/layouts/app.blade.php ENDPATH**/ ?>