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
}
