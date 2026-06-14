<?php
// PKE-16: Browser test untuk Target Kesehatan
namespace Tests\Browser;

use App\Models\TargetKesehatan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE16_TargetKesehatanTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE16',
            'email'         => "dusk.pke16.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        TargetKesehatan::where('user_id', $this->testUser->id)->delete();
        $this->testUser->delete();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function login(Browser $browser): Browser
    {
        return $browser->loginAs($this->testUser);
    }

    private function loginDanBukaTarget(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/target-kesehatan')
                    ->waitFor('.stat-cards', 15);
    }

    private function buatTarget(array $override = []): TargetKesehatan
    {
        return TargetKesehatan::create(array_merge([
            'user_id'             => $this->testUser->id,
            'nama'                => 'Target Dusk',
            'deskripsi'           => 'Deskripsi target dusk test',
            'icon'                => 'fas fa-heartbeat',
            'icon_color'          => '#2d9e72',
            'icon_bg'             => '#e8fff4',
            'target_aktivitas'    => 10,
            'aktivitas_dilakukan' => 5,
            'satuan'              => 'kali',
            'tanggal_target'      => Carbon::today()->addMonth()->format('Y-m-d'),
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Membuka halaman Target Kesehatan — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_membuka_halaman_target_kesehatan(): void
    {
        $this->buatTarget();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertPathIs('/target-kesehatan')
                 ->assertVisible('.stat-cards')
                 ->assertVisible('.content-grid')
                 ->screenshot('tc01-halaman-target-kesehatan-tampil');
        });
    }

    /** @test TC-02: Menampilkan ringkasan target kesehatan — total, on track, perlu perhatian, tercapai tampil (Positive) */
    public function test_tc02_menampilkan_ringkasan_target_kesehatan(): void
    {
        $this->buatTarget(['aktivitas_dilakukan' => 6]); // 60% → on-track
        $this->buatTarget(['aktivitas_dilakukan' => 3]); // 30% → perlu-perhatian
        $this->buatTarget(['aktivitas_dilakukan' => 10]); // 100% → tercapai

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('On Track')
                 ->assertSee('Perlu Perhatian')
                 ->assertSee('Tercapai')
                 ->assertPresent('.stat-card')
                 ->screenshot('tc02-ringkasan-target-tampil');
        });
    }

    /** @test TC-03: Membuka form pembuatan target baru — form input tampil (Positive) */
    public function test_tc03_membuka_form_pembuatan_target_baru(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/target-kesehatan/create')
                 ->waitFor('.form-card', 15)
                 ->assertVisible('.form-card')
                 ->assertSee('Buat Target Kesehatan Baru')
                 ->assertPresent('input[name="nama"]')
                 ->assertPresent('textarea[name="deskripsi"]')
                 ->screenshot('tc03-form-buat-target-tampil');
        });
    }

    /** @test TC-04: Mengisi data target kesehatan — sistem menerima seluruh data input (Positive) */
    public function test_tc04_mengisi_data_target_kesehatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/target-kesehatan/create')
                 ->waitFor('.form-card', 15)
                 ->type('input[name="nama"]', 'Target Dusk Isi')
                 ->type('textarea[name="deskripsi"]', 'Deskripsi pengisian form dusk')
                 ->type('input[name="target_aktivitas"]', '10')
                 ->type('input[name="aktivitas_dilakukan"]', '3')
                 ->select('select[name="satuan"]', 'kali')
                 ->type('input[name="tanggal_target"]', Carbon::today()->addMonth()->format('Y-m-d'))
                 ->assertInputValue('input[name="nama"]', 'Target Dusk Isi')
                 ->assertInputValue('input[name="target_aktivitas"]', '10')
                 ->screenshot('tc04-data-target-berhasil-diisi');
        });
    }

    /** @test TC-05: Menyimpan target kesehatan baru — target tersimpan dan tampil di daftar (Positive) */
    public function test_tc05_menyimpan_target_kesehatan_baru(): void
    {
        $tanggal = Carbon::today()->addMonth()->format('Y-m-d');

        $this->browse(function (Browser $browser) use ($tanggal) {
            $this->login($browser)
                 ->visit('/target-kesehatan/create')
                 ->waitFor('.form-card', 15)
                 ->type('input[name="nama"]', 'Target Dusk Simpan')
                 ->type('textarea[name="deskripsi"]', 'Deskripsi target simpan')
                 ->tap(fn($b) => $b->script("document.querySelector('input[name=\"icon\"]').click()"))
                 ->type('input[name="target_aktivitas"]', '8')
                 ->type('input[name="aktivitas_dilakukan"]', '2')
                 ->select('select[name="satuan"]', 'kali')
                 ->tap(fn($b) => $b->script("document.querySelector('input[name=\"tanggal_target\"]').value = '{$tanggal}'"))
                 ->click('.btn-simpan')
                 ->waitFor('.stat-cards', 15)
                 ->assertPathIs('/target-kesehatan')
                 ->assertSee('Target Dusk Simpan')
                 ->screenshot('tc05-target-berhasil-disimpan');
        });
    }

    /** @test TC-06: Validasi form target kesehatan kosong — pesan validasi tampil (Negative) */
    public function test_tc06_validasi_form_target_kesehatan_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/target-kesehatan/create')
                 ->waitFor('.form-card', 15)
                 // Isi semua field kecuali nama agar server-side validation mengembalikan form-error
                 ->type('input[name="target_aktivitas"]', '5')
                 ->select('select[name="satuan"]', 'kali')
                 ->tap(fn($b) => $b->script("document.querySelector('input[name=\"tanggal_target\"]').value = '" . Carbon::today()->addMonth()->format('Y-m-d') . "'"))
                 // Bypass HTML5 required pada nama agar form bisa disubmit tanpa nama
                 ->tap(fn($b) => $b->script("document.querySelector('input[name=\"nama\"]').removeAttribute('required')"))
                 ->click('.btn-simpan')
                 ->waitFor('.form-error', 10)
                 ->assertVisible('.form-error')
                 ->screenshot('tc06-validasi-form-kosong');
        });
    }

    /** @test TC-07: Menampilkan daftar target kesehatan — daftar tampil lengkap (Positive) */
    public function test_tc07_menampilkan_daftar_target_kesehatan(): void
    {
        $this->buatTarget(['nama' => 'Target Daftar Dusk']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertVisible('.target-list')
                 ->assertPresent('.target-card')
                 ->assertSee('Target Daftar Dusk')
                 ->assertPresent('.status-badge')
                 ->assertPresent('.target-date')
                 ->screenshot('tc07-daftar-target-tampil');
        });
    }

    /** @test TC-08: Menghitung progress target kesehatan — progress dihitung dengan benar (Positive) */
    public function test_tc08_menghitung_progress_target_kesehatan(): void
    {
        // 4/10 = 40% → perlu-perhatian
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 4]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertVisible('.progress-bar')
                 ->assertVisible('.progress-pct')
                 ->assertSee('40%')
                 ->screenshot('tc08-progress-target-dihitung');
        });
    }

    /** @test TC-09: Menampilkan progress bar target — progress bar sesuai persentase (Positive) */
    public function test_tc09_menampilkan_progress_bar_target(): void
    {
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 7]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertVisible('.progress-wrap')
                 ->assertPresent('.progress-fill')
                 ->assertVisible('.progress-pct')
                 ->screenshot('tc09-progress-bar-tampil');
        });
    }

    /** @test TC-10: Menampilkan status "On Track" — label On Track tampil pada target (Positive) */
    public function test_tc10_menampilkan_status_on_track(): void
    {
        // 6/10 = 60% → on-track
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 6]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('On Track')
                 ->assertPresent('.badge-on-track')
                 ->screenshot('tc10-status-on-track-tampil');
        });
    }

    /** @test TC-11: Menampilkan status "Perlu Perhatian" — label Perlu Perhatian tampil (Positive) */
    public function test_tc11_menampilkan_status_perlu_perhatian(): void
    {
        // 3/10 = 30% → perlu-perhatian
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 3]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('Perlu Perhatian')
                 ->assertPresent('.badge-perlu-perhatian')
                 ->screenshot('tc11-status-perlu-perhatian-tampil');
        });
    }

    /** @test TC-12: Menampilkan status "Tercapai" — label Tercapai dan indikator keberhasilan tampil (Positive) */
    public function test_tc12_menampilkan_status_tercapai(): void
    {
        // 10/10 = 100% → tercapai
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 10]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('Tercapai')
                 ->assertPresent('.badge-tercapai')
                 ->assertSee('Target tercapai!')
                 ->screenshot('tc12-status-tercapai-tampil');
        });
    }

    /** @test TC-13: Menampilkan detail target kesehatan — detail tampil saat buka edit (Positive) */
    public function test_tc13_menampilkan_detail_target_kesehatan(): void
    {
        $target = $this->buatTarget(['nama' => 'Target Detail Dusk', 'deskripsi' => 'Detail deskripsi dusk']);

        $this->browse(function (Browser $browser) use ($target) {
            $this->login($browser)
                 ->visit("/target-kesehatan/{$target->id}/edit")
                 ->waitFor('.form-card', 15)
                 ->assertInputValue('input[name="nama"]', 'Target Detail Dusk')
                 ->assertPresent('textarea[name="deskripsi"]')
                 ->assertPresent('input[name="target_aktivitas"]')
                 ->assertPresent('input[name="tanggal_target"]')
                 ->screenshot('tc13-detail-target-tampil');
        });
    }

    /** @test TC-14: Menampilkan pencapaian terbaru — notifikasi pencapaian tampil di panel (Positive) */
    public function test_tc14_menampilkan_pencapaian_terbaru(): void
    {
        // tercapai_at harus diisi agar muncul di panel pencapaian terbaru
        $this->buatTarget([
            'target_aktivitas'    => 5,
            'aktivitas_dilakukan' => 5,
            'tercapai_at'         => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('Pencapaian Terbaru')
                 ->assertVisible('.pencapaian-card')
                 ->assertSee('Target Tercapai!')
                 ->screenshot('tc14-pencapaian-terbaru-tampil');
        });
    }

    /** @test TC-15: Memfilter target berdasarkan kategori status — daftar diperbarui sesuai filter (Positive) */
    public function test_tc15_memfilter_target_berdasarkan_kategori_status(): void
    {
        $this->buatTarget(['aktivitas_dilakukan' => 6]); // on-track
        $this->buatTarget(['aktivitas_dilakukan' => 2]); // perlu-perhatian

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->clickLink('On Track')
                 ->waitFor('.stat-cards', 10)
                 ->assertQueryStringHas('filter', 'on-track')
                 ->assertPresent('.filter-tab.active')
                 ->screenshot('tc15-filter-on-track-aktif');
        });
    }

    /** @test TC-16: Menampilkan notifikasi target hampir terlambat — status perlu perhatian tampil (Positive) */
    public function test_tc16_menampilkan_notifikasi_target_hampir_terlambat(): void
    {
        // Deadline dekat + progress rendah → perlu-perhatian
        $this->buatTarget([
            'aktivitas_dilakukan' => 1,
            'target_aktivitas'    => 10,
            'tanggal_target'      => Carbon::today()->addDays(3)->format('Y-m-d'),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('Perlu Perhatian')
                 ->assertPresent('.badge-perlu-perhatian')
                 ->screenshot('tc16-notifikasi-target-hampir-terlambat');
        });
    }

    /** @test TC-17: Menampilkan target yang telah selesai — target tampil di kategori Tercapai (Positive) */
    public function test_tc17_menampilkan_target_yang_telah_selesai(): void
    {
        $this->buatTarget(['nama' => 'Target Selesai Dusk', 'aktivitas_dilakukan' => 10, 'target_aktivitas' => 10]);

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/target-kesehatan?filter=tercapai')
                 ->waitFor('.stat-cards', 15)
                 ->assertSee('Target Selesai Dusk')
                 ->assertPresent('.badge-tercapai')
                 ->screenshot('tc17-target-selesai-tampil-di-tercapai');
        });
    }

    /** @test TC-18: Menyimpan data target ke database — data tersimpan tanpa error (Positive) */
    public function test_tc18_menyimpan_data_target_ke_database(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/target-kesehatan/create')
                 ->waitFor('.form-card', 15)
                 ->type('input[name="nama"]', 'Target DB Dusk')
                 ->type('textarea[name="deskripsi"]', 'Simpan ke DB')
                 ->tap(fn($b) => $b->script("document.querySelector('input[name=\"icon\"]').click()"))
                 ->type('input[name="target_aktivitas"]', '5')
                 ->type('input[name="aktivitas_dilakukan"]', '1')
                 ->select('select[name="satuan"]', 'kali')
                 ->tap(fn($b) => $b->script("document.querySelector('input[name=\"tanggal_target\"]').value = '" . Carbon::today()->addMonth()->format('Y-m-d') . "'"))
                 ->click('.btn-simpan')
                 ->waitFor('.stat-cards', 15)
                 ->assertPathIs('/target-kesehatan')
                 ->assertSee('Target DB Dusk')
                 ->screenshot('tc18-data-target-tersimpan-ke-db');
        });

        $this->assertDatabaseHas('target_kesehatan', [
            'user_id' => $this->testUser->id,
            'nama'    => 'Target DB Dusk',
        ]);
    }

    /** @test TC-19: Menampilkan informasi terakhir diperbarui — timestamp tampil (Positive) */
    public function test_tc19_menampilkan_informasi_terakhir_diperbarui(): void
    {
        $this->buatTarget();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertPresent('.data-timestamp')
                 ->screenshot('tc19-informasi-terakhir-diperbarui-tampil');
        });
    }

    /** @test TC-20: Menampilkan pesan belum memiliki target kesehatan — empty state tampil (Negative) */
    public function test_tc20_menampilkan_pesan_belum_memiliki_target(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertVisible('.empty-state')
                 ->assertSee('Buat Target Baru')
                 ->assertMissing('.target-card')
                 ->screenshot('tc20-empty-state-belum-ada-target');
        });
    }

    /** @test TC-21: Menampilkan pesan progress belum tersedia — progress 0% tampil (Negative) */
    public function test_tc21_menampilkan_pesan_progress_belum_tersedia(): void
    {
        // aktivitas_dilakukan = 0 → progress 0%
        $this->buatTarget(['aktivitas_dilakukan' => 0]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaTarget($browser)
                 ->assertSee('0%')
                 ->assertPresent('.progress-fill')
                 ->screenshot('tc21-progress-nol-persen-tampil');
        });
    }

    /** @test TC-22: Menampilkan pesan gagal memuat data target — redirect login jika belum login (Negative) */
    public function test_tc22_menampilkan_pesan_gagal_memuat_data_target(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/target-kesehatan')
                    ->pause(1000)
                    ->assertPathIs('/login')
                    ->screenshot('tc22-redirect-login-jika-belum-login');
        });
    }

    /** @test TC-23: Menampilkan loading state halaman target kesehatan — halaman dimuat dengan benar (Positive) */
    public function test_tc23_menampilkan_loading_state(): void
    {
        $this->buatTarget();

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/target-kesehatan')
                 ->waitFor('.stat-cards', 15)
                 ->assertVisible('.stat-cards')
                 ->assertPresent('.content-grid')
                 ->screenshot('tc23-loading-state-selesai');
        });
    }
}
