PANDUAN QUICK START
===================

Panduan cepat untuk menjalankan MEDISLOT dalam 5 menit!

LANGKAH 1: SETUP DATABASE
--------------------------
1. Buka phpMyAdmin: http://localhost/phpmyadmin
2. Klik "New" atau "Buat Database Baru"
3. Nama Database: medislot_db
4. Collation: utf8mb4_unicode_ci
5. Klik "Create"

LANGKAH 2: KONFIGURASI .env
----------------------------
1. Buka file MedislotApp/.env
2. Ubah/sesuaikan:
   
   DB_DATABASE=medislot_db
   DB_USERNAME=root
   DB_PASSWORD=[kosongkan atau sesuaikan]

LANGKAH 3: INSTALL & RUN
------------------------
Buka Terminal di folder MedislotApp:

composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve

LANGKAH 4: AKSES APLIKASI
--------------------------
Buka browser:
http://localhost:8000

✅ BERHASIL! Aplikasi sudah berjalan.

HALAMAN YANG TERSEDIA
---------------------
1. http://localhost:8000/
   Halaman Katalog Pemeriksaan (Publik)

2. http://localhost:8000/dashboard
   Dashboard Admin (Perlu Login)

MENU DI DASHBOARD
-----------------
- Dashboard: Lihat statistik
- Tambah Pemeriksaan Baru: Create examination
- Tambah Jadwal: Create schedule
- Tambah Rekomendasi: Create recommendation

FITUR UTAMA
-----------
✓ Filter Pemeriksaan per Kategori
✓ Tampilkan Jadwal Tersedia
✓ Rekomendasi Jadwal sesuai Usia
✓ Admin CRUD untuk semua data
✓ Responsive Mobile Design

SHORTCUT COMMANDS
------------------
Jika ada error, jalankan:

# Clear cache
php artisan cache:clear

# Reset database (hapus semua, jalankan ulang migrations + seed)
php artisan migrate:refresh --seed

# Lihat database langsung
php artisan tinker

NEXT STEPS
----------
1. Customize data pemeriksaan sesuai kebutuhan
2. Tambahkan logo/branding
3. Setup authentication lengkap
4. Deploy ke production

BANTUAN
-------
- Baca README.md untuk detail lengkap
- Baca INSTALASI.md untuk panduan detail
- Baca STRUKTUR.md untuk architecture
- Baca folder resources/views untuk template

Enjoy! 🚀
