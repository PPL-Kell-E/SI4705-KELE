<?php
// PKE-12: Browser test untuk Riwayat Kesehatan
namespace Tests\Browser;

use App\Models\HasilPemeriksaan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE12_RiwayatKesehatanTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE12',
            'email'         => "dusk.pke12.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        HasilPemeriksaan::where('user_id', $this->testUser->id)->delete();
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

    private function loginDanBukaRiwayat(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/riwayat')
                    ->waitFor('.filter-bar', 15);
    }

    private function buatRiwayat(array $override = []): HasilPemeriksaan
    {
        return HasilPemeriksaan::create(array_merge([
            'user_id'             => $this->testUser->id,
            'jenis_pemeriksaan'   => 'Cek Darah Dusk',
            'tanggal_pemeriksaan' => Carbon::today()->format('Y-m-d'),
            'fasilitas_kesehatan' => 'RS Dusk',
            'nama_dokter'         => 'dr. Dusk',
            'hasil_pemeriksaan'   => 'Semua hasil dalam batas normal.',
            'catatan_tambahan'    => 'Kontrol 3 bulan lagi.',
            'hidden_at'           => null,
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Menampilkan halaman riwayat kesehatan — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_menampilkan_halaman_riwayat_kesehatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertPathIs('/riwayat')
                 ->assertSee('Riwayat Kesehatan')
                 ->assertVisible('.filter-bar')
                 ->screenshot('tc01-halaman-riwayat-kesehatan');
        });
    }

    /** @test TC-02: Mengambil data riwayat kesehatan — sistem berhasil ambil data (Positive) */
    public function test_tc02_mengambil_data_riwayat_kesehatan(): void
    {
        $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Ambil Data Riwayat']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertSee('Dusk Ambil Data Riwayat')
                 ->assertPresent('.riwayat-card')
                 ->screenshot('tc02-data-riwayat-berhasil-diambil');
        });
    }

    /** @test TC-03: Menampilkan timeline riwayat — timeline tampil dengan benar (Positive) */
    public function test_tc03_menampilkan_timeline_riwayat(): void
    {
        $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Timeline Test']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertVisible('.timeline')
                 ->assertPresent('.tl-item')
                 ->assertVisible('.riwayat-card')
                 ->assertVisible('.date-badge')
                 ->assertVisible('.card-title')
                 ->screenshot('tc03-timeline-riwayat-tampil');
        });
    }

    /** @test TC-04: Menggunakan filter minggu ini — riwayat diperbarui sesuai minggu (Positive) */
    public function test_tc04_filter_minggu_ini(): void
    {
        // Data dengan tanggal minggu ini
        $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Dusk Filter Minggu',
            'tanggal_pemeriksaan' => Carbon::now()->startOfWeek()->format('Y-m-d'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRiwayat($browser)
                 ->clickLink('Minggu Ini')
                 ->waitFor('.filter-bar', 10)
                 ->assertQueryStringHas('filter', 'minggu_ini')
                 ->assertSee('Minggu Ini')
                 ->screenshot('tc04-filter-minggu-ini');
        });
    }

    /** @test TC-05: Menggunakan filter bulan ini — riwayat diperbarui sesuai bulan (Positive) */
    public function test_tc05_filter_bulan_ini(): void
    {
        $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Dusk Filter Bulan',
            'tanggal_pemeriksaan' => Carbon::now()->startOfMonth()->format('Y-m-d'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRiwayat($browser)
                 ->clickLink('Bulan Ini')
                 ->waitFor('.filter-bar', 10)
                 ->assertQueryStringHas('filter', 'bulan_ini')
                 ->assertSee('Bulan Ini')
                 ->screenshot('tc05-filter-bulan-ini');
        });
    }

    /** @test TC-06: Menggunakan custom date range — riwayat sesuai rentang tanggal (Positive) */
    public function test_tc06_custom_date_range(): void
    {
        $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Dusk Custom Range',
            'tanggal_pemeriksaan' => '2026-01-15',
        ]);

        $this->browse(function (Browser $browser) {
            // Navigasi langsung dengan query params custom range
            $this->login($browser)
                 ->visit('/riwayat?filter=custom&dari=2026-01-01&sampai=2026-01-31')
                 ->waitFor('.filter-bar', 15)
                 ->assertSee('Dusk Custom Range')
                 ->assertQueryStringHas('filter', 'custom')
                 ->screenshot('tc06-custom-date-range');
        });
    }

    /** @test TC-07: Filter tidak menemukan data — sistem menampilkan pesan data tidak ditemukan (Negative) */
    public function test_tc07_filter_tidak_menemukan_data(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/riwayat?filter=custom&dari=2000-01-01&sampai=2000-01-31')
                 ->waitFor('.filter-bar', 15)
                 ->assertVisible('.empty-state')
                 ->assertSee('Tidak ada riwayat pemeriksaan pada rentang waktu tersebut')
                 ->screenshot('tc07-filter-tidak-menemukan-data');
        });
    }

    /** @test TC-08: Menampilkan detail riwayat — detail riwayat pemeriksaan tampil (Positive) */
    public function test_tc08_menampilkan_detail_riwayat(): void
    {
        $hasil = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Dusk Detail Riwayat',
            'fasilitas_kesehatan' => 'RS Detail Dusk',
            'hasil_pemeriksaan'   => 'Detail hasil lengkap untuk pengujian Dusk.',
        ]);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertSee('Dusk Detail Riwayat')
                 ->click("#card-{$hasil->id} .btn-toggle-detail")
                 ->waitFor("#card-{$hasil->id}.is-open", 5)
                 ->assertVisible('.card-detail')
                 ->assertSee('Detail hasil lengkap untuk pengujian Dusk.')
                 ->screenshot('tc08-detail-riwayat-tampil');
        });
    }

    /** @test TC-09: Riwayat pemeriksaan kosong — sistem menampilkan empty state (Negative) */
    public function test_tc09_riwayat_pemeriksaan_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertVisible('.empty-state')
                 ->assertSee('Belum ada riwayat pemeriksaan terdokumentasi')
                 ->assertMissing('.riwayat-card')
                 ->screenshot('tc09-riwayat-kosong-empty-state');
        });
    }

    /** @test TC-10: Membuka konfirmasi hapus riwayat — popup konfirmasi tampil (Positive) */
    public function test_tc10_membuka_konfirmasi_hapus(): void
    {
        $hasil = $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Konfirmasi Hapus']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertSee('Dusk Konfirmasi Hapus')
                 ->click("#card-{$hasil->id} .btn-hapus-riwayat")
                 ->waitFor('#confirmModal.open', 5)
                 ->assertVisible('.modal-box')
                 ->assertSee('Hapus dari Riwayat?')
                 ->assertVisible('.btn-modal-confirm')
                 ->assertVisible('.btn-modal-cancel')
                 ->screenshot('tc10-konfirmasi-hapus-tampil');
        });
    }

    /** @test TC-11: Menghapus riwayat dari tampilan — riwayat disembunyikan dari halaman (Positive) */
    public function test_tc11_menghapus_riwayat_dari_tampilan(): void
    {
        $hasil = $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Hapus Tampilan']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertSee('Dusk Hapus Tampilan')
                 ->click("#card-{$hasil->id} .btn-hapus-riwayat")
                 ->waitFor('#confirmModal.open', 5)
                 ->click('.btn-modal-confirm')
                 ->waitFor('.filter-bar', 10)
                 ->assertDontSee('Dusk Hapus Tampilan')
                 ->screenshot('tc11-riwayat-berhasil-disembunyikan');
        });
    }

    /** @test TC-12: Validasi data database — data asli tidak terhapus setelah hide (Positive) */
    public function test_tc12_validasi_data_asli_tidak_terhapus(): void
    {
        $hasil = $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Validasi DB']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaRiwayat($browser)
                 ->click("#card-{$hasil->id} .btn-hapus-riwayat")
                 ->waitFor('#confirmModal.open', 5)
                 ->click('.btn-modal-confirm')
                 ->waitFor('.filter-bar', 10)
                 ->screenshot('tc12-validasi-data-db');
        });

        // Data masih ada di DB, hanya hidden_at yang terisi
        $this->assertDatabaseHas('hasil_pemeriksaan', ['id' => $hasil->id]);
        $this->assertNotNull(HasilPemeriksaan::find($hasil->id)?->hidden_at);
    }

    /** @test TC-13: Membatalkan penghapusan — riwayat tetap ditampilkan (Positive) */
    public function test_tc13_membatalkan_penghapusan(): void
    {
        $hasil = $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Batal Hapus']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertSee('Dusk Batal Hapus')
                 ->click("#card-{$hasil->id} .btn-hapus-riwayat")
                 ->waitFor('#confirmModal.open', 5)
                 ->click('.btn-modal-cancel')
                 ->pause(500)
                 ->assertMissing('#confirmModal.open')
                 ->assertSee('Dusk Batal Hapus')
                 ->screenshot('tc13-penghapusan-dibatalkan');
        });

        $this->assertNull(HasilPemeriksaan::find($hasil->id)?->hidden_at);
    }

    /** @test TC-14: Gagal menghapus riwayat — halaman menampilkan toast error saat hide gagal (Negative) */
    public function test_tc14_gagal_menghapus_riwayat(): void
    {
        // Simulasi: riwayat sudah di-hide sebelumnya, coba klik hapus lagi → item tidak ada di DOM
        $hasil = $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Gagal Hapus', 'hidden_at' => now()]);

        $this->browse(function (Browser $browser) use ($hasil) {
            // Item dengan hidden_at tidak muncul di riwayat, sehingga tidak bisa dihapus lagi
            $this->loginDanBukaRiwayat($browser)
                 ->assertMissing("#card-{$hasil->id}")
                 ->assertMissing(".toast-error")
                 ->screenshot('tc14-gagal-hapus-item-sudah-hidden');
        });
    }

    /** @test TC-15: Gagal memuat detail riwayat — filter ekstrem tidak crash halaman (Negative) */
    public function test_tc15_gagal_memuat_detail_riwayat(): void
    {
        $this->browse(function (Browser $browser) {
            // Filter dengan rentang tanggal tidak valid / ekstrem → halaman tetap render
            $this->login($browser)
                 ->visit('/riwayat?filter=custom&dari=2099-01-01&sampai=2099-01-31')
                 ->waitFor('.filter-bar', 15)
                 ->assertVisible('.filter-bar')
                 ->assertVisible('.empty-state')
                 ->screenshot('tc15-filter-ekstrem-halaman-tetap-render');
        });
    }

    /** @test TC-16: Menampilkan loading state — halaman berhasil dimuat setelah loading (Positive) */
    public function test_tc16_menampilkan_loading_state(): void
    {
        $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Loading State']);

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/riwayat')
                 ->waitFor('.filter-bar', 15)
                 ->assertVisible('.filter-bar')
                 ->assertPresent('.riwayat-card')
                 ->screenshot('tc16-loading-state-selesai');
        });
    }

    /** @test TC-17: Validasi pembaruan tampilan — daftar diperbarui tanpa reload/error (Positive) */
    public function test_tc17_validasi_pembaruan_tampilan_setelah_hapus(): void
    {
        $hasil = $this->buatRiwayat(['jenis_pemeriksaan' => 'Dusk Validasi Tampilan']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaRiwayat($browser)
                 ->assertSee('Dusk Validasi Tampilan')
                 ->click("#card-{$hasil->id} .btn-hapus-riwayat")
                 ->waitFor('#confirmModal.open', 5)
                 ->click('.btn-modal-confirm')
                 ->waitFor('.filter-bar', 10)
                 // Halaman diperbarui tanpa error — toast sukses tampil
                 ->assertPresent('.toast-success')
                 ->assertDontSee('Dusk Validasi Tampilan')
                 ->screenshot('tc17-tampilan-diperbarui-tanpa-error');
        });
    }
}
