RINGKASAN STRUKTUR APLIKASI MEDISLOT
====================================

📁 STRUKTUR FOLDER
-------------------
MedislotApp/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── ExaminationController.php       [Kelola Pemeriksaan]
│   │       ├── ScheduleController.php          [Kelola Jadwal]
│   │       └── RecommendationController.php    [Kelola Rekomendasi]
│   └── Models/
│       ├── User.php                           [Model User]
│       ├── Examination.php                    [Model Pemeriksaan]
│       ├── ExaminationSchedule.php            [Model Jadwal]
│       ├── ScheduleRecommendation.php         [Model Rekomendasi]
│       └── ExaminationBooking.php             [Model Booking]
│
├── config/
│   └── examinations.php                        [Data Pemeriksaan Sample]
│
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_examinations_table.php
│   │   ├── 2024_01_01_000002_create_examination_schedules_table.php
│   │   ├── 2024_01_01_000003_create_schedule_recommendations_table.php
│   │   └── 2024_01_01_000004_create_examination_bookings_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php                 [Main Seeder]
│       └── ExaminationSeeder.php              [Seeder Pemeriksaan]
│
├── resources/
│   └── views/
│       ├── layout.blade.php                   [Layout Utama]
│       ├── examinations/
│       │   ├── index.blade.php                [Halaman Katalog]
│       │   ├── show.blade.php                 [Detail Pemeriksaan]
│       │   ├── create.blade.php               [Form Tambah]
│       │   ├── edit.blade.php                 [Form Edit]
│       │   └── dashboard.blade.php            [Dashboard Admin]
│       ├── schedules/
│       │   ├── index.blade.php                [Daftar Jadwal]
│       │   ├── create.blade.php               [Form Tambah Jadwal]
│       │   ├── show.blade.php                 [Detail Jadwal]
│       │   └── edit.blade.php                 [Form Edit Jadwal]
│       └── recommendations/
│           ├── index.blade.php                [Daftar Rekomendasi]
│           ├── create.blade.php               [Form Tambah Rekomendasi]
│           ├── show.blade.php                 [Detail Rekomendasi]
│           └── edit.blade.php                 [Form Edit Rekomendasi]
│
├── public/
│   ├── css/
│   │   └── style.css                          [Custom Styling]
│   └── js/
│       └── script.js                          [Custom JavaScript]
│
├── routes/
│   └── web.php                                [Route Definitions]
│
├── storage/
│   └── logs/                                  [Application Logs]
│
├── .env.example                               [Environment Template]
├── composer.json                              [PHP Dependencies]
├── README.md                                  [Dokumentasi Utama]
├── INSTALASI.md                               [Panduan Instalasi]
└── STRUKTUR.md                                [File ini]

📊 DATABASE SCHEMA
------------------

Tabel: examinations
├── id (Primary Key)
├── name (string)
├── description (text)
├── icon (string)
├── category (string)
├── price (decimal)
├── duration (integer - minutes)
├── is_active (boolean)
├── created_at
└── updated_at

Tabel: examination_schedules
├── id (Primary Key)
├── examination_id (Foreign Key)
├── schedule_date (date)
├── start_time (time)
├── end_time (time)
├── max_capacity (integer)
├── current_capacity (integer)
├── status (enum: available, full, cancelled)
├── created_at
└── updated_at

Tabel: schedule_recommendations
├── id (Primary Key)
├── examination_id (Foreign Key)
├── age_min (integer)
├── age_max (integer)
├── frequency (integer)
├── frequency_unit (enum: hari, minggu, bulan, tahun)
├── description (text)
├── is_active (boolean)
├── created_at
└── updated_at

Tabel: examination_bookings
├── id (Primary Key)
├── user_id (Foreign Key)
├── schedule_id (Foreign Key)
├── booking_date (datetime)
├── status (enum: pending, confirmed, completed, cancelled)
├── notes (text, nullable)
├── created_at
└── updated_at

🎨 TEKNOLOGI YANG DIGUNAKAN
----------------------------
Backend:
- Laravel 10 (Web Framework)
- PHP 8.1+ (Programming Language)
- MySQL/MariaDB (Database)
- Eloquent ORM (Database Abstraction)

Frontend:
- Bootstrap 5 (UI Framework)
- Font Awesome 6 (Icons)
- Blade Template Engine (Templating)
- Vanilla JavaScript (Interactivity)

🔄 ALUR APLIKASI
-----------------

1. User mengakses /
   ↓
2. ExaminationController@index
   ↓
3. Retrieve examinations dari database
   ↓
4. Render view examinations/index.blade.php
   ↓
5. Tampilkan katalog pemeriksaan dengan filter kategori

Alur Detail Pemeriksaan:
1. User klik "Lihat Detail"
   ↓
2. ExaminationController@show
   ↓
3. Retrieve examination + schedules + recommendations
   ↓
4. Render view examinations/show.blade.php
   ↓
5. Tampilkan detail + jadwal + rekomendasi

Alur Admin Create Pemeriksaan:
1. Admin access /examinations/create
   ↓
2. ExaminationController@create
   ↓
3. Render form di examinations/create.blade.php
   ↓
4. Admin submit form
   ↓
5. ExaminationController@store
   ↓
6. Validate dan save ke database
   ↓
7. Redirect ke dashboard

📝 MODEL RELATIONSHIPS
----------------------

Examination
├── hasMany ExaminationSchedule
└── hasMany ScheduleRecommendation

ExaminationSchedule
├── belongsTo Examination
└── hasMany ExaminationBooking

ScheduleRecommendation
└── belongsTo Examination

ExaminationBooking
├── belongsTo User
└── belongsTo ExaminationSchedule

User
└── hasMany ExaminationBooking

🚀 FITUR UNGGULAN
------------------
1. Responsive Design - Mobile friendly
2. Filter Kategori - Pencarian mudah
3. Rekomendasi Otomatis - Berdasarkan usia
4. Dashboard Admin - Manajemen lengkap
5. Form Validation - Input yang aman
6. Modern UI - Bootstrap 5
7. RESTful API - Struktur routes yang clean
8. Database Relationships - Eloquent ORM

📋 DAFTAR ROUTE
----------------

PUBLIC ROUTES:
GET  /                              → index katalog
GET  /examinations/{id}             → detail pemeriksaan

ADMIN ROUTES (dengan auth):
GET  /dashboard                     → dashboard
GET  /examinations/create           → form tambah
POST /examinations                  → store baru
GET  /examinations/{id}/edit        → form edit
PUT  /examinations/{id}             → update
DELETE /examinations/{id}           → delete

GET  /schedules                     → list jadwal
POST /schedules                     → store jadwal
GET  /schedules/create              → form jadwal
GET  /schedules/{id}                → detail jadwal
GET  /schedules/{id}/edit           → form edit jadwal
PUT  /schedules/{id}                → update jadwal
DELETE /schedules/{id}              → delete jadwal

GET  /recommendations               → list rekomendasi
POST /recommendations               → store rekomendasi
GET  /recommendations/create        → form rekomendasi
GET  /recommendations/{id}          → detail rekomendasi
GET  /recommendations/{id}/edit     → form edit rekomendasi
PUT  /recommendations/{id}          → update rekomendasi
DELETE /recommendations/{id}        → delete rekomendasi

🔐 KEAMANAN
-----------
- CSRF Protection (token di form)
- SQL Injection Prevention (parameterized queries)
- XSS Prevention (blade escaping)
- Authentication & Authorization
- Password Hashing (bcrypt)
- Input Validation

✅ TESTING CHECKLIST
-------------------
[ ] Katalog bisa diakses (publik)
[ ] Detail pemeriksaan tampil dengan benar
[ ] Filter kategori berfungsi
[ ] Admin dashboard accessible (after login)
[ ] Tambah pemeriksaan berfungsi
[ ] Edit pemeriksaan berfungsi
[ ] Delete pemeriksaan berfungsi
[ ] Tambah jadwal berfungsi
[ ] Edit jadwal berfungsi
[ ] Tambah rekomendasi berfungsi
[ ] Edit rekomendasi berfungsi
[ ] Form validation berfungsi
[ ] Database seeder berfungsi

Generated by: SI4705-KELE
Last Updated: 2024
