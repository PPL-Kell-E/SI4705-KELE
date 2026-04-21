<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDISLOT - Data Kesehatan</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Medislot Logo">
            <span>MEDISLOT</span>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="nav-item"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-bell"></i> Pengingat</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-lightbulb"></i> Rekomendasi</a></li>
            <li><a href="{{ route('health.index') }}" class="nav-item active"><i class="fa-solid fa-square-check"></i> Data Kesehatan</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-book-medical"></i> Katalog Pemeriksaan</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-calendar-days"></i> Jadwal Saya</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-chart-line"></i> Insight</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-clipboard-list"></i> Hasil Pemeriksaan</a></li>
            <li><a href="#" class="nav-item"><i class="fa-solid fa-user"></i> Profile</a></li>
        </ul>
        <a href="#" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content">
        <header>
            <div class="page-title">
                <h1>Data Kesehatan</h1>
                <p>Catatan personal premium yang peduli pada kondisi tubuh Anda.</p>
            </div>
            <div class="header-actions">
                <button class="icon-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
                <button class="icon-btn"><i class="fa-solid fa-bell"></i></button>
                <button class="icon-btn"><i class="fa-solid fa-user"></i></button>
            </div>
        </header>

        @yield('content')
    </div>

    @if(session('success'))
    <div class="modal-overlay active" id="successOverlay">
        <div class="success-popup active">
            <div class="success-icon">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3>{{ session('success') }}</h3>
            <button class="btn-continue" onclick="document.getElementById('successOverlay').classList.remove('active')">Continue</button>
        </div>
    </div>
    @endif

    @yield('scripts')
</body>
</html>
