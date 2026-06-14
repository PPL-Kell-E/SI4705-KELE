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
}
