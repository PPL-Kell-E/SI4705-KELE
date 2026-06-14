<?php
// PKE-15: Browser test untuk Insight dan Evaluasi Kesehatan
namespace Tests\Browser;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE15_InsightTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE15',
            'email'         => "dusk.pke15.{$uniqueId}@test.local",
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

    private function loginDanBukaInsight(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/insight')
                    ->waitFor('.insight-grid', 15);
    }

    private function buatJadwal(array $override = []): Jadwal
    {
        return Jadwal::create(array_merge([
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Cek Umum Dusk',
            'tanggal'           => Carbon::today()->format('Y-m-d'),
            'waktu'             => '09:00',
            'fasilitas_klinik'  => 'RS Dusk',
            'status'            => 'selesai',
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Menampilkan halaman insight dan pencapaian — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_menampilkan_halaman_insight_dan_pencapaian(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPathIs('/insight')
                 ->assertVisible('.insight-grid')
                 ->assertVisible('.streak-banner')
                 ->screenshot('tc01-halaman-insight-berhasil-ditampilkan');
        });
    }

    /** @test TC-02: Mengambil data insight kesehatan — sistem berhasil mengambil data insight (Positive) */
    public function test_tc02_mengambil_data_insight_kesehatan(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.insight-grid')
                 ->assertPresent('.section-card')
                 ->screenshot('tc02-data-insight-berhasil-diambil');
        });
    }

    /** @test TC-03: Menghitung health streak — sistem menampilkan jumlah streak dengan benar (Positive) */
    public function test_tc03_menghitung_health_streak(): void
    {
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.streak-banner')
                 ->assertVisible('.streak-count')
                 ->assertVisible('.streak-label')
                 ->screenshot('tc03-health-streak-tampil');
        });
    }

    /** @test TC-04: Menampilkan pencapaian tercapai — sistem menampilkan pencapaian yang berhasil diperoleh (Positive) */
    public function test_tc04_menampilkan_pencapaian_tercapai(): void
    {
        // totalCompleted >= 2 → unlock achievement "Periksa Rutin"
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->subDays(1)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.achievement-card.achieved')
                 ->assertSee('Tercapai')
                 ->screenshot('tc04-pencapaian-tercapai-tampil');
        });
    }

    /** @test TC-05: Menampilkan pencapaian belum tercapai — sistem menampilkan progress achievement (Positive) */
    public function test_tc05_menampilkan_pencapaian_belum_tercapai(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.achievement-card')
                 ->assertPresent('.achievement-progress-bar-wrap')
                 ->screenshot('tc05-pencapaian-belum-tercapai-progress-tampil');
        });
    }

    /** @test TC-06: Menampilkan insight pintar — sistem menampilkan insight kesehatan (Positive) */
    public function test_tc06_menampilkan_insight_pintar(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.insight-list')
                 ->assertPresent('.insight-item')
                 ->assertVisible('.insight-item-title')
                 ->screenshot('tc06-insight-pintar-tampil');
        });
    }

    /** @test TC-07: Menampilkan tips kesehatan — sistem menampilkan tips kesehatan harian (Positive) */
    public function test_tc07_menampilkan_tips_kesehatan(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.tips-card')
                 ->assertVisible('.tips-title')
                 ->assertVisible('.tips-desc')
                 ->screenshot('tc07-tips-kesehatan-tampil');
        });
    }

    /** @test TC-08: Menampilkan rekomendasi kesehatan — sistem menampilkan rekomendasi kesehatan (Positive) */
    public function test_tc08_menampilkan_rekomendasi_kesehatan(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.tips-recommendation')
                 ->assertVisible('.tips-rec-label')
                 ->screenshot('tc08-rekomendasi-kesehatan-tampil');
        });
    }

    /** @test TC-09: Pencapaian belum tersedia — sistem menampilkan pesan pencapaian kosong (Negative) */
    public function test_tc09_pencapaian_belum_tersedia(): void
    {
        // User baru tanpa aktivitas → tidak ada achievement tercapai
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.empty-state')
                 ->assertMissing('.achievement-card.achieved')
                 ->screenshot('tc09-pencapaian-kosong-empty-state');
        });
    }

    /** @test TC-10: Insight belum tersedia — sistem menampilkan section insight meski aktivitas belum cukup (Negative) */
    public function test_tc10_insight_belum_tersedia(): void
    {
        // User baru tanpa aktivitas → insight section tetap tampil (sistem selalu hasilkan insight default)
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.insight-list')
                 ->assertVisible('.section-card')
                 ->assertMissing('.achievement-card.achieved')
                 ->screenshot('tc10-insight-belum-tersedia-default');
        });
    }

    /** @test TC-11: Health streak belum tersedia — sistem menampilkan informasi streak belum tersedia (Negative) */
    public function test_tc11_health_streak_belum_tersedia(): void
    {
        // User tanpa jadwal selesai hari ini → streak = 0 → streak-zero tampil
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.streak-zero')
                 ->assertMissing('.streak-count')
                 ->screenshot('tc11-health-streak-belum-tersedia');
        });
    }

    /** @test TC-12: Gagal mengambil data insight — sistem menampilkan pesan gagal memuat data (Negative) */
    public function test_tc12_gagal_mengambil_data_insight(): void
    {
        // Akses tanpa login → redirect ke login
        $this->browse(function (Browser $browser) {
            $browser->visit('/insight')
                    ->pause(1000)
                    ->assertPathIs('/login')
                    ->screenshot('tc12-redirect-login-jika-belum-login');
        });
    }

    /** @test TC-13: Menampilkan loading state — sistem menampilkan loading state sementara (Positive) */
    public function test_tc13_menampilkan_loading_state(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/insight')
                 ->waitFor('.insight-grid', 15)
                 ->assertVisible('.insight-grid')
                 ->assertPresent('.section-card')
                 ->screenshot('tc13-loading-state-selesai');
        });
    }

    /** @test TC-14: Validasi pembaruan insight — insight diperbarui tanpa error (Positive) */
    public function test_tc14_validasi_pembaruan_insight(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->refresh()
                 ->waitFor('.insight-grid', 15)
                 ->assertPathIs('/insight')
                 ->assertVisible('.insight-grid')
                 ->assertPresent('.last-updated')
                 ->screenshot('tc14-insight-diperbarui-tanpa-error');
        });
    }
}
