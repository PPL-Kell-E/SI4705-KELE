README MEDISLOT - Sistem Katalog Pemeriksaan Kesehatan
======================================================

MEDISLOT adalah aplikasi web berbasis Laravel yang dirancang untuk mengelola katalog pemeriksaan kesehatan, jadwal pemeriksaan, dan rekomendasi jadwal pemeriksaan sesuai usia pasien.

FITUR UTAMA
-----------
✓ Katalog Pemeriksaan Kesehatan
  - Tampilkan daftar lengkap pemeriksaan kesehatan
  - Filter berdasarkan kategori
  - Detail pemeriksaan dengan harga, durasi, dan deskripsi

✓ Manajemen Jadwal Pemeriksaan
  - Buat jadwal pemeriksaan baru
  - Kelola kapasitas dan status jadwal
  - Tampilkan jadwal tersedia untuk pasien

✓ Rekomendasi Jadwal Pemeriksaan
  - Buat rekomendasi berdasarkan usia pasien
  - Tentukan frekuensi pemeriksaan
  - Tampilkan rekomendasi di detail pemeriksaan

✓ Dashboard Admin
  - Kelola semua pemeriksaan
  - Monitor jadwal dan rekomendasi
  - Statistik sistem

PERSYARATAN SISTEM
------------------
- PHP 8.1 atau lebih tinggi
- MySQL/MariaDB 5.7 atau lebih tinggi
- Composer
- Node.js (opsional, untuk asset compilation)

INSTALASI
---------
1. Clone repository atau extract file
2. Install dependencies:
   composer install
   
3. Copy .env.example ke .env:
   cp .env.example .env
   
4. Generate application key:
   php artisan key:generate
   
5. Configure database di file .env:
   DB_DATABASE=medislot_db
   DB_USERNAME=root
   DB_PASSWORD=
   
6. Jalankan migrations:
   php artisan migrate
   
7. (Opsional) Jalankan seeders untuk data sample:
   php artisan db:seed
   
8. Jalankan development server:
   php artisan serve
   
9. Akses aplikasi di: http://localhost:8000

STRUKTUR FILE
-------------
MedislotApp/
├── app/
│   ├── Http/Controllers/    # Controllers aplikasi
│   │   ├── ExaminationController.php
│   │   ├── ScheduleController.php
│   │   └── RecommendationController.php
│   └── Models/              # Eloquent Models
│       ├── Examination.php
│       ├── ExaminationSchedule.php
│       ├── ScheduleRecommendation.php
│       └── ExaminationBooking.php
├── database/
│   └── migrations/          # Database migrations
├── resources/
│   └── views/               # Blade templates
│       ├── layout.blade.php
│       ├── examinations/
│       ├── schedules/
│       └── recommendations/
├── public/
│   ├── css/style.css        # Custom styling
│   └── js/script.js         # Custom JavaScript
├── routes/
│   └── web.php              # Route definitions
├── config/
│   └── examinations.php     # Configuration data
├── composer.json            # PHP dependencies
├── .env.example             # Environment variables template
└── README.md                # File ini

ROUTE UTAMA
-----------
GET  /                              - Halaman katalog pemeriksaan
GET  /examinations/{id}             - Detail pemeriksaan

Admin Routes (memerlukan autentikasi):
GET  /dashboard                     - Dashboard admin
GET  /examinations/create           - Form tambah pemeriksaan
POST /examinations                  - Simpan pemeriksaan baru
GET  /examinations/{id}/edit        - Form edit pemeriksaan
PUT  /examinations/{id}             - Perbarui pemeriksaan
DELETE /examinations/{id}           - Hapus pemeriksaan

GET  /schedules                     - Daftar jadwal
POST /schedules                     - Buat jadwal baru
GET  /schedules/create              - Form buat jadwal
GET  /schedules/{id}/edit           - Form edit jadwal
PUT  /schedules/{id}                - Perbarui jadwal
DELETE /schedules/{id}              - Hapus jadwal

GET  /recommendations               - Daftar rekomendasi
POST /recommendations               - Buat rekomendasi baru
GET  /recommendations/create        - Form buat rekomendasi
GET  /recommendations/{id}/edit     - Form edit rekomendasi
PUT  /recommendations/{id}          - Perbarui rekomendasi
DELETE /recommendations/{id}        - Hapus rekomendasi

DATA SAMPLE
-----------
Sistem dilengkapi dengan 9 jenis pemeriksaan kesehatan:
1. Elektrokardiogram (EKG) - Kategori Jantung
2. Elektrodiagraf - Kategori Neurologi
3. Pemeriksaan Fisik Umum - Kategori Umum
4. Scaling & Pembersihan Karang - Kategori Gigi
5. Tes Darah Lengkap - Kategori Lab
6. Endoskopi THT - Kategori THT
7. Pemeriksaan Glaukoma - Kategori Mata
8. Tes Darah Lengkap & Kolesterol - Kategori Lab
9. Konsultasi Dermatologi - Kategori Dermatologi

TEKNOLOGI YANG DIGUNAKAN
------------------------
- Laravel 10 (Backend Framework)
- PHP 8.1+ (Language)
- MySQL (Database)
- Bootstrap 5 (Frontend Framework)
- Font Awesome 6 (Icon Library)
- Blade Template Engine
- Eloquent ORM

PANDUAN PENGGUNAAN
------------------

1. MELIHAT KATALOG PEMERIKSAAN
   - Akses halaman utama "/"
   - Telusuri daftar pemeriksaan yang tersedia
   - Filter berdasarkan kategori
   - Klik "Lihat Detail" untuk melihat jadwal dan rekomendasi

2. MENGELOLA PEMERIKSAAN (Admin)
   - Login ke sistem
   - Akses Dashboard Admin
   - Gunakan tombol "Tambah Pemeriksaan Baru"
   - Isi form dengan data pemeriksaan
   - Klik "Simpan Pemeriksaan"

3. MENGELOLA JADWAL (Admin)
   - Akses "Tambah Jadwal" dari dashboard
   - Pilih pemeriksaan
   - Tentukan tanggal dan jam
   - Atur kapasitas maksimal
   - Jadwal siap dipesan oleh pasien

4. MEMBUAT REKOMENDASI (Admin)
   - Akses "Tambah Rekomendasi" dari dashboard
   - Pilih pemeriksaan
   - Atur rentang usia
   - Tentukan frekuensi pemeriksaan
   - Tambahkan deskripsi
   - Simpan rekomendasi

TROUBLESHOOTING
---------------

Q: Error "Class not found"
A: Jalankan `composer dump-autoload`

Q: Database connection error
A: Verifikasi pengaturan DB di .env dan pastikan database sudah dibuat

Q: Migration error
A: Pastikan database kosong atau jalankan `php artisan migrate:reset` terlebih dahulu

Q: Permission denied untuk storage/logs
A: Jalankan `php artisan storage:link` dan ubah permissions folder

KONTRIBUSI
----------
Untuk melaporkan bug atau memberikan saran, silakan hubungi tim SI4705-KELE.

LISENSI
-------
MIT License - Bebas digunakan untuk keperluan komersial dan non-komersial

KONTAK
------
Email: info@medislot.com
Support: support@medislot.com
Website: www.medislot.com

Terima kasih telah menggunakan MEDISLOT!
