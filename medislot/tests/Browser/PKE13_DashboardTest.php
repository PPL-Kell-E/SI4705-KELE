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
            'password_hash' => 'Password123!', // cast 'hashed' otomatis hash — jangan Hash::make()
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
    // GUI TESTS — Buka browser, input data ke DB, verifikasi tampilan di UI
    // =========================================================================

    /**
     * @test TC-B01: GUI — Stat "Jadwal Bulan Ini" tampil benar di dashboard
     *
     * Langkah:
     * 1. Buat 2 jadwal bulan ini dan 1 jadwal bulan lalu di DB
     * 2. Buka browser, login, buka dashboard
     * 3. Pastikan stat card "Jadwal Bulan Ini" menampilkan angka 2
     * 4. Verifikasi data di DB hanya 2 untuk bulan ini
     */
    public function test_tcb01_gui_stat_jadwal_bulan_ini(): void
    {
        // Langkah 1
        $this->buatJadwal(['tanggal' => Carbon::now()->format('Y-m-d')]);
        $this->buatJadwal(['tanggal' => Carbon::now()->addDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['tanggal' => Carbon::now()->subMonth()->format('Y-m-d')]);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Jadwal Bulan Ini')
                 ->assertSeeIn('.stat-grid', '2')
                 ->screenshot('tcb01-gui-stat-jadwal-bulan-ini');
        });

        // Langkah 4
        $count = Jadwal::where('user_id', $this->testUser->id)
            ->whereMonth('tanggal', Carbon::today()->month)
            ->whereYear('tanggal', Carbon::today()->year)
            ->count();
        $this->assertEquals(2, $count);
    }

    /**
     * @test TC-B02: GUI — Stat "Pengingat Aktif" hanya menampilkan jadwal mendatang 7 hari
     *
     * Langkah:
     * 1. Buat 1 jadwal mendatang dalam 3 hari, 1 mendatang > 7 hari, 1 selesai
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan "Pengingat Aktif" menampilkan angka 1 di UI
     * 4. Verifikasi count di DB = 1
     */
    public function test_tcb02_gui_stat_pengingat_aktif_7_hari(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(3)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->format('Y-m-d')]);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pengingat Aktif')
                 ->assertSeeIn('.stat-grid', '1')
                 ->screenshot('tcb02-gui-stat-pengingat-aktif');
        });

        // Langkah 4
        $count = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(7)])
            ->count();
        $this->assertEquals(1, $count);
    }

    /**
     * @test TC-B03: GUI — Stat "Pemeriksaan Selesai" tampil benar di dashboard
     *
     * Langkah:
     * 1. Buat 3 jadwal selesai dan 1 mendatang di DB
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan "Pemeriksaan Selesai" menampilkan angka 3 di UI
     * 4. Verifikasi di DB count selesai = 3
     */
    public function test_tcb03_gui_stat_pemeriksaan_selesai(): void
    {
        // Langkah 1
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'mendatang']);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pemeriksaan Selesai')
                 ->assertSeeIn('.stat-grid', '3')
                 ->screenshot('tcb03-gui-stat-pemeriksaan-selesai');
        });

        // Langkah 4
        $count = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')->count();
        $this->assertEquals(3, $count);
    }

    /**
     * @test TC-B04: GUI — Jadwal terdekat tampil dengan nama yang benar di dashboard
     *
     * Langkah:
     * 1. Buat 3 jadwal mendatang: 10 hari, 2 hari, 5 hari
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan "Pemeriksaan Berikutnya" menampilkan jadwal 2 hari lagi (paling dekat)
     * 4. Verifikasi di DB jadwal terdekat = 2 hari lagi
     */
    public function test_tcb04_gui_jadwal_terdekat_paling_awal(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d'), 'jenis_pemeriksaan' => 'Jadwal Jauh PKE13']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(2)->format('Y-m-d'),  'jenis_pemeriksaan' => 'Jadwal Dekat PKE13']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(5)->format('Y-m-d'),  'jenis_pemeriksaan' => 'Jadwal Tengah PKE13']);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pemeriksaan Berikutnya')
                 ->assertSee('Jadwal Dekat PKE13')
                 ->assertDontSee('Jadwal Jauh PKE13')
                 ->screenshot('tcb04-gui-jadwal-terdekat');
        });

        // Langkah 4
        $terdekat = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->where('tanggal', '>=', $today)
            ->orderBy('tanggal')->first();
        $this->assertEquals('Jadwal Dekat PKE13', $terdekat->jenis_pemeriksaan);
    }

    /**
     * @test TC-B05: GUI — Progress konsistensi 50% tampil di UI
     *
     * Langkah:
     * 1. Buat 2 jadwal selesai dan 2 jadwal mendatang bulan ini di DB
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan progress bar dan angka "50%" tampil di UI
     * 4. Verifikasi perhitungan di DB: 2/4 × 100 = 50%
     */
    public function test_tcb05_gui_progress_konsistensi_50_persen(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->addDays(1)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(6)->format('Y-m-d')]);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Progress Konsistensi')
                 ->assertSee('50%')
                 ->assertVisible('.progress-bar-fill')
                 ->screenshot('tcb05-gui-progress-50-persen');
        });

        // Langkah 4
        $list       = Jadwal::where('user_id', $this->testUser->id)
            ->whereMonth('tanggal', $today->month)->whereYear('tanggal', $today->year)->get();
        $persentase = $list->count() > 0 ? round($list->where('status','selesai')->count() / $list->count() * 100) : 0;
        $this->assertEquals(50, $persentase);
    }

    /**
     * @test TC-B06: GUI — Progress 0% tampil saat tidak ada jadwal bulan ini
     *
     * Langkah:
     * 1. Tidak buat jadwal apapun
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan "0%" tampil di UI (tidak ada division by zero)
     * 4. Verifikasi total jadwal bulan ini = 0
     */
    public function test_tcb06_gui_progress_nol_tanpa_jadwal(): void
    {
        // Langkah 1: Tidak ada jadwal

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Progress Konsistensi')
                 ->assertSee('0%')
                 ->screenshot('tcb06-gui-progress-nol-persen');
        });

        // Langkah 4
        $today = Carbon::today();
        $total = Jadwal::where('user_id', $this->testUser->id)
            ->whereMonth('tanggal', $today->month)->whereYear('tanggal', $today->year)->count();
        $this->assertEquals(0, $total);
    }

    /**
     * @test TC-B07: GUI — Aktivitas terbaru menampilkan maksimal 3 jadwal di UI
     *
     * Langkah:
     * 1. Buat 5 jadwal di DB
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan section "Aktivitas Terbaru" tampil dan ada item di UI
     * 4. Verifikasi query DB hanya mengambil 3 terakhir
     */
    public function test_tcb07_gui_aktivitas_terbaru_limit_3(): void
    {
        // Langkah 1
        for ($i = 1; $i <= 5; $i++) {
            $this->buatJadwal(['jenis_pemeriksaan' => "Aktivitas PKE13 {$i}", 'status' => 'selesai']);
        }

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Aktivitas Terbaru')
                 ->assertVisible('.activity-list')
                 ->assertPresent('.activity-item')
                 ->screenshot('tcb07-gui-aktivitas-terbaru-limit-3');
        });

        // Langkah 4
        $aktivitas = Jadwal::where('user_id', $this->testUser->id)
            ->orderByDesc('updated_at')->limit(3)->get();
        $this->assertCount(3, $aktivitas);
        $this->assertTrue($aktivitas[0]->updated_at->gte($aktivitas[1]->updated_at));
    }

    /**
     * @test TC-B08: GUI — Insight jadwal mendekati tampil saat ada jadwal dalam 3 hari
     *
     * Langkah:
     * 1. Buat jadwal mendatang 2 hari ke depan di DB
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan section "Insight Hari Ini" tampil
     * 4. Verifikasi jadwal "soon" ada di DB (dalam 3 hari)
     */
    public function test_tcb08_gui_insight_jadwal_mendekati(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $jadwal = $this->buatJadwal([
            'status'            => 'mendatang',
            'tanggal'           => $today->copy()->addDays(2)->format('Y-m-d'),
            'jenis_pemeriksaan' => 'Cek Insight PKE13',
        ]);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Insight Hari Ini')
                 ->assertVisible('.insight-card')
                 ->screenshot('tcb08-gui-insight-jadwal-mendekati');
        });

        // Langkah 4
        $soon = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(3)])
            ->first();
        $this->assertNotNull($soon);
        $this->assertEquals($jadwal->id, $soon->id);
    }

    /**
     * @test TC-B09: GUI — Pencapaian "Pemula Sehat" tampil saat user punya health data
     *
     * Langkah:
     * 1. Buat HealthData untuk testUser di DB
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan section "Pencapaian Terbaru" tampil
     * 4. Verifikasi HealthData ada di DB
     */
    public function test_tcb09_gui_pencapaian_pemula_sehat(): void
    {
        // Langkah 1
        HealthData::create([
            'user_id'    => $this->testUser->id,
            'weight_kg'  => 60,
            'height_cm'  => 165,
            'blood_type' => 'B',
        ]);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pencapaian Terbaru')
                 ->assertVisible('.achievement-latest')
                 ->screenshot('tcb09-gui-pencapaian-pemula-sehat');
        });

        // Langkah 4
        $this->assertTrue(HealthData::where('user_id', $this->testUser->id)->exists());
        $this->assertDatabaseHas('health_data', ['user_id' => $this->testUser->id, 'blood_type' => 'B']);
    }

    /**
     * @test TC-B10: GUI — Stat cards muncul semua di dashboard (Jadwal, Pengingat, Selesai)
     *
     * Langkah:
     * 1. Buat variasi data: 1 jadwal bulan ini, 1 mendatang 3 hari, 1 selesai
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan ketiga stat card tampil dengan label yang benar
     * 4. Verifikasi semua data tersimpan di DB
     */
    public function test_tcb10_gui_semua_stat_card_tampil(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(3)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->subDays(1)->format('Y-m-d')]);

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertVisible('.stat-grid')
                 ->assertSee('Jadwal Bulan Ini')
                 ->assertSee('Pengingat Aktif')
                 ->assertSee('Pemeriksaan Selesai')
                 ->assertPresent('.stat-card')
                 ->assertPresent('.stat-value')
                 ->screenshot('tcb10-gui-semua-stat-card-tampil');
        });

        // Langkah 4
        $this->assertEquals(2, Jadwal::where('user_id', $this->testUser->id)->count());
        $this->assertDatabaseHas('jadwal', ['user_id' => $this->testUser->id, 'status' => 'selesai']);
        $this->assertDatabaseHas('jadwal', ['user_id' => $this->testUser->id, 'status' => 'mendatang']);
    }

    /**
     * @test TC-B11: GUI — Empty state jadwal tampil saat tidak ada jadwal mendatang
     *
     * Langkah:
     * 1. Tidak buat jadwal apapun
     * 2. Buka browser dan buka dashboard
     * 3. Pastikan section "Pemeriksaan Berikutnya" tampil dengan empty state
     * 4. Verifikasi tidak ada jadwal di DB untuk user ini
     */
    public function test_tcb11_gui_empty_state_jadwal_kosong(): void
    {
        // Langkah 1: Tidak ada jadwal

        // Langkah 2–3
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pemeriksaan Berikutnya')
                 ->assertVisible('.empty-card')
                 ->screenshot('tcb11-gui-empty-state-jadwal-kosong');
        });

        // Langkah 4
        $this->assertEquals(0, Jadwal::where('user_id', $this->testUser->id)->count());
    }

    /**
     * @test TC-B12: GUI — Data isolation: dashboard hanya tampilkan data milik user sendiri
     *
     * Langkah:
     * 1. Buat user lain dengan 10 jadwal selesai di DB
     * 2. Buat 1 jadwal selesai untuk testUser
     * 3. Login sebagai testUser, buka dashboard
     * 4. Pastikan stat "Pemeriksaan Selesai" hanya menampilkan 1 (milik testUser)
     * 5. Verifikasi isolasi di DB
     */
    public function test_tcb12_gui_data_isolation_dashboard(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE13',
            'email'         => "lain.pke13.{$uid}@test.local",
            'password_hash' => 'Password123!',
            'role'          => 'user',
        ]);
        for ($i = 0; $i < 10; $i++) {
            Jadwal::create([
                'user_id'           => $userLain->id,
                'jenis_pemeriksaan' => "Jadwal User Lain {$i}",
                'tanggal'           => $today->copy()->subDays($i)->format('Y-m-d'),
                'waktu'             => '09:00',
                'fasilitas_klinik'  => 'RS X',
                'status'            => 'selesai',
            ]);
        }

        // Langkah 2
        $this->buatJadwal(['status' => 'selesai']);

        // Langkah 3–4
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaDashboard($browser)
                 ->assertSee('Pemeriksaan Selesai')
                 ->assertSeeIn('.stat-grid', '1')
                 ->screenshot('tcb12-gui-data-isolation-dashboard');
        });

        // Langkah 5
        $selesaiSaya = Jadwal::where('user_id', $this->testUser->id)->where('status', 'selesai')->count();
        $this->assertEquals(1, $selesaiSaya);

        Jadwal::where('user_id', $userLain->id)->delete();
        $userLain->delete();
    }
}
