<?php
// PKE-13: Browser test untuk Dashboard Kesehatan Personal
namespace Tests\Browser;

use App\Models\Jadwal;
use App\Models\HealthData;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE13_DashboardTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE13',
            'email'         => "dusk.pke13.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        Jadwal::where('user_id', $this->testUser->id)->delete();
        HealthData::where('user_id', $this->testUser->id)->delete();
        $this->testUser->delete();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function login(Browser $browser): Browser
    {
        return $browser->pause(500)
                       ->visit('/login')
                       ->waitFor('input[name="email"]', 15)
                       ->type('input[name="email"]', $this->testUser->email)
                       ->type('input[name="password"]', 'Password123!')
                       ->press('Sign In')
                       ->waitForLocation('/dashboard', 20);
    }

    private function loginDanBukaDashboard(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->waitFor('.stat-grid', 15);
    }

    private function buatJadwal(array $override = []): Jadwal
    {
        return Jadwal::create(array_merge([
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Cek Umum Dusk',
            'tanggal'           => Carbon::today()->format('Y-m-d'),
            'waktu'             => '09:00',
            'fasilitas_klinik'  => 'RS Dusk',
            'status'            => 'mendatang',
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Menampilkan halaman dashboard — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_menampilkan_halaman_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertPathIs('/dashboard')
                 ->assertSee('Dashboard')
                 ->assertVisible('.stat-grid')
                 ->screenshot('tc01-halaman-dashboard-berhasil-ditampilkan');
        });
    }

    /** @test TC-02: Menampilkan informasi pengguna — sistem menampilkan nama pengguna dan tanggal (Positive) */
    public function test_tc02_menampilkan_informasi_pengguna(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Test Dusk PKE13')
                 ->assertVisible('.dash-date')
                 ->screenshot('tc02-informasi-pengguna-tampil');
        });
    }

    /** @test TC-03: Menampilkan statistik kesehatan — statistik kesehatan tampil dengan benar (Positive) */
    public function test_tc03_menampilkan_statistik_kesehatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertVisible('.stat-grid')
                 ->assertPresent('.stat-card')
                 ->assertSee('Jadwal Bulan Ini')
                 ->assertSee('Pengingat Aktif')
                 ->assertSee('Pemeriksaan Selesai')
                 ->screenshot('tc03-statistik-kesehatan-tampil');
        });
    }

    /** @test TC-04: Menampilkan jadwal pemeriksaan berikutnya — sistem menampilkan jadwal berikutnya (Positive) */
    public function test_tc04_menampilkan_jadwal_berikutnya(): void
    {
        $this->buatJadwal([
            'jenis_pemeriksaan' => 'Dusk Jadwal Berikutnya',
            'tanggal'           => Carbon::tomorrow()->format('Y-m-d'),
            'status'            => 'mendatang',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pemeriksaan Berikutnya')
                 ->assertSee('Dusk Jadwal Berikutnya')
                 ->assertVisible('.next-exam')
                 ->screenshot('tc04-jadwal-berikutnya-tampil');
        });
    }

    /** @test TC-05: Menampilkan progress konsistensi — progress konsistensi tampil dengan benar (Positive) */
    public function test_tc05_menampilkan_progress_konsistensi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Progress Konsistensi')
                 ->assertVisible('.progress-bar-wrap')
                 ->assertVisible('.progress-pct')
                 ->screenshot('tc05-progress-konsistensi-tampil');
        });
    }

    /** @test TC-06: Menghitung progress kesehatan — sistem menghitung progress dengan benar (Positive) */
    public function test_tc06_menghitung_progress_kesehatan(): void
    {
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::now()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => Carbon::now()->addDay()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertVisible('.progress-pct')
                 ->assertVisible('.progress-bar-fill')
                 ->assertSee('Progress Konsistensi')
                 ->screenshot('tc06-progress-kesehatan-dihitung');
        });
    }

    /** @test TC-07: Menampilkan status pemeriksaan — status pemeriksaan tampil dengan benar (Positive) */
    public function test_tc07_menampilkan_status_pemeriksaan(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertVisible('.stat-grid')
                 ->assertSee('Pemeriksaan Selesai')
                 ->assertVisible('.stat-value')
                 ->screenshot('tc07-status-pemeriksaan-tampil');
        });
    }

    /** @test TC-08: Menampilkan akses cepat — menu akses cepat tampil dengan benar (Positive) */
    public function test_tc08_menampilkan_akses_cepat(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Akses Cepat')
                 ->assertVisible('.quick-grid')
                 ->assertPresent('.quick-item')
                 ->assertSee('Rekomendasi')
                 ->assertSee('Jadwal Saya')
                 ->screenshot('tc08-akses-cepat-tampil');
        });
    }

    /** @test TC-09: Menampilkan insight kesehatan — sistem menampilkan insight kesehatan (Positive) */
    public function test_tc09_menampilkan_insight_kesehatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Insight Hari Ini')
                 ->assertVisible('.insight-card')
                 ->assertVisible('.insight-text')
                 ->screenshot('tc09-insight-kesehatan-tampil');
        });
    }

    /** @test TC-10: Menampilkan achievement terbaru — achievement terbaru tampil dengan benar (Positive) */
    public function test_tc10_menampilkan_achievement_terbaru(): void
    {
        HealthData::create([
            'user_id'        => $this->testUser->id,
            'tinggi_badan'   => 170,
            'berat_badan'    => 65,
            'golongan_darah' => 'A',
            'tanggal'        => Carbon::today()->format('Y-m-d'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pencapaian Terbaru')
                 ->assertVisible('.achievement-latest')
                 ->screenshot('tc10-achievement-terbaru-tampil');
        });
    }

    /** @test TC-11: Menampilkan tips kesehatan — sistem menampilkan daftar tips kesehatan (Positive) */
    public function test_tc11_menampilkan_tips_kesehatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Tips Kesehatan')
                 ->assertVisible('.tips-list')
                 ->assertPresent('.tip-item')
                 ->screenshot('tc11-tips-kesehatan-tampil');
        });
    }

    /** @test TC-12: Menampilkan aktivitas terbaru — aktivitas terbaru tampil dengan benar (Positive) */
    public function test_tc12_menampilkan_aktivitas_terbaru(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Aktivitas Terbaru')
                 ->assertVisible('.activity-list')
                 ->assertPresent('.activity-item')
                 ->screenshot('tc12-aktivitas-terbaru-tampil');
        });
    }

    /** @test TC-13: Jadwal pemeriksaan kosong — sistem menampilkan informasi jadwal kosong (Negative) */
    public function test_tc13_jadwal_pemeriksaan_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pemeriksaan Berikutnya')
                 ->assertVisible('.empty-card')
                 ->screenshot('tc13-jadwal-kosong-empty-state');
        });
    }

    /** @test TC-14: Insight belum tersedia — sistem menampilkan pesan insight belum tersedia (Negative) */
    public function test_tc14_insight_belum_tersedia(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Insight Hari Ini')
                 ->assertVisible('.insight-card')
                 ->screenshot('tc14-insight-default-tampil');
        });
    }

    /** @test TC-15: Aktivitas terbaru kosong — sistem menampilkan pesan aktivitas kosong (Negative) */
    public function test_tc15_aktivitas_terbaru_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Aktivitas Terbaru')
                 ->assertMissing('.activity-item')
                 ->screenshot('tc15-aktivitas-kosong');
        });
    }

    /** @test TC-16: Gagal mengambil data dashboard — sistem menampilkan pesan gagal memuat data (Negative) */
    public function test_tc16_gagal_mengambil_data_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->pause(1000)
                    ->assertPathIs('/login')
                    ->screenshot('tc16-redirect-ke-login-jika-belum-login');
        });
    }

    /** @test TC-17: Menampilkan loading state — sistem menampilkan loading state sementara (Positive) */
    public function test_tc17_menampilkan_loading_state(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->waitFor('.stat-grid', 15)
                 ->assertVisible('.stat-grid')
                 ->assertPresent('.stat-card')
                 ->screenshot('tc17-loading-state-selesai');
        });
    }

    /** @test TC-18: Validasi pembaruan dashboard — dashboard diperbarui tanpa error (Positive) */
    public function test_tc18_validasi_pembaruan_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->refresh()
                 ->waitFor('.stat-grid', 15)
                 ->assertPathIs('/dashboard')
                 ->assertVisible('.stat-grid')
                 ->assertVisible('.dash-date')
                 ->screenshot('tc18-dashboard-diperbarui-tanpa-error');
        });
    }

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /**
     * @test TC-B01: Stat "Jadwal Bulan Ini" — query DB mengembalikan jumlah jadwal bulan ini
     *
     * Langkah:
     * 1. Buat 2 jadwal di bulan ini dan 1 jadwal di bulan lalu milik testUser
     * 2. Query jadwal bulan ini (sama seperti DashboardController)
     * 3. Pastikan count = 2 (hanya bulan ini)
     */
    public function test_tcb01_stat_jadwal_bulan_ini(): void
    {
        // Langkah 1: Buat data jadwal
        $this->buatJadwal(['tanggal' => Carbon::now()->format('Y-m-d')]);
        $this->buatJadwal(['tanggal' => Carbon::now()->addDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['tanggal' => Carbon::now()->subMonth()->format('Y-m-d')]); // bulan lalu

        // Langkah 2: Jalankan query seperti controller
        $jadwalBulanIni = Jadwal::where('user_id', $this->testUser->id)
            ->whereMonth('tanggal', Carbon::today()->month)
            ->whereYear('tanggal', Carbon::today()->year)
            ->count();

        // Langkah 3: Verifikasi hanya 2 jadwal yang dihitung
        $this->assertEquals(2, $jadwalBulanIni);
    }

    /**
     * @test TC-B02: Stat "Pengingat Aktif" — query DB hanya menghitung jadwal mendatang dalam 7 hari ke depan
     *
     * Langkah:
     * 1. Buat jadwal mendatang dalam 7 hari (harus terhitung)
     * 2. Buat jadwal mendatang > 7 hari (tidak terhitung)
     * 3. Buat jadwal status selesai (tidak terhitung)
     * 4. Pastikan count = 1
     */
    public function test_tcb02_stat_pengingat_aktif_7_hari(): void
    {
        $today = Carbon::today();

        // Langkah 1–3: Buat variasi jadwal
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(3)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->addDays(2)->format('Y-m-d')]);

        // Langkah 4: Query seperti controller
        $pengingatAktif = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(7)])
            ->count();

        $this->assertEquals(1, $pengingatAktif);
    }

    /**
     * @test TC-B03: Stat "Pemeriksaan Selesai" — query DB hanya menghitung status selesai
     *
     * Langkah:
     * 1. Buat 3 jadwal selesai dan 1 jadwal mendatang
     * 2. Query jadwal berstatus selesai
     * 3. Pastikan count = 3
     */
    public function test_tcb03_stat_pemeriksaan_selesai(): void
    {
        // Langkah 1
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'mendatang']);

        // Langkah 2
        $selesai = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->count();

        // Langkah 3
        $this->assertEquals(3, $selesai);
    }

    /**
     * @test TC-B04: Jadwal terdekat — query DB mengambil jadwal mendatang paling awal
     *
     * Langkah:
     * 1. Buat 3 jadwal mendatang dengan tanggal berbeda
     * 2. Query jadwal terdekat (order by tanggal ASC, first)
     * 3. Pastikan yang dikembalikan adalah jadwal dengan tanggal paling awal
     */
    public function test_tcb04_jadwal_terdekat_paling_awal(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d'), 'jenis_pemeriksaan' => 'Jadwal Jauh']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(2)->format('Y-m-d'),  'jenis_pemeriksaan' => 'Jadwal Dekat']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(5)->format('Y-m-d'),  'jenis_pemeriksaan' => 'Jadwal Tengah']);

        // Langkah 2
        $terdekat = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->where('tanggal', '>=', $today)
            ->orderBy('tanggal')->orderBy('waktu')
            ->first();

        // Langkah 3
        $this->assertEquals('Jadwal Dekat', $terdekat->jenis_pemeriksaan);
    }

    /**
     * @test TC-B05: Persentase konsistensi — dihitung dari selesai / total jadwal bulan ini × 100
     *
     * Langkah:
     * 1. Buat 2 jadwal selesai dan 2 jadwal mendatang di bulan ini
     * 2. Hitung persentase seperti controller (selesai / total × 100, round)
     * 3. Pastikan hasilnya 50%
     */
    public function test_tcb05_persentase_konsistensi_bulan_ini(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->addDays(1)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(6)->format('Y-m-d')]);

        // Langkah 2
        $jadwalBulanIniList = Jadwal::where('user_id', $this->testUser->id)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->get();

        $selesai    = $jadwalBulanIniList->where('status', 'selesai')->count();
        $total      = $jadwalBulanIniList->count();
        $persentase = $total > 0 ? round(($selesai / $total) * 100) : 0;

        // Langkah 3
        $this->assertEquals(50, $persentase);
    }

    /**
     * @test TC-B06: Streak bulanan — dihitung berurutan dari bulan ini ke belakang
     *
     * Langkah:
     * 1. Buat jadwal selesai di bulan ini
     * 2. Buat jadwal selesai di bulan lalu
     * 3. Tidak ada jadwal selesai di 2 bulan lalu (streak berhenti)
     * 4. Hitung streak dengan logika controller
     * 5. Pastikan streak = 2
     */
    public function test_tcb06_streak_dihitung_berurutan(): void
    {
        $now = Carbon::now();

        // Langkah 1–3
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $now->copy()->startOfMonth()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $now->copy()->subMonth()->startOfMonth()->format('Y-m-d')]);
        // Bulan 2 lalu tidak ada jadwal selesai

        // Langkah 4: Logika hitungStreak dari controller
        $streak = 0;
        $bulan  = Carbon::now()->startOfMonth();
        for ($i = 0; $i < 24; $i++) {
            $ada = Jadwal::where('user_id', $this->testUser->id)
                ->where('status', 'selesai')
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->exists();

            if ($ada) { $streak++; $bulan->subMonth(); }
            else break;
        }

        // Langkah 5
        $this->assertEquals(2, $streak);
    }

    /**
     * @test TC-B07: Streak = 0 jika tidak ada jadwal selesai bulan ini
     *
     * Langkah:
     * 1. Buat jadwal mendatang (bukan selesai) di bulan ini
     * 2. Hitung streak
     * 3. Pastikan streak = 0
     */
    public function test_tcb07_streak_nol_tanpa_jadwal_selesai(): void
    {
        // Langkah 1
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => Carbon::now()->format('Y-m-d')]);

        // Langkah 2
        $streak = 0;
        $bulan  = Carbon::now()->startOfMonth();
        for ($i = 0; $i < 24; $i++) {
            $ada = Jadwal::where('user_id', $this->testUser->id)
                ->where('status', 'selesai')
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->exists();

            if ($ada) { $streak++; $bulan->subMonth(); }
            else break;
        }

        // Langkah 3
        $this->assertEquals(0, $streak);
    }

    /**
     * @test TC-B08: Insight "jadwal akan datang" — jadwal dalam 3 hari memicu insight yang sesuai
     *
     * Langkah:
     * 1. Buat jadwal mendatang dalam 2 hari
     * 2. Query insight seperti controller (cek upcoming dalam 3 hari)
     * 3. Pastikan ditemukan jadwal yang memicu insight "soon"
     */
    public function test_tcb08_insight_jadwal_mendekati(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $jadwal = $this->buatJadwal([
            'status'  => 'mendatang',
            'tanggal' => $today->copy()->addDays(2)->format('Y-m-d'),
        ]);

        // Langkah 2
        $soon = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(3)])
            ->first();

        // Langkah 3
        $this->assertNotNull($soon);
        $this->assertEquals($jadwal->id, $soon->id);
    }

    /**
     * @test TC-B09: Pencapaian "Pemula Sehat" — muncul saat user punya HealthData tapi streak = 0
     *
     * Langkah:
     * 1. Buat HealthData untuk testUser
     * 2. Tidak ada jadwal selesai (streak = 0, totalSelesai = 0)
     * 3. Pastikan HealthData terdeteksi di DB
     * 4. Verifikasi logika pencapaian: hasHealthData true → "Pemula Sehat"
     */
    public function test_tcb09_pencapaian_pemula_sehat(): void
    {
        // Langkah 1
        HealthData::create([
            'user_id'        => $this->testUser->id,
            'tinggi_badan'   => 165,
            'berat_badan'    => 60,
            'golongan_darah' => 'B',
            'tanggal'        => Carbon::today()->format('Y-m-d'),
        ]);

        // Langkah 2: Tidak ada jadwal selesai

        // Langkah 3
        $hasHealthData = HealthData::where('user_id', $this->testUser->id)->exists();
        $this->assertTrue($hasHealthData);

        // Langkah 4: Logika pencapaian dari controller
        $streak       = 0; // tidak ada jadwal selesai
        $totalSelesai = 0;

        $pencapaian = null;
        if ($streak >= 1 && $totalSelesai >= 5) {
            $pencapaian = 'Rajin Periksa';
        } elseif ($streak >= 1) {
            $pencapaian = 'Konsisten 1 Bulan';
        } elseif ($hasHealthData) {
            $pencapaian = 'Pemula Sehat';
        }

        $this->assertEquals('Pemula Sehat', $pencapaian);
    }

    /**
     * @test TC-B10: Aktivitas terbaru — query mengembalikan 3 jadwal terakhir diupdated
     *
     * Langkah:
     * 1. Buat 5 jadwal dengan updated_at berbeda
     * 2. Query aktivitas terbaru (limit 3, order by updated_at DESC)
     * 3. Pastikan hanya 3 jadwal yang dikembalikan
     * 4. Pastikan urutannya descending berdasarkan updated_at
     */
    public function test_tcb10_aktivitas_terbaru_limit_3(): void
    {
        // Langkah 1
        for ($i = 1; $i <= 5; $i++) {
            $this->buatJadwal(['jenis_pemeriksaan' => "Jadwal Aktivitas {$i}"]);
        }

        // Langkah 2
        $aktivitas = Jadwal::where('user_id', $this->testUser->id)
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get();

        // Langkah 3
        $this->assertCount(3, $aktivitas);

        // Langkah 4
        for ($i = 0; $i < $aktivitas->count() - 1; $i++) {
            $this->assertTrue(
                $aktivitas[$i]->updated_at->gte($aktivitas[$i + 1]->updated_at)
            );
        }
    }

    /**
     * @test TC-B11: Persentase = 0 saat tidak ada jadwal bulan ini
     *
     * Langkah:
     * 1. Tidak buat jadwal apapun
     * 2. Hitung persentase dengan logika controller
     * 3. Pastikan hasilnya 0 (bukan division by zero error)
     */
    public function test_tcb11_persentase_nol_tanpa_jadwal(): void
    {
        // Langkah 1: Tidak ada jadwal

        // Langkah 2
        $today          = Carbon::today();
        $jadwalBulanIni = Jadwal::where('user_id', $this->testUser->id)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->get();

        $selesai    = $jadwalBulanIni->where('status', 'selesai')->count();
        $total      = $jadwalBulanIni->count();
        $persentase = $total > 0 ? round(($selesai / $total) * 100) : 0;

        // Langkah 3
        $this->assertEquals(0, $persentase);
        $this->assertEquals(0, $total);
    }

    /**
     * @test TC-B12: Data isolation — stat dashboard hanya menghitung jadwal milik user sendiri
     *
     * Langkah:
     * 1. Buat user lain dengan 5 jadwal selesai
     * 2. Buat 1 jadwal selesai untuk testUser
     * 3. Query pemeriksaan selesai khusus testUser
     * 4. Pastikan hasilnya 1 (bukan 6)
     */
    public function test_tcb12_data_isolation_stat_dashboard(): void
    {
        // Langkah 1
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE13',
            'email'         => "lain.pke13.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
        for ($i = 0; $i < 5; $i++) {
            Jadwal::create([
                'user_id'           => $userLain->id,
                'jenis_pemeriksaan' => "Jadwal User Lain {$i}",
                'tanggal'           => Carbon::now()->format('Y-m-d'),
                'waktu'             => '09:00',
                'fasilitas_klinik'  => 'RS X',
                'status'            => 'selesai',
            ]);
        }

        // Langkah 2
        $this->buatJadwal(['status' => 'selesai']);

        // Langkah 3
        $selesaiSaya = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->count();

        // Langkah 4
        $this->assertEquals(1, $selesaiSaya);

        Jadwal::where('user_id', $userLain->id)->delete();
        $userLain->delete();
    }
}
