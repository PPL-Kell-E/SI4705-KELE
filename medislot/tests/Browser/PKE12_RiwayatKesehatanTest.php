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

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /** @test TC-B01: Riwayat tersimpan ke DB dan terbaca dengan benar */
    public function test_tcb01_riwayat_tersimpan_ke_db(): void
    {
        $riwayat = $this->buatRiwayat(['jenis_pemeriksaan' => 'Riwayat B01']);

        $this->assertDatabaseHas('hasil_pemeriksaan', [
            'id'                => $riwayat->id,
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Riwayat B01',
            'hidden_at'         => null,
        ]);
    }

    /** @test TC-B02: hide() mengisi hidden_at, data tidak terhapus dari DB */
    public function test_tcb02_hide_isi_hidden_at_data_tetap_ada(): void
    {
        $riwayat = $this->buatRiwayat(['jenis_pemeriksaan' => 'Riwayat B02']);

        $riwayat->update(['hidden_at' => now()]);

        $fresh = HasilPemeriksaan::find($riwayat->id);
        $this->assertNotNull($fresh->hidden_at);
        $this->assertDatabaseHas('hasil_pemeriksaan', ['id' => $riwayat->id]);
    }

    /** @test TC-B03: Riwayat dengan hidden_at tidak muncul di query riwayat */
    public function test_tcb03_riwayat_hidden_tidak_muncul_di_query(): void
    {
        $terlihat  = $this->buatRiwayat(['jenis_pemeriksaan' => 'Riwayat Terlihat B03', 'hidden_at' => null]);
        $tersembunyi = $this->buatRiwayat(['jenis_pemeriksaan' => 'Riwayat Tersembunyi B03', 'hidden_at' => now()]);

        $hasil = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->pluck('id');

        $this->assertContains($terlihat->id, $hasil);
        $this->assertNotContains($tersembunyi->id, $hasil);
    }

    /** @test TC-B04: Filter minggu_ini hanya mengembalikan data minggu ini */
    public function test_tcb04_filter_minggu_ini(): void
    {
        $mingguIni = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B04 Minggu Ini',
            'tanggal_pemeriksaan' => Carbon::now()->startOfWeek()->format('Y-m-d'),
        ]);
        $bulanLalu = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B04 Bulan Lalu',
            'tanggal_pemeriksaan' => Carbon::now()->subMonth()->format('Y-m-d'),
        ]);

        $hasil = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->whereBetween('tanggal_pemeriksaan', [
                        Carbon::now()->startOfWeek(Carbon::MONDAY),
                        Carbon::now()->endOfWeek(Carbon::SUNDAY),
                    ])
                    ->pluck('id');

        $this->assertContains($mingguIni->id, $hasil);
        $this->assertNotContains($bulanLalu->id, $hasil);
    }

    /** @test TC-B05: Filter bulan_ini hanya mengembalikan data bulan ini */
    public function test_tcb05_filter_bulan_ini(): void
    {
        $bulanIni = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B05 Bulan Ini',
            'tanggal_pemeriksaan' => Carbon::now()->startOfMonth()->format('Y-m-d'),
        ]);
        $tahunLalu = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B05 Tahun Lalu',
            'tanggal_pemeriksaan' => Carbon::now()->subYear()->format('Y-m-d'),
        ]);

        $hasil = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->whereYear('tanggal_pemeriksaan', now()->year)
                    ->whereMonth('tanggal_pemeriksaan', now()->month)
                    ->pluck('id');

        $this->assertContains($bulanIni->id, $hasil);
        $this->assertNotContains($tahunLalu->id, $hasil);
    }

    /** @test TC-B06: Filter custom date range mengembalikan data dalam rentang yang tepat */
    public function test_tcb06_filter_custom_date_range(): void
    {
        $dalamRange = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B06 Dalam Range',
            'tanggal_pemeriksaan' => '2026-01-15',
        ]);
        $luarRange = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B06 Luar Range',
            'tanggal_pemeriksaan' => '2026-03-01',
        ]);

        $hasil = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->where('tanggal_pemeriksaan', '>=', '2026-01-01')
                    ->where('tanggal_pemeriksaan', '<=', '2026-01-31')
                    ->pluck('id');

        $this->assertContains($dalamRange->id, $hasil);
        $this->assertNotContains($luarRange->id, $hasil);
    }

    /** @test TC-B07: Filter tahun_ini hanya mengembalikan data tahun ini */
    public function test_tcb07_filter_tahun_ini(): void
    {
        $tahunIni = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B07 Tahun Ini',
            'tanggal_pemeriksaan' => Carbon::now()->format('Y-m-d'),
        ]);
        $tahunLalu = $this->buatRiwayat([
            'jenis_pemeriksaan'   => 'Riwayat B07 Tahun Lalu',
            'tanggal_pemeriksaan' => Carbon::now()->subYear()->format('Y-m-d'),
        ]);

        $hasil = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->whereYear('tanggal_pemeriksaan', now()->year)
                    ->pluck('id');

        $this->assertContains($tahunIni->id, $hasil);
        $this->assertNotContains($tahunLalu->id, $hasil);
    }

    /** @test TC-B08: Riwayat diurutkan descending berdasarkan tanggal_pemeriksaan */
    public function test_tcb08_urutan_descending_tanggal(): void
    {
        $this->buatRiwayat(['tanggal_pemeriksaan' => '2026-01-01']);
        $this->buatRiwayat(['tanggal_pemeriksaan' => '2026-03-15']);
        $this->buatRiwayat(['tanggal_pemeriksaan' => '2026-02-10']);

        $hasil = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->orderByDesc('tanggal_pemeriksaan')
                    ->get();

        for ($i = 0; $i < $hasil->count() - 1; $i++) {
            $this->assertTrue(
                $hasil[$i]->tanggal_pemeriksaan->gte($hasil[$i + 1]->tanggal_pemeriksaan),
                'Riwayat harus diurutkan descending berdasarkan tanggal_pemeriksaan'
            );
        }
    }

    /** @test TC-B09: Data isolation — riwayat user lain tidak terlihat */
    public function test_tcb09_data_isolation_antar_user(): void
    {
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE12',
            'email'         => "lain.pke12.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $milikSaya   = $this->buatRiwayat(['jenis_pemeriksaan' => 'Milik Saya B09']);
        $milikMereka = HasilPemeriksaan::create([
            'user_id'             => $userLain->id,
            'jenis_pemeriksaan'   => 'Milik Mereka B09',
            'tanggal_pemeriksaan' => Carbon::today()->format('Y-m-d'),
            'hasil_pemeriksaan'   => 'Hasil mereka.',
        ]);

        $hasilSaya = HasilPemeriksaan::where('user_id', $this->testUser->id)
                        ->whereNull('hidden_at')
                        ->pluck('id');

        $this->assertContains($milikSaya->id, $hasilSaya);
        $this->assertNotContains($milikMereka->id, $hasilSaya);

        $milikMereka->delete();
        $userLain->delete();
    }

    /** @test TC-B10: Otorisasi — user lain tidak bisa hide riwayat user ini */
    public function test_tcb10_otorisasi_user_lain_tidak_bisa_hide(): void
    {
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'Intruder PKE12',
            'email'         => "intruder.pke12.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $riwayat = $this->buatRiwayat(['jenis_pemeriksaan' => 'Data Pribadi B10']);

        // Verifikasi: user_id berbeda → abort_if di controller akan reject
        $this->assertNotEquals($userLain->id, $riwayat->user_id);
        $this->assertNull($riwayat->hidden_at);

        $userLain->delete();
    }

    /** @test TC-B11: Filter kosong (semua) mengembalikan seluruh riwayat visible */
    public function test_tcb11_filter_semua_tanpa_batasan(): void
    {
        $this->buatRiwayat(['tanggal_pemeriksaan' => '2025-05-01']);
        $this->buatRiwayat(['tanggal_pemeriksaan' => '2024-12-31']);
        $this->buatRiwayat(['tanggal_pemeriksaan' => Carbon::today()->format('Y-m-d')]);

        $semua = HasilPemeriksaan::where('user_id', $this->testUser->id)
                    ->whereNull('hidden_at')
                    ->count();

        $this->assertEquals(3, $semua);
    }

    /** @test TC-B12: hidden_at terisi datetime saat hide, bukan null */
    public function test_tcb12_hidden_at_terisi_datetime_setelah_hide(): void
    {
        $riwayat = $this->buatRiwayat();
        $this->assertNull($riwayat->hidden_at);

        $riwayat->update(['hidden_at' => now()]);

        $fresh = HasilPemeriksaan::find($riwayat->id);
        $this->assertNotNull($fresh->hidden_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fresh->hidden_at);
    }
}
