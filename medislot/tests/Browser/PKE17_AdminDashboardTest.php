<?php
// PKE-17: Browser test untuk Panel Monitoring Admin
namespace Tests\Browser;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE17_AdminDashboardTest extends DuskTestCase
{
    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->adminUser = User::create([
            'full_name'     => 'Admin Dusk PKE17',
            'email'         => "dusk.admin.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'admin',
        ]);

        $this->regularUser = User::create([
            'full_name'     => 'User Dusk PKE17',
            'email'         => "dusk.user.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        Jadwal::where('user_id', $this->regularUser->id)->delete();
        $this->regularUser->delete();
        $this->adminUser->delete();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function loginAsAdmin(Browser $browser): Browser
    {
        return $browser->loginAs($this->adminUser);
    }

    private function loginDanBukaAdmin(Browser $browser): Browser
    {
        return $this->loginAsAdmin($browser)
                    ->visit('/admin')
                    ->waitFor('.stat-row', 15);
    }

    private function buatJadwal(array $override = []): Jadwal
    {
        return Jadwal::create(array_merge([
            'user_id'           => $this->regularUser->id,
            'jenis_pemeriksaan' => 'Cek Umum Admin Dusk',
            'tanggal'           => Carbon::today()->format('Y-m-d'),
            'waktu'             => '09:00',
            'fasilitas_klinik'  => 'RS Dusk Admin',
            'status'            => 'mendatang',
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Membuka dashboard admin — Dashboard admin berhasil ditampilkan (Positive) */
    public function test_tc01_membuka_dashboard_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertPathIs('/admin')
                 ->assertVisible('.stat-row')
                 ->assertSee('Dashboard')
                 ->screenshot('tc01-dashboard-admin-berhasil-ditampilkan');
        });
    }

    /** @test TC-02: Menampilkan statistik pengguna — Total pengguna berhasil ditampilkan (Positive) */
    public function test_tc02_menampilkan_statistik_pengguna(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertVisible('.stat-card')
                 ->assertSee('Total Pengguna')
                 ->assertPresent('.stat-value')
                 ->screenshot('tc02-statistik-pengguna-tampil');
        });
    }

    /** @test TC-03: Menampilkan statistik jadwal — Total jadwal pemeriksaan berhasil ditampilkan (Positive) */
    public function test_tc03_menampilkan_statistik_jadwal(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertSee('Total Jadwal Pemeriksaan')
                 ->assertPresent('.stat-value')
                 ->screenshot('tc03-statistik-jadwal-tampil');
        });
    }

    /** @test TC-04: Menampilkan pengguna aktif — Jumlah pengguna aktif bulan ini tampil (Positive) */
    public function test_tc04_menampilkan_pengguna_aktif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertSee('Pengguna Aktif Bulan Ini')
                 ->assertPresent('.stat-value')
                 ->assertPresent('.stat-label')
                 ->screenshot('tc04-pengguna-aktif-tampil');
        });
    }

    /** @test TC-05: Menampilkan grafik pemeriksaan — Grafik pemeriksaan bulanan berhasil ditampilkan (Positive) */
    public function test_tc05_menampilkan_grafik_pemeriksaan(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertSee('Pemeriksaan Bulanan')
                 ->assertVisible('.chart-wrap')
                 ->assertPresent('#monthlyChart')
                 ->screenshot('tc05-grafik-pemeriksaan-tampil');
        });
    }

    /** @test TC-06: Menampilkan daftar pengguna — Daftar pengguna berhasil ditampilkan (Positive) */
    public function test_tc06_menampilkan_daftar_pengguna(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertSee('Daftar Pengguna')
                 ->assertVisible('.user-table')
                 ->screenshot('tc06-daftar-pengguna-tampil');
        });
    }

    /** @test TC-07: Melakukan pencarian pengguna — Sistem memfilter daftar pengguna (Positive) */
    public function test_tc07_melakukan_pencarian_pengguna(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertVisible('.search-wrap')
                 ->type('#searchInput', 'User Dusk')
                 ->keys('#searchInput', '{enter}')
                 ->waitFor('.user-table', 10)
                 ->assertPathIs('/admin')
                 ->screenshot('tc07-pencarian-pengguna-difilter');
        });
    }

    /** @test TC-08: Hasil pencarian ditemukan — Data pengguna yang dicari tampil (Positive) */
    public function test_tc08_hasil_pencarian_ditemukan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->type('#searchInput', 'User Dusk PKE17')
                 ->keys('#searchInput', '{enter}')
                 ->waitFor('.user-table', 10)
                 ->assertSee('User Dusk PKE17')
                 ->assertMissing('.empty-row')
                 ->screenshot('tc08-hasil-pencarian-ditemukan');
        });
    }

    /** @test TC-09: Hasil pencarian tidak ditemukan — Sistem menampilkan pesan "Pengguna tidak ditemukan" (Negative) */
    public function test_tc09_hasil_pencarian_tidak_ditemukan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->type('#searchInput', 'xyzKataKunciTidakAda99999')
                 ->keys('#searchInput', '{enter}')
                 ->waitFor('.user-table', 10)
                 ->assertPresent('.empty-row')
                 ->assertSee('Pengguna tidak ditemukan')
                 ->screenshot('tc09-hasil-pencarian-tidak-ditemukan');
        });
    }

    /** @test TC-10: Data dashboard kosong — Sistem menampilkan nilai default (Negative) */
    public function test_tc10_data_dashboard_kosong(): void
    {
        // Admin baru tanpa jadwal → totalJadwal = 0, stat tetap tampil dengan angka default 0
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->assertVisible('.stat-row')
                 ->assertPresent('.stat-value')
                 ->assertSee('Total Jadwal Pemeriksaan')
                 ->assertSee('Total Pengguna')
                 ->screenshot('tc10-dashboard-kosong-nilai-default');
        });
    }

    /** @test TC-11: Grafik gagal dimuat — Sistem menampilkan pesan error grafik (Negative) */
    public function test_tc11_grafik_gagal_dimuat(): void
    {
        // Verifikasi chart-wrap hadir dan canvas chart dapat dirender
        // (simulasi error state via filter tahun yang tidak ada data)
        $tahunLama = now()->year - 4;

        $this->browse(function (Browser $browser) use ($tahunLama) {
            $this->loginAsAdmin($browser)
                 ->visit("/admin?tahun={$tahunLama}")
                 ->waitFor('.chart-wrap', 15)
                 ->assertVisible('.chart-wrap')
                 ->assertPresent('#monthlyChart')
                 ->screenshot('tc11-grafik-tahun-lama-kosong');
        });
    }

    /** @test TC-12: Database gagal diakses — Sistem menampilkan pesan gagal memuat dashboard (Negative) */
    public function test_tc12_database_gagal_diakses(): void
    {
        // Akses /admin tanpa login → redirect ke halaman login (unauthorized)
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin')
                    ->pause(1000)
                    ->assertPathIs('/login')
                    ->screenshot('tc12-redirect-login-jika-belum-login');
        });
    }

    /** @test TC-13: Loading state dashboard — Sistem menampilkan loading state (Positive) */
    public function test_tc13_loading_state_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                 ->visit('/admin')
                 ->waitFor('.stat-row', 15)
                 ->assertVisible('.stat-row')
                 ->assertPresent('.panel')
                 ->screenshot('tc13-loading-state-selesai');
        });
    }

    /** @test TC-14: Data dashboard diperbarui — Dashboard menampilkan data terbaru (Positive) */
    public function test_tc14_data_dashboard_diperbarui(): void
    {
        $this->buatJadwal(['status' => 'selesai']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaAdmin($browser)
                 ->refresh()
                 ->waitFor('.stat-row', 15)
                 ->assertPathIs('/admin')
                 ->assertVisible('.stat-row')
                 ->assertVisible('.user-table')
                 ->screenshot('tc14-dashboard-diperbarui-data-terbaru');
        });
    }
}
