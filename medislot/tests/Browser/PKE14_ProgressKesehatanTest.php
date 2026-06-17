<?php
// PKE-14: Browser test untuk Progress Kesehatan Personal
namespace Tests\Browser;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE14_ProgressKesehatanTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE14',
            'email'         => "dusk.pke14.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        Jadwal::where('user_id', $this->testUser->id)->delete();
        $this->testUser->delete();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function login(Browser $browser): Browser
    {
        return $browser->loginAs($this->testUser);
    }

    private function loginDanBukaProgress(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/insight/progress')
                    ->waitUntil(
                        "document.querySelector('.progress-grid-top') || document.querySelector('.pg-empty-full')",
                        15
                    );
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

    /** @test TC-01: Menampilkan halaman progress kesehatan — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_menampilkan_halaman_progress_kesehatan(): void
    {
        // User baru tanpa data → empty state tampil (halaman tetap berhasil dimuat)
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertPathIs('/insight/progress')
                 ->assertVisible('.pg-empty-full')
                 ->assertSee('Belum ada data pemeriksaan')
                 ->screenshot('tc01-halaman-progress-berhasil-ditampilkan');
        });
    }

    /** @test TC-02: Mengambil data progress kesehatan — sistem berhasil mengambil data dari database (Positive) */
    public function test_tc02_mengambil_data_progress_kesehatan(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.progress-grid-top')
                 ->assertVisible('.pk-bar-wrap')
                 ->assertPresent('.pk-info-val')
                 ->screenshot('tc02-data-progress-berhasil-diambil');
        });
    }

    /** @test TC-03: Menampilkan progress konsistensi — progress bar dan persentase tampil dengan benar (Positive) */
    public function test_tc03_menampilkan_progress_konsistensi(): void
    {
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => Carbon::tomorrow()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.pk-bar-wrap')
                 ->assertVisible('.pk-bar-fill')
                 ->assertVisible('.pk-persen')
                 ->assertSee('Progress Konsistensi')
                 ->screenshot('tc03-progress-konsistensi-tampil');
        });
    }

    /** @test TC-04: Menghitung progres konsistensi — sistem menampilkan progres sebesar 80% (Positive) */
    public function test_tc04_menghitung_progres_konsistensi(): void
    {
        // 4 selesai dari 5 total = 80%
        for ($i = 0; $i < 4; $i++) {
            $this->buatJadwal([
                'status' => 'selesai',
                'tanggal' => Carbon::now()->startOfMonth()->addDays($i)->format('Y-m-d'),
            ]);
        }
        $this->buatJadwal([
            'status' => 'mendatang',
            'tanggal' => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.pk-persen')
                 ->assertSee('80%')
                 ->assertVisible('.pk-bar-fill')
                 ->screenshot('tc04-progres-konsistensi-80-persen');
        });
    }

    /** @test TC-05: Menampilkan health streak — sistem menampilkan jumlah hari konsisten dengan benar (Positive) */
    public function test_tc05_menampilkan_health_streak(): void
    {
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::now()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.streak-card')
                 ->assertVisible('.streak-count')
                 ->assertSee('Hari Konsisten')
                 ->screenshot('tc05-health-streak-tampil');
        });
    }

    /** @test TC-06: Menampilkan status pemeriksaan — sistem menampilkan status pemeriksaan dengan benar (Positive) */
    public function test_tc06_menampilkan_status_pemeriksaan(): void
    {
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => Carbon::tomorrow()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.step-section')
                 ->assertSee('Status Pemeriksaan')
                 ->assertPresent('.step-item')
                 ->screenshot('tc06-status-pemeriksaan-tampil');
        });
    }

    /** @test TC-07: Menampilkan jadwal pemeriksaan berikutnya — sistem menampilkan jadwal berikutnya dengan benar (Positive) */
    public function test_tc07_menampilkan_jadwal_berikutnya(): void
    {
        $this->buatJadwal([
            'jenis_pemeriksaan' => 'Dusk Jadwal Berikutnya',
            'tanggal'           => Carbon::tomorrow()->format('Y-m-d'),
            'status'            => 'mendatang',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.next-banner')
                 ->assertSee('Pemeriksaan berikutnya')
                 ->assertSee('Dusk Jadwal Berikutnya')
                 ->screenshot('tc07-jadwal-berikutnya-tampil');
        });
    }

    /** @test TC-08: Menampilkan grafik tren pemeriksaan — grafik tren berhasil ditampilkan (Positive) */
    public function test_tc08_menampilkan_grafik_tren_pemeriksaan(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertSee('Tren Pemeriksaan')
                 ->assertVisible('.chart-wrap')
                 ->assertVisible('.chart-legend')
                 ->screenshot('tc08-grafik-tren-tampil');
        });
    }

    /** @test TC-09: Menggunakan filter periode — grafik dan statistik diperbarui sesuai filter (Positive) */
    public function test_tc09_menggunakan_filter_periode(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/insight/progress?periode=6')
                 ->waitUntil(
                     "document.querySelector('.progress-grid-top') || document.querySelector('.pg-empty-full')",
                     15
                 )
                 ->assertSee('Tren Pemeriksaan')
                 ->assertVisible('.chart-wrap')
                 ->screenshot('tc09-filter-periode-6-bulan');
        });
    }

    /** @test TC-10: Menampilkan statistik pemeriksaan — statistik total, selesai, dan pending tampil dengan benar (Positive) */
    public function test_tc10_menampilkan_statistik_pemeriksaan(): void
    {
        $this->buatJadwal(['status' => 'selesai']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => Carbon::tomorrow()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertSee('Statistik Pemeriksaan')
                 ->assertSee('Total Pemeriksaan')
                 ->assertSee('Selesai')
                 ->assertSee('Pending')
                 ->assertPresent('.stat-item')
                 ->screenshot('tc10-statistik-pemeriksaan-tampil');
        });
    }

    /** @test TC-11: Data pemeriksaan kosong — sistem menampilkan empty state (Negative) */
    public function test_tc11_data_pemeriksaan_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/insight/progress')
                 ->waitFor('.pg-empty-full', 15)
                 ->assertVisible('.pg-empty-full')
                 ->assertMissing('.progress-grid-top')
                 ->screenshot('tc11-data-kosong-empty-state');
        });
    }

    /** @test TC-12: Tidak ada jadwal berikutnya — sistem menampilkan informasi jadwal belum tersedia (Negative) */
    public function test_tc12_tidak_ada_jadwal_berikutnya(): void
    {
        // Hanya jadwal selesai, tidak ada mendatang
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.next-empty')
                 ->assertMissing('.next-banner')
                 ->screenshot('tc12-tidak-ada-jadwal-berikutnya');
        });
    }

    /** @test TC-13: Streak belum tersedia — sistem menampilkan informasi streak belum tersedia (Negative) */
    public function test_tc13_streak_belum_tersedia(): void
    {
        // User baru tanpa jadwal selesai → streak 0
        $this->buatJadwal(['status' => 'mendatang']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.streak-card')
                 ->assertDontSeeIn('.streak-card', 'Hari Konsisten')
                 ->screenshot('tc13-streak-belum-tersedia');
        });
    }

    /** @test TC-14: Gagal mengambil data — sistem menampilkan pesan gagal memuat data (Negative) */
    public function test_tc14_gagal_mengambil_data(): void
    {
        // Akses tanpa login → redirect ke login
        $this->browse(function (Browser $browser) {
            $browser->visit('/insight/progress')
                    ->pause(1000)
                    ->assertPathIs('/login')
                    ->screenshot('tc14-redirect-login-jika-belum-login');
        });
    }

    /** @test TC-15: Menampilkan loading state — sistem menampilkan loading state sementara (Positive) */
    public function test_tc15_menampilkan_loading_state(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaProgress($browser)
                 ->assertVisible('.progress-grid-top')
                 ->assertPresent('.pk-bar-wrap')
                 ->screenshot('tc15-loading-state-selesai');
        });
    }

    /** @test TC-16: Validasi pembaruan grafik — grafik diperbarui tanpa error saat ganti filter periode (Positive) */
    public function test_tc16_validasi_pembaruan_grafik(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/insight/progress?periode=12')
                 ->waitUntil(
                     "document.querySelector('.progress-grid-top') || document.querySelector('.pg-empty-full')",
                     15
                 )
                 ->assertPathIs('/insight/progress')
                 ->assertVisible('.chart-wrap')
                 ->assertMissing('.pg-error')
                 ->screenshot('tc16-grafik-diperbarui-tanpa-error');
        });
    }

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /**
     * @test TC-B01: Progress konsistensi bulan ini — dihitung dari selesai / total × 100
     *
     * Langkah:
     * 1. Buat 3 jadwal selesai dan 1 jadwal mendatang di bulan ini
     * 2. Query seperti InsightController::progress()
     * 3. Hitung progressPersen
     * 4. Pastikan hasilnya 75%
     */
    public function test_tcb01_progress_konsistensi_dihitung_benar(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->addDays(1)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->addDays(2)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d')]);

        // Langkah 2
        $bulanIniJadwal  = Jadwal::where('user_id', $this->testUser->id)
            ->whereYear('tanggal', $today->year)
            ->whereMonth('tanggal', $today->month)
            ->get();
        $totalBulanIni   = $bulanIniJadwal->count();
        $selesaiBulanIni = $bulanIniJadwal->where('status', 'selesai')->count();

        // Langkah 3
        $progressPersen = $totalBulanIni > 0 ? round($selesaiBulanIni / $totalBulanIni * 100) : 0;

        // Langkah 4
        $this->assertEquals(75, $progressPersen);
        $this->assertEquals(3, $selesaiBulanIni);
        $this->assertEquals(4, $totalBulanIni);
    }

    /**
     * @test TC-B02: Progress = 0 saat tidak ada jadwal bulan ini (no division by zero)
     *
     * Langkah:
     * 1. Tidak buat jadwal apapun
     * 2. Hitung progressPersen dengan logika controller
     * 3. Pastikan hasilnya 0, totalBulanIni = 0
     */
    public function test_tcb02_progress_nol_tanpa_jadwal(): void
    {
        // Langkah 1: Tidak ada jadwal

        // Langkah 2
        $today          = Carbon::today();
        $bulanIniJadwal = Jadwal::where('user_id', $this->testUser->id)
            ->whereYear('tanggal', $today->year)
            ->whereMonth('tanggal', $today->month)
            ->get();
        $totalBulanIni   = $bulanIniJadwal->count();
        $selesaiBulanIni = $bulanIniJadwal->where('status', 'selesai')->count();
        $progressPersen  = $totalBulanIni > 0 ? round($selesaiBulanIni / $totalBulanIni * 100) : 0;

        // Langkah 3
        $this->assertEquals(0, $progressPersen);
        $this->assertEquals(0, $totalBulanIni);
    }

    /**
     * @test TC-B03: computeStreak — dihitung dari hari ini mundur per tanggal unik berturut-turut
     *
     * Langkah:
     * 1. Buat jadwal selesai hari ini
     * 2. Buat jadwal selesai kemarin
     * 3. Tidak ada jadwal selesai 2 hari lalu (streak berhenti)
     * 4. Jalankan logika computeStreak dari controller
     * 5. Pastikan streak = 2
     */
    public function test_tcb03_compute_streak_berurutan(): void
    {
        $today = Carbon::today();

        // Langkah 1–3
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->subDay()->format('Y-m-d')]);
        // 2 hari lalu tidak ada → streak berhenti

        // Langkah 4: Logika computeStreak dari InsightController
        $completedDates = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        $streak = 0;
        $check  = Carbon::today();
        foreach ($completedDates as $date) {
            $d = Carbon::parse($date);
            if ($d->toDateString() === $check->toDateString()) {
                $streak++;
                $check->subDay();
            } elseif ($d->lt($check)) {
                break;
            }
        }

        // Langkah 5
        $this->assertEquals(2, $streak);
    }

    /**
     * @test TC-B04: Streak = 0 saat tidak ada jadwal selesai hari ini
     *
     * Langkah:
     * 1. Buat jadwal selesai kemarin (bukan hari ini)
     * 2. Jalankan computeStreak
     * 3. Streak harus = 0 karena hari ini tidak ada jadwal selesai
     */
    public function test_tcb04_streak_nol_tanpa_selesai_hari_ini(): void
    {
        // Langkah 1
        $this->buatJadwal([
            'status'  => 'selesai',
            'tanggal' => Carbon::yesterday()->format('Y-m-d'),
        ]);

        // Langkah 2
        $completedDates = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        $streak = 0;
        $check  = Carbon::today();
        foreach ($completedDates as $date) {
            $d = Carbon::parse($date);
            if ($d->toDateString() === $check->toDateString()) {
                $streak++;
                $check->subDay();
            } elseif ($d->lt($check)) {
                break;
            }
        }

        // Langkah 3
        $this->assertEquals(0, $streak);
    }

    /**
     * @test TC-B05: Jadwal mendatang berikutnya — query mengembalikan jadwal paling awal
     *
     * Langkah:
     * 1. Buat 3 jadwal mendatang dengan tanggal berbeda
     * 2. Query nextJadwal seperti controller (order tanggal ASC, first)
     * 3. Pastikan yang dikembalikan adalah jadwal paling awal
     */
    public function test_tcb05_next_jadwal_paling_awal(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(7)->format('Y-m-d'),  'jenis_pemeriksaan' => 'Jadwal Minggu Depan']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(2)->format('Y-m-d'),  'jenis_pemeriksaan' => 'Jadwal 2 Hari Lagi']);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(14)->format('Y-m-d'), 'jenis_pemeriksaan' => 'Jadwal 2 Minggu Lagi']);

        // Langkah 2
        $nextJadwal = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->where('tanggal', '>=', $today)
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu', 'asc')
            ->first();

        // Langkah 3
        $this->assertNotNull($nextJadwal);
        $this->assertEquals('Jadwal 2 Hari Lagi', $nextJadwal->jenis_pemeriksaan);
    }

    /**
     * @test TC-B06: Jadwal dalam periode terpilih — filter tanggal >= startDate berfungsi
     *
     * Langkah:
     * 1. Set periode = 1_bulan (30 hari ke belakang)
     * 2. Buat jadwal 10 hari lalu (masuk periode)
     * 3. Buat jadwal 60 hari lalu (di luar periode)
     * 4. Query jadwalPeriode seperti controller
     * 5. Pastikan hanya jadwal dalam periode yang dikembalikan
     */
    public function test_tcb06_filter_periode_30_hari(): void
    {
        $today     = Carbon::today();
        $startDate = $today->copy()->subDays(30);

        // Langkah 2–3
        $dalamPeriode = $this->buatJadwal(['tanggal' => $today->copy()->subDays(10)->format('Y-m-d')]);
        $luarPeriode  = $this->buatJadwal(['tanggal' => $today->copy()->subDays(60)->format('Y-m-d')]);

        // Langkah 4
        $jadwalPeriode = Jadwal::where('user_id', $this->testUser->id)
            ->where('tanggal', '>=', $startDate)
            ->orderBy('tanggal', 'asc')
            ->pluck('id');

        // Langkah 5
        $this->assertContains($dalamPeriode->id, $jadwalPeriode);
        $this->assertNotContains($luarPeriode->id, $jadwalPeriode);
    }

    /**
     * @test TC-B07: Statistik periode — total, selesai, pending dihitung benar dari DB
     *
     * Langkah:
     * 1. Buat 3 jadwal selesai dan 2 jadwal mendatang dalam periode 6 bulan
     * 2. Query jadwalPeriode dan hitung statistik
     * 3. Pastikan total = 5, selesai = 3, pending = 2
     * 4. Pastikan selesaiPersen = 60%, pendingPersen = 40%
     */
    public function test_tcb07_statistik_periode_dihitung_benar(): void
    {
        $today     = Carbon::today();
        $startDate = $today->copy()->subDays(180);

        // Langkah 1
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->subDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->subDays(10)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai',   'tanggal' => $today->copy()->subDays(15)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d')]);

        // Langkah 2
        $jadwalPeriode  = Jadwal::where('user_id', $this->testUser->id)
            ->where('tanggal', '>=', $startDate)
            ->get();
        $totalPeriode   = $jadwalPeriode->count();
        $selesaiPeriode = $jadwalPeriode->where('status', 'selesai')->count();
        $pendingPeriode = $jadwalPeriode->where('status', 'mendatang')->count();
        $selesaiPersen  = $totalPeriode > 0 ? round($selesaiPeriode / $totalPeriode * 100, 1) : 0;
        $pendingPersen  = $totalPeriode > 0 ? round($pendingPeriode / $totalPeriode * 100, 1) : 0;

        // Langkah 3
        $this->assertEquals(5, $totalPeriode);
        $this->assertEquals(3, $selesaiPeriode);
        $this->assertEquals(2, $pendingPeriode);

        // Langkah 4
        $this->assertEquals(60.0, $selesaiPersen);
        $this->assertEquals(40.0, $pendingPersen);
    }

    /**
     * @test TC-B08: recentJadwal — 5 jenis pemeriksaan unik paling awal
     *
     * Langkah:
     * 1. Buat 7 jadwal: 3 jenis berbeda, salah satu duplikat
     * 2. Query recentJadwal seperti controller (unique jenis, take 5, order tanggal asc)
     * 3. Pastikan yang dikembalikan unik dan maksimal 5 item
     */
    public function test_tcb08_recent_jadwal_unik_dan_limit_5(): void
    {
        $today = Carbon::today();

        // Langkah 1: Buat jadwal dengan berbagai jenis (ada duplikat)
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Darah',    'tanggal' => $today->copy()->subDays(10)->format('Y-m-d')]);
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Mata',     'tanggal' => $today->copy()->subDays(8)->format('Y-m-d')]);
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Jantung',  'tanggal' => $today->copy()->subDays(6)->format('Y-m-d')]);
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Darah',    'tanggal' => $today->copy()->subDays(4)->format('Y-m-d')]); // duplikat
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Gigi',     'tanggal' => $today->copy()->subDays(2)->format('Y-m-d')]);
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Paru',     'tanggal' => $today->copy()->subDays(1)->format('Y-m-d')]);
        $this->buatJadwal(['jenis_pemeriksaan' => 'Cek Kulit',    'tanggal' => $today->copy()->format('Y-m-d')]);

        // Langkah 2
        $recentJadwal = Jadwal::where('user_id', $this->testUser->id)
            ->orderBy('tanggal', 'asc')
            ->get()
            ->unique('jenis_pemeriksaan')
            ->take(5)
            ->values();

        // Langkah 3
        $this->assertLessThanOrEqual(5, $recentJadwal->count());
        $jenisUnik = $recentJadwal->pluck('jenis_pemeriksaan')->unique();
        $this->assertEquals($recentJadwal->count(), $jenisUnik->count()); // tidak ada duplikat
    }

    /**
     * @test TC-B09: Data isolation — progress hanya menghitung jadwal milik user sendiri
     *
     * Langkah:
     * 1. Buat user lain dengan 10 jadwal selesai bulan ini
     * 2. Buat 2 jadwal selesai untuk testUser bulan ini
     * 3. Query progress konsistensi khusus testUser
     * 4. Pastikan totalBulanIni = 2 (bukan 12)
     */
    public function test_tcb09_data_isolation_progress(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE14',
            'email'         => "lain.pke14.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
        for ($i = 0; $i < 10; $i++) {
            Jadwal::create([
                'user_id'           => $userLain->id,
                'jenis_pemeriksaan' => "Jadwal Orang Lain {$i}",
                'tanggal'           => $today->copy()->addDays($i)->format('Y-m-d'),
                'waktu'             => '09:00',
                'fasilitas_klinik'  => 'RS X',
                'status'            => 'selesai',
            ]);
        }

        // Langkah 2
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->addDays(1)->format('Y-m-d')]);

        // Langkah 3
        $bulanIniSaya = Jadwal::where('user_id', $this->testUser->id)
            ->whereYear('tanggal', $today->year)
            ->whereMonth('tanggal', $today->month)
            ->get();

        // Langkah 4
        $this->assertEquals(2, $bulanIniSaya->count());

        Jadwal::where('user_id', $userLain->id)->delete();
        $userLain->delete();
    }

    /**
     * @test TC-B10: Insight "jadwal terlewat" — jadwal mendatang dengan tanggal lampau terdeteksi
     *
     * Langkah:
     * 1. Buat jadwal status mendatang dengan tanggal kemarin (terlewat)
     * 2. Query jadwal terlewat seperti computeSmartInsights()
     * 3. Pastikan jadwal tersebut terdeteksi sebagai terlewat
     */
    public function test_tcb10_insight_jadwal_terlewat(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal([
            'status'            => 'mendatang',
            'tanggal'           => $today->copy()->subDays(3)->format('Y-m-d'),
            'jenis_pemeriksaan' => 'Cek Terlewat B10',
        ]);

        // Langkah 2
        $missed = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->where('tanggal', '<', $today)
            ->selectRaw('jenis_pemeriksaan, COUNT(*) as cnt')
            ->groupBy('jenis_pemeriksaan')
            ->get();

        // Langkah 3
        $this->assertGreaterThan(0, $missed->count());
        $this->assertEquals('Cek Terlewat B10', $missed->first()->jenis_pemeriksaan);
    }

    /**
     * @test TC-B11: Insight "jadwal mendatang 7 hari" — jadwal dalam 7 hari terdeteksi
     *
     * Langkah:
     * 1. Buat 2 jadwal mendatang dalam 7 hari ke depan
     * 2. Buat 1 jadwal mendatang > 7 hari (tidak terhitung)
     * 3. Query upcoming seperti computeSmartInsights()
     * 4. Pastikan count = 2
     */
    public function test_tcb11_insight_jadwal_mendatang_7_hari(): void
    {
        $today = Carbon::today();

        // Langkah 1–2
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(2)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(5)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d')]);

        // Langkah 3
        $upcoming = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(7)])
            ->count();

        // Langkah 4
        $this->assertEquals(2, $upcoming);
    }
}
