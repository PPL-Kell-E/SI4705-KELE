DOKUMENTASI API
================

Dokumentasi teknis untuk developers yang ingin mengintegrasikan MEDISLOT
dengan sistem lain atau membuat custom aplikasi.

📌 BASE URL
-----------
http://localhost:8000

🔐 AUTHENTICATION
------------------
Semua endpoint yang memerlukan auth, tambahkan header:
Authorization: Bearer {token}
atau login terlebih dahulu (session-based)

📋 ENDPOINTS PUBLIK
--------------------

1. GET /
   Daftar semua pemeriksaan aktif
   Response: HTML (Halaman Katalog)

2. GET /examinations/{id}
   Detail pemeriksaan + jadwal + rekomendasi
   Parameters:
   - id (required): ID pemeriksaan
   Response: HTML (Detail Page)

🔒 ENDPOINTS ADMIN (Requires Authentication)
-----------------------------------------------

EXAMINATION ENDPOINTS
---------------------

1. GET /dashboard
   Dashboard admin dengan statistik
   Response: HTML Dashboard

2. GET /examinations/create
   Form untuk membuat pemeriksaan baru
   Response: HTML Form

3. POST /examinations
   Buat pemeriksaan baru
   Request Body:
   {
     "name": "Nama Pemeriksaan",
     "description": "Deskripsi",
     "icon": "fas fa-heart",
     "category": "Kategori",
     "price": 150000,
     "duration": 30
   }
   Response: Redirect + Success Message

4. GET /examinations/{id}/edit
   Form untuk edit pemeriksaan
   Response: HTML Form

5. PUT /examinations/{id}
   Update pemeriksaan
   Request Body: (sama seperti POST)
   Response: Redirect + Success Message

6. DELETE /examinations/{id}
   Hapus pemeriksaan
   Response: Redirect + Success Message

SCHEDULE ENDPOINTS
-------------------

1. GET /schedules
   Daftar semua jadwal pemeriksaan
   Response: HTML (Table List)

2. GET /schedules/create
   Form untuk membuat jadwal baru
   Response: HTML Form

3. POST /schedules
   Buat jadwal baru
   Request Body:
   {
     "examination_id": 1,
     "schedule_date": "2024-02-15",
     "start_time": "09:00",
     "end_time": "17:00",
     "max_capacity": 20
   }
   Response: Redirect + Success Message

4. GET /schedules/{id}
   Detail jadwal + bookings
   Response: HTML Detail

5. GET /schedules/{id}/edit
   Form untuk edit jadwal
   Response: HTML Form

6. PUT /schedules/{id}
   Update jadwal
   Request Body:
   {
     "examination_id": 1,
     "schedule_date": "2024-02-15",
     "start_time": "09:00",
     "end_time": "17:00",
     "max_capacity": 20,
     "status": "available"
   }
   Response: Redirect + Success Message

7. DELETE /schedules/{id}
   Hapus jadwal
   Response: Redirect + Success Message

RECOMMENDATION ENDPOINTS
-------------------------

1. GET /recommendations
   Daftar semua rekomendasi jadwal
   Response: HTML (Table List)

2. GET /recommendations/create
   Form untuk membuat rekomendasi baru
   Response: HTML Form

3. POST /recommendations
   Buat rekomendasi baru
   Request Body:
   {
     "examination_id": 1,
     "age_min": 40,
     "age_max": 60,
     "frequency": 1,
     "frequency_unit": "tahun",
     "description": "Pemeriksaan untuk pencegahan penyakit"
   }
   Response: Redirect + Success Message

4. GET /recommendations/{id}
   Detail rekomendasi
   Response: HTML Detail

5. GET /recommendations/{id}/edit
   Form untuk edit rekomendasi
   Response: HTML Form

6. PUT /recommendations/{id}
   Update rekomendasi
   Request Body: (sama seperti POST)
   Response: Redirect + Success Message

7. DELETE /recommendations/{id}
   Hapus rekomendasi
   Response: Redirect + Success Message

📊 DATA MODELS
--------------

Model Examination
{
  "id": 1,
  "name": "EKG",
  "description": "Pemeriksaan jantung",
  "icon": "fas fa-heart",
  "category": "Jantung",
  "price": 150000,
  "duration": 15,
  "is_active": true,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}

Model ExaminationSchedule
{
  "id": 1,
  "examination_id": 1,
  "schedule_date": "2024-02-15",
  "start_time": "09:00:00",
  "end_time": "17:00:00",
  "max_capacity": 20,
  "current_capacity": 15,
  "status": "available",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}

Model ScheduleRecommendation
{
  "id": 1,
  "examination_id": 1,
  "age_min": 40,
  "age_max": 60,
  "frequency": 1,
  "frequency_unit": "tahun",
  "description": "Untuk pencegahan penyakit",
  "is_active": true,
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}

🔄 HTTP STATUS CODES
---------------------
200 OK - Request berhasil
201 Created - Resource berhasil dibuat
204 No Content - Request berhasil, tidak ada content
301 Moved Permanently - Redirect
302 Found - Redirect
400 Bad Request - Request tidak valid
401 Unauthorized - Auth diperlukan
403 Forbidden - Akses ditolak
404 Not Found - Resource tidak ditemukan
422 Unprocessable Entity - Validasi gagal
500 Server Error - Error di server

✅ CONTOH REQUEST
------------------

1. Create Examination
curl -X POST http://localhost:8000/examinations \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -d "name=EKG&description=Pemeriksaan Jantung&icon=fas fa-heart&category=Jantung&price=150000&duration=15"

2. Get Schedule List
curl http://localhost:8000/schedules

3. Create Schedule
curl -X POST http://localhost:8000/schedules \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "X-CSRF-TOKEN: {csrf_token}" \
  -d "examination_id=1&schedule_date=2024-02-15&start_time=09:00&end_time=17:00&max_capacity=20"

🚀 INTEGRASI DENGAN JAVASCRIPT
-------------------------------

// Fetch examination list
fetch('http://localhost:8000/')
  .then(response => response.text())
  .then(data => console.log(data))

// Create new examination (requires CSRF token from form)
fetch('http://localhost:8000/examinations', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'EKG',
    description: 'Pemeriksaan Jantung',
    icon: 'fas fa-heart',
    category: 'Jantung',
    price: 150000,
    duration: 15
  })
})

💡 TIPS DEVELOPMENT
--------------------
1. Gunakan Laravel Tinker untuk test queries
   php artisan tinker
   
2. Debug dengan dd() helper
   dd($variable);
   
3. Lihat SQL queries yang di-generate
   DB::enableQueryLog();
   
4. Test dengan form tools (Postman, Insomnia)

5. Check database dengan migrations:
   php artisan migrate:status

📚 REFERENSI TAMBAHAN
---------------------
- Laravel Docs: https://laravel.com/docs
- Blade Templating: https://laravel.com/docs/blade
- Eloquent ORM: https://laravel.com/docs/eloquent
- Routing: https://laravel.com/docs/routing
- Validation: https://laravel.com/docs/validation

Dibuat oleh: SI4705-KELE Team
Last Updated: 2024
