PANDUAN INSTALASI LENGKAP - MEDISLOT
====================================

MEDISLOT adalah sistem manajemen katalog pemeriksaan kesehatan berbasis Laravel.
Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi.

PERSYARATAN SISTEM
------------------
- PHP 8.1 atau lebih tinggi
- MySQL/MariaDB 5.7+
- Composer (PHP Package Manager)
- Git (opsional)
- Text Editor (VS Code, Sublime, dll)
- Terminal/Command Prompt

LANGKAH INSTALASI
-----------------

1. PERSIAPAN AWAL
   - Buka Terminal/Command Prompt
   - Navigasi ke folder project
   - Pastikan Anda memiliki akses administrator

2. INSTALL DEPENDENCIES PHP
   Jalankan perintah:
   
   composer install
   
   Tunggu sampai semua package selesai diunduh dan diinstall.

3. KONFIGURASI ENVIRONMENT
   a) Copy file .env.example menjadi .env
      cp .env.example .env
      
      (Atau manual: rename .env.example menjadi .env)
   
   b) Buka file .env dan konfigurasi:
   
      APP_NAME=MEDISLOT
      APP_ENV=local
      APP_DEBUG=true
      APP_URL=http://localhost:8000
      
      DB_CONNECTION=mysql
      DB_HOST=127.0.0.1
      DB_PORT=3306
      DB_DATABASE=medislot_db
      DB_USERNAME=root
      DB_PASSWORD=
      
      MAIL_DRIVER=log
      MAIL_FROM_ADDRESS=info@medislot.com

4. GENERATE APPLICATION KEY
   Jalankan:
   
   php artisan key:generate
   
   Ini akan membuat APP_KEY di file .env

5. BUAT DATABASE
   a) Buka phpMyAdmin atau tools MySQL Anda
   b) Buat database baru dengan nama: medislot_db
   c) Pastikan character set: utf8mb4
   d) Pastikan collation: utf8mb4_unicode_ci

6. JALANKAN MIGRATIONS
   Jalankan:
   
   php artisan migrate
   
   Ini akan membuat semua tabel di database.

7. SEED DATABASE (OPTIONAL - untuk data sample)
   Jalankan:
   
   php artisan db:seed
   
   Ini akan memasukkan data contoh pemeriksaan kesehatan.

8. GENERATE STORAGE LINK
   Jalankan:
   
   php artisan storage:link
   
   Ini membuat symlink untuk folder storage.

9. JALANKAN APPLICATION
   Jalankan:
   
   php artisan serve
   
   Aplikasi akan berjalan di http://localhost:8000

10. AKSES APLIKASI
    - Buka browser dan pergi ke http://localhost:8000
    - Anda akan melihat halaman katalog pemeriksaan
    - Untuk akses admin, login dengan credentials yang telah dibuat

STRUKTUR FOLDER PENTING
-----------------------
app/                    - Source code PHP/Laravel
├── Http/
│   └── Controllers/    - Logic aplikasi
├── Models/             - Database models
config/                 - File konfigurasi
database/
├── migrations/         - Database schema
└── seeders/           - Data sample
resources/
├── views/             - Template HTML (Blade)
└── css/               - Stylesheet
public/               - File yang bisa diakses public
routes/               - Route definitions
storage/              - Cache, logs, uploads
tests/                - Test files

FITUR UTAMA YANG TERSEDIA
-------------------------
✓ Katalog Pemeriksaan Kesehatan
✓ Manajemen Jadwal Pemeriksaan
✓ Rekomendasi Jadwal berdasarkan Usia
✓ Dashboard Admin
✓ CRUD Operations untuk semua data
✓ Filter dan Pencarian
✓ Responsive Design

COMMAND BERGUNA
---------------
php artisan serve                    - Start dev server
php artisan migrate                  - Run migrations
php artisan db:seed                  - Seed database
php artisan tinker                   - Interactive shell
php artisan make:model ModelName     - Create model
php artisan make:controller ControllerName - Create controller
php artisan cache:clear              - Clear cache
php artisan route:list               - List all routes

TROUBLESHOOTING
---------------

Problem: "Class 'PDO' not found"
Solution: Install PHP MySQL extension (php_pdo_mysql.dll di Windows)

Problem: "Column not found in database"
Solution: Jalankan fresh migration
  php artisan migrate:refresh

Problem: Permission denied untuk storage folder
Solution: 
  Windows: Right-click folder > Properties > Security > Edit
  Linux: chmod -R 755 storage/

Problem: "No application encryption key has been specified"
Solution: Jalankan php artisan key:generate

Problem: "Class not found"
Solution: Jalankan composer dump-autoload

Problem: "Port 8000 already in use"
Solution: Gunakan port berbeda:
  php artisan serve --port=8001

PASCA INSTALASI
---------------
1. Ubah password default jika ada akun admin
2. Sesuaikan data pemeriksaan dengan kebutuhan
3. Buat jadwal pemeriksaan
4. Buat rekomendasi jadwal
5. Test fitur dari halaman katalog
6. Backup database secara berkala

MAINTENANCE
-----------
1. Buat backup database secara rutin
2. Clear cache jika ada perubahan:
   php artisan cache:clear
3. Update dependencies:
   composer update
4. Monitor file logs:
   storage/logs/laravel.log

PERSIAPAN UNTUK PRODUCTION
---------------------------
1. Set APP_DEBUG=false di .env
2. Set APP_ENV=production di .env
3. Jalankan: php artisan config:cache
4. Jalankan: php artisan route:cache
5. Gunakan web server (Apache/Nginx) bukan php artisan serve
6. Aktifkan HTTPS
7. Backup database rutin

DUKUNGAN DAN BANTUAN
--------------------
- Email: support@medislot.com
- Documentation: Lihat folder docs/
- Issue Tracker: GitHub repository
- FAQ: Lihat README.md

LISENSI
-------
MIT License - Bebas digunakan

Created by: SI4705-KELE
Last Updated: 2024
