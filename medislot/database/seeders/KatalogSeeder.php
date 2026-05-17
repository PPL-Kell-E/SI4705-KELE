<?php

namespace Database\Seeders;

use App\Models\KatalogPemeriksaan;
use Illuminate\Database\Seeder;

class KatalogSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'slug' => 'ekg', 'nama' => 'Elektrokardiogram (EKG)', 'kategori' => 'Jantung',
                'icon' => 'fa-wave-square', 'bg_color' => '#e8f5f0', 'icon_color' => '#2d9e72',
                'singkat' => 'Perekaman aktivitas listrik jantung untuk mendeteksi kelainan irama jantung.',
                'deskripsi' => 'Elektrokardiogram (EKG) adalah prosedur diagnostik yang merekam aktivitas listrik jantung menggunakan elektroda yang ditempel di kulit. Hasil rekaman membantu dokter mendeteksi aritmia, serangan jantung, pembesaran jantung, dan gangguan konduksi lainnya.',
                'persiapan' => ['Kenakan pakaian yang mudah dibuka di bagian dada.', 'Hindari penggunaan losion atau minyak di kulit sebelum pemeriksaan.', 'Informasikan semua obat yang sedang dikonsumsi kepada dokter.', 'Tidak perlu berpuasa sebelum pemeriksaan.'],
                'biaya_min' => 150000, 'biaya_max' => 350000, 'durasi' => '15–30 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'ekokardiografi', 'nama' => 'Ekokardiografi', 'kategori' => 'Jantung',
                'icon' => 'fa-heart-pulse', 'bg_color' => '#fdf0f0', 'icon_color' => '#e05252',
                'singkat' => 'USG jantung untuk melihat struktur dan fungsi otot serta katup jantung.',
                'deskripsi' => 'Ekokardiografi menggunakan gelombang ultrasonik untuk menghasilkan gambar real-time jantung. Pemeriksaan ini menilai fungsi pompa jantung, kondisi katup, dan mendeteksi adanya cairan di sekitar jantung.',
                'persiapan' => ['Tidak diperlukan persiapan khusus untuk ekokardiografi transthorakal.', 'Untuk ekokardiografi transesofageal, puasa 6 jam sebelum pemeriksaan.', 'Lepaskan perhiasan di area dada.', 'Kenakan pakaian yang nyaman dan longgar.'],
                'biaya_min' => 400000, 'biaya_max' => 900000, 'durasi' => '30–60 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'fisik-umum', 'nama' => 'Pemeriksaan Fisik Umum', 'kategori' => 'Umum',
                'icon' => 'fa-stethoscope', 'bg_color' => '#e8f0f5', 'icon_color' => '#3a7abf',
                'singkat' => 'Pengecekan kondisi fisik dasar termasuk tekanan darah, detak jantung, dan paru-respirasi.',
                'deskripsi' => 'Pemeriksaan fisik umum adalah evaluasi menyeluruh kondisi tubuh yang dilakukan oleh dokter umum. Mencakup pengukuran tanda vital (tekanan darah, suhu, nadi, pernapasan), pemeriksaan organ utama, dan penilaian kondisi umum pasien.',
                'persiapan' => ['Puasa ringan 2–4 jam sebelum jika disertai tes darah.', 'Bawa riwayat kesehatan dan daftar obat yang dikonsumsi.', 'Kenakan pakaian yang nyaman dan mudah dilepas.', 'Catat keluhan atau gejala yang dirasakan sebelum ke dokter.'],
                'biaya_min' => 75000, 'biaya_max' => 200000, 'durasi' => '20–45 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'scaling', 'nama' => 'Scaling & Pembersihan Karang', 'kategori' => 'Gigi',
                'icon' => 'fa-tooth', 'bg_color' => '#f5f0e8', 'icon_color' => '#bf8a3a',
                'singkat' => 'Pembersihan karang gigi profesional untuk mencegah penyakit gusi dan gigi berlubang.',
                'deskripsi' => 'Scaling gigi adalah prosedur pembersihan plak dan karang gigi (tartar) yang menumpuk di atas dan bawah garis gusi menggunakan alat ultrasonik dan instrumen khusus.',
                'persiapan' => ['Sikat gigi sebelum datang ke klinik.', 'Informasikan kondisi medis seperti diabetes atau pengencer darah.', 'Hindari merokok 24 jam sebelum prosedur.', 'Konsumsi obat pereda nyeri jika sensitif terhadap rasa sakit.'],
                'biaya_min' => 200000, 'biaya_max' => 500000, 'durasi' => '30–60 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'endoskopi-tht', 'nama' => 'Endoskopi THT', 'kategori' => 'THT',
                'icon' => 'fa-ear-deaf', 'bg_color' => '#f0e8f5', 'icon_color' => '#7a3abf',
                'singkat' => 'Pemeriksaan bagian dalam telinga, hidung, dan tenggorokan menggunakan kamera kecil.',
                'deskripsi' => 'Endoskopi THT menggunakan kamera serat optik fleksibel untuk memeriksa rongga hidung, faring, dan laring secara langsung. Membantu diagnosis polip, infeksi kronis, tumor, dan gangguan suara.',
                'persiapan' => ['Puasa 4–6 jam sebelum pemeriksaan jika menggunakan anestesi.', 'Hindari penggunaan semprotan hidung 24 jam sebelumnya.', 'Informasikan riwayat alergi obat kepada dokter.', 'Lepaskan gigi palsu jika ada.'],
                'biaya_min' => 300000, 'biaya_max' => 700000, 'durasi' => '15–30 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'visus-mata', 'nama' => 'Cek Visus Mata', 'kategori' => 'Mata',
                'icon' => 'fa-eye', 'bg_color' => '#e8f5f0', 'icon_color' => '#2d9e72',
                'singkat' => 'Pemeriksaan ketajaman penglihatan untuk menentukan kebutuhan kacamata atau lensa kontak.',
                'deskripsi' => 'Pemeriksaan visus mengukur ketajaman penglihatan menggunakan kartu Snellen atau chart digital. Hasilnya digunakan untuk menentukan apakah pasien memerlukan kacamata, lensa kontak, atau tindakan lebih lanjut.',
                'persiapan' => ['Bawa kacamata atau lensa kontak yang sudah ada jika punya.', 'Tidak perlu berpuasa.', 'Hindari membaca berlebihan 1–2 jam sebelum pemeriksaan agar mata tidak lelah.', 'Jika menggunakan lensa kontak, lepas 24 jam sebelum jika diperlukan pemeriksaan refraksi.'],
                'biaya_min' => 50000, 'biaya_max' => 150000, 'durasi' => '15–30 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'glaukoma', 'nama' => 'Pemeriksaan Glaukoma', 'kategori' => 'Mata',
                'icon' => 'fa-eye-slash', 'bg_color' => '#f0f5e8', 'icon_color' => '#6a9e2d',
                'singkat' => 'Pengukuran tekanan bola mata untuk pencegahan kebutaan akibat glaukoma.',
                'deskripsi' => 'Pemeriksaan glaukoma meliputi tonometri (pengukuran tekanan intraokular), pemeriksaan saraf optik, dan tes lapang pandang. Glaukoma sering tidak bergejala hingga tahap lanjut sehingga deteksi dini sangat penting.',
                'persiapan' => ['Hindari kafein 2 jam sebelum pemeriksaan tonometri.', 'Lepaskan lensa kontak sebelum pemeriksaan.', 'Informasikan riwayat penggunaan obat tetes mata.', 'Pupil mungkin akan dilebarkan, siapkan kacamata hitam untuk perjalanan pulang.'],
                'biaya_min' => 100000, 'biaya_max' => 300000, 'durasi' => '20–40 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'darah-lengkap', 'nama' => 'Tes Darah Lengkap', 'kategori' => 'Laboratorium',
                'icon' => 'fa-vial', 'bg_color' => '#fdf0f0', 'icon_color' => '#e05252',
                'singkat' => 'Analisis komponen darah untuk mendeteksi berbagai kondisi medis dari anemia hingga infeksi.',
                'deskripsi' => 'Complete Blood Count (CBC) menganalisis sel darah merah, sel darah putih, hemoglobin, hematokrit, dan trombosit. Hasilnya membantu diagnosis anemia, infeksi, gangguan pembekuan darah, leukemia, dan kondisi lainnya.',
                'persiapan' => ['Puasa 8–12 jam sebelum pengambilan sampel (hanya minum air putih).', 'Hindari olahraga berat 24 jam sebelumnya.', 'Informasikan obat-obatan yang sedang dikonsumsi.', 'Kenakan baju lengan pendek atau yang mudah digulung.'],
                'biaya_min' => 80000, 'biaya_max' => 200000, 'durasi' => '10–15 menit', 'status' => 'nonaktif',
            ],
            [
                'slug' => 'gula-kolesterol', 'nama' => 'Cek Gula Darah & Kolesterol', 'kategori' => 'Laboratorium',
                'icon' => 'fa-flask', 'bg_color' => '#fdf5e8', 'icon_color' => '#bf8a3a',
                'singkat' => 'Skrining rutin untuk mendeteksi risiko diabetes dan penyakit kardiovaskular.',
                'deskripsi' => 'Pemeriksaan ini mengukur kadar glukosa darah puasa dan profil lipid (kolesterol total, LDL, HDL, trigliserida). Merupakan skrining esensial untuk deteksi dini diabetes melitus tipe 2 dan risiko penyakit jantung koroner.',
                'persiapan' => ['Puasa 10–12 jam sebelum pemeriksaan.', 'Hanya boleh minum air putih selama puasa.', 'Hindari olahraga berat malam sebelum pemeriksaan.', 'Tetap konsumsi obat rutin kecuali diinstruksikan sebaliknya oleh dokter.'],
                'biaya_min' => 60000, 'biaya_max' => 180000, 'durasi' => '10–15 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'dermatologi', 'nama' => 'Konsultasi Dermatologi', 'kategori' => 'Kulit',
                'icon' => 'fa-hand-dots', 'bg_color' => '#e8f0f5', 'icon_color' => '#3a7abf',
                'singkat' => 'Pemeriksaan spesialis untuk masalah kulit, rambut, dan kuku.',
                'deskripsi' => 'Konsultasi dengan dokter spesialis kulit dan kelamin untuk mendiagnosis dan menangani berbagai kondisi dermatologis seperti jerawat, eksim, psoriasis, infeksi jamur, alopecia, dan deteksi dini kanker kulit melalui dermoskopi.',
                'persiapan' => ['Bersihkan area kulit yang bermasalah, jangan tutup dengan makeup.', 'Dokumentasikan kapan gejala pertama muncul dan perkembangannya.', 'Bawa produk perawatan kulit yang biasa digunakan.', 'Informasikan riwayat alergi dan obat yang sedang dikonsumsi.'],
                'biaya_min' => 150000, 'biaya_max' => 400000, 'durasi' => '20–45 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'usg-abdomen', 'nama' => 'USG Abdomen', 'kategori' => 'Radiologi',
                'icon' => 'fa-x-ray', 'bg_color' => '#f0e8f5', 'icon_color' => '#7a3abf',
                'singkat' => 'Pencitraan organ perut menggunakan ultrasonografi untuk deteksi gangguan hati, ginjal, dan kandung empedu.',
                'deskripsi' => 'USG abdomen menggunakan gelombang suara frekuensi tinggi untuk memvisualisasikan organ dalam perut termasuk hati, limpa, pankreas, ginjal, kandung empedu, dan aorta.',
                'persiapan' => ['Puasa 6–8 jam sebelum pemeriksaan.', 'Minum air putih 1 liter dan tahan kencing 1 jam sebelum (untuk USG pelvis).', 'Hindari makanan pembentuk gas seperti sayuran mentah dan minuman bersoda.', 'Kenakan pakaian yang mudah diangkat di bagian perut.'],
                'biaya_min' => 200000, 'biaya_max' => 500000, 'durasi' => '20–30 menit', 'status' => 'aktif',
            ],
            [
                'slug' => 'rontgen', 'nama' => 'Rontgen / X-Ray', 'kategori' => 'Radiologi',
                'icon' => 'fa-lungs', 'bg_color' => '#e8f5f0', 'icon_color' => '#2d9e72',
                'singkat' => 'Pencitraan menggunakan sinar-X untuk memeriksa tulang, paru-paru, dan organ dada.',
                'deskripsi' => 'Pemeriksaan rontgen menggunakan radiasi sinar-X untuk menghasilkan gambar struktur internal tubuh. Digunakan untuk mendeteksi patah tulang, infeksi paru-paru, pneumonia, TBC, dan kelainan jantung-paru.',
                'persiapan' => ['Lepaskan perhiasan, jam tangan, dan benda logam lainnya.', 'Informasikan jika sedang hamil atau kemungkinan hamil.', 'Tidak diperlukan puasa untuk rontgen biasa.', 'Kenakan pakaian tanpa aksesoris logam.'],
                'biaya_min' => 100000, 'biaya_max' => 300000, 'durasi' => '10–15 menit', 'status' => 'aktif',
            ],
        ];

        foreach ($items as $item) {
            KatalogPemeriksaan::firstOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
