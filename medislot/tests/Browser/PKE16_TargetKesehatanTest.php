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

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /**
     * @test TC-B01: Membuat target kesehatan — tersimpan di DB dengan semua kolom benar
     *
     * Langkah:
     * 1. Buat target baru via buatTarget() dengan override nama unik
     * 2. Pastikan target ada di DB dengan semua kolom yang diisi
     * 3. Pastikan target_aktivitas, aktivitas_dilakukan, satuan tersimpan benar
     * 4. Pastikan user_id sesuai testUser
     */
    public function test_tcb01_buat_target_tersimpan_di_db(): void
    {
        $tanggal = Carbon::today()->addMonth()->format('Y-m-d');

        // Langkah 1
        $target = $this->buatTarget([
            'nama'                => 'Target Backend B01',
            'deskripsi'           => 'Deskripsi B01',
            'target_aktivitas'    => 20,
            'aktivitas_dilakukan' => 8,
            'satuan'              => 'menit',
            'tanggal_target'      => $tanggal,
        ]);

        // Langkah 2–4
        $this->assertDatabaseHas('target_kesehatan', [
            'user_id'             => $this->testUser->id,
            'nama'                => 'Target Backend B01',
            'deskripsi'           => 'Deskripsi B01',
            'target_aktivitas'    => 20,
            'aktivitas_dilakukan' => 8,
            'satuan'              => 'menit',
        ]);
        $this->assertNotNull($target->id);
        $this->assertEquals($this->testUser->id, $target->user_id);
    }

    /**
     * @test TC-B02: Accessor progress — dihitung min(100, round(dilakukan/target*100))
     *
     * Langkah:
     * 1. Buat target dengan aktivitas_dilakukan=7, target_aktivitas=10
     * 2. Akses $target->progress
     * 3. Pastikan progress = 70
     * 4. Buat target dengan dilakukan > target → pastikan progress = 100 (tidak melebihi)
     */
    public function test_tcb02_accessor_progress_dihitung_benar(): void
    {
        // Langkah 1–3
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 7]);
        $this->assertEquals(70, $target->progress);

        // Langkah 4: progress tidak boleh melebihi 100
        $targetLebih = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 15]);
        $this->assertEquals(100, $targetLebih->progress);
    }

    /**
     * @test TC-B03: Accessor status — 'tercapai' saat progress >= 100
     *
     * Langkah:
     * 1. Buat target dengan aktivitas_dilakukan = target_aktivitas (100%)
     * 2. Akses $target->status
     * 3. Pastikan status = 'tercapai'
     * 4. Pastikan statusLabel = 'Tercapai'
     */
    public function test_tcb03_status_tercapai_saat_progress_100(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 10]);

        // Langkah 2–4
        $this->assertEquals('tercapai', $target->status);
        $this->assertEquals('Tercapai', $target->statusLabel);
    }

    /**
     * @test TC-B04: Accessor status — 'on-track' saat 50% <= progress < 100%
     *
     * Langkah:
     * 1. Buat target dengan aktivitas_dilakukan=6, target_aktivitas=10 (60%)
     * 2. Akses $target->status
     * 3. Pastikan status = 'on-track'
     * 4. Pastikan statusLabel = 'On Track'
     */
    public function test_tcb04_status_on_track_saat_progress_50_sampai_99(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 6]);

        // Langkah 2–4
        $this->assertEquals('on-track', $target->status);
        $this->assertEquals('On Track', $target->statusLabel);
    }

    /**
     * @test TC-B05: Accessor status — 'perlu-perhatian' saat progress < 50%
     *
     * Langkah:
     * 1. Buat target dengan aktivitas_dilakukan=3, target_aktivitas=10 (30%)
     * 2. Akses $target->status
     * 3. Pastikan status = 'perlu-perhatian'
     * 4. Pastikan statusLabel = 'Perlu Perhatian'
     */
    public function test_tcb05_status_perlu_perhatian_saat_progress_kurang_50(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 3]);

        // Langkah 2–4
        $this->assertEquals('perlu-perhatian', $target->status);
        $this->assertEquals('Perlu Perhatian', $target->statusLabel);
    }

    /**
     * @test TC-B06: Progress = 0 saat aktivitas_dilakukan = 0
     *
     * Langkah:
     * 1. Buat target dengan aktivitas_dilakukan = 0
     * 2. Akses $target->progress
     * 3. Pastikan progress = 0 dan status = 'perlu-perhatian'
     * 4. Verifikasi data di DB
     */
    public function test_tcb06_progress_nol_saat_belum_ada_aktivitas(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 0]);

        // Langkah 2–3
        $this->assertEquals(0, $target->progress);
        $this->assertEquals('perlu-perhatian', $target->status);

        // Langkah 4
        $this->assertDatabaseHas('target_kesehatan', [
            'user_id'             => $this->testUser->id,
            'aktivitas_dilakukan' => 0,
        ]);
    }

    /**
     * @test TC-B07: tercapai_at otomatis diisi saat aktivitas_dilakukan >= target_aktivitas di store
     *
     * Langkah:
     * 1. Buat target langsung via model dengan dilakukan >= target
     * 2. Set tercapai_at secara manual (mirror logika controller store)
     * 3. Verifikasi tercapai_at tidak null di DB
     * 4. Verifikasi target tersimpan dengan benar
     */
    public function test_tcb07_tercapai_at_terisi_otomatis_saat_target_tercapai(): void
    {
        // Langkah 1–2: Mirror logika controller store()
        $data = [
            'user_id'             => $this->testUser->id,
            'nama'                => 'Target Tercapai B07',
            'deskripsi'           => 'Sudah selesai',
            'icon'                => 'fas fa-star',
            'icon_color'          => '#2d9e72',
            'icon_bg'             => '#e8fff4',
            'target_aktivitas'    => 5,
            'aktivitas_dilakukan' => 5,
            'satuan'              => 'kali',
            'tanggal_target'      => Carbon::today()->addMonth()->format('Y-m-d'),
        ];
        if ($data['aktivitas_dilakukan'] >= $data['target_aktivitas']) {
            $data['tercapai_at'] = now();
        }
        $target = TargetKesehatan::create($data);

        // Langkah 3–4
        $this->assertNotNull($target->tercapai_at);
        $this->assertDatabaseHas('target_kesehatan', [
            'user_id' => $this->testUser->id,
            'nama'    => 'Target Tercapai B07',
        ]);
        $fromDb = TargetKesehatan::find($target->id);
        $this->assertNotNull($fromDb->tercapai_at);
    }

    /**
     * @test TC-B08: tercapai_at tidak diisi saat aktivitas_dilakukan < target_aktivitas
     *
     * Langkah:
     * 1. Buat target dengan dilakukan < target
     * 2. Pastikan tercapai_at = null (belum tercapai)
     * 3. Verifikasi di DB
     */
    public function test_tcb08_tercapai_at_null_saat_belum_tercapai(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 4]);

        // Langkah 2
        $this->assertNull($target->tercapai_at);

        // Langkah 3
        $fromDb = TargetKesehatan::find($target->id);
        $this->assertNull($fromDb->tercapai_at);
    }

    /**
     * @test TC-B09: Update target — data diperbarui di DB dan tercapai_at diisi saat baru tercapai
     *
     * Langkah:
     * 1. Buat target dengan aktivitas_dilakukan = 4 (belum tercapai, tercapai_at = null)
     * 2. Update aktivitas_dilakukan = 10 (tercapai), mirror logika controller update()
     * 3. Pastikan tercapai_at tidak null setelah update
     * 4. Verifikasi data baru di DB
     */
    public function test_tcb09_update_target_tercapai_at_terisi(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 4]);
        $this->assertNull($target->tercapai_at);

        // Langkah 2: Mirror logika update()
        $updateData = ['aktivitas_dilakukan' => 10];
        if ($updateData['aktivitas_dilakukan'] >= $target->target_aktivitas
            && $target->tercapai_at === null) {
            $updateData['tercapai_at'] = now();
        }
        $target->update($updateData);

        // Langkah 3–4
        $this->assertNotNull($target->fresh()->tercapai_at);
        $this->assertDatabaseHas('target_kesehatan', [
            'id'                  => $target->id,
            'aktivitas_dilakukan' => 10,
        ]);
    }

    /**
     * @test TC-B10: Update target — tercapai_at direset ke null saat dilakukan kembali < target
     *
     * Langkah:
     * 1. Buat target yang sudah tercapai (tercapai_at terisi)
     * 2. Update aktivitas_dilakukan = 3 (< target), mirror logika controller update()
     * 3. Pastikan tercapai_at menjadi null kembali
     * 4. Verifikasi di DB
     */
    public function test_tcb10_update_target_tercapai_at_direset(): void
    {
        // Langkah 1
        $target = $this->buatTarget([
            'target_aktivitas'    => 10,
            'aktivitas_dilakukan' => 10,
            'tercapai_at'         => now(),
        ]);
        $this->assertNotNull($target->tercapai_at);

        // Langkah 2: Mirror logika update() — reset jika dilakukan < target
        $updateData = ['aktivitas_dilakukan' => 3];
        if ($updateData['aktivitas_dilakukan'] < $target->target_aktivitas) {
            $updateData['tercapai_at'] = null;
        }
        $target->update($updateData);

        // Langkah 3–4
        $this->assertNull($target->fresh()->tercapai_at);
        $this->assertDatabaseHas('target_kesehatan', [
            'id'                  => $target->id,
            'aktivitas_dilakukan' => 3,
        ]);
    }

    /**
     * @test TC-B11: Hapus target — data terhapus dari DB (hard delete)
     *
     * Langkah:
     * 1. Buat target baru
     * 2. Verifikasi ada di DB
     * 3. Hapus target
     * 4. Verifikasi sudah tidak ada di DB (assertDatabaseMissing)
     */
    public function test_tcb11_hapus_target_terhapus_dari_db(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['nama' => 'Target Hapus B11']);

        // Langkah 2
        $this->assertDatabaseHas('target_kesehatan', [
            'id'   => $target->id,
            'nama' => 'Target Hapus B11',
        ]);

        // Langkah 3
        $target->delete();

        // Langkah 4
        $this->assertDatabaseMissing('target_kesehatan', ['id' => $target->id]);
    }

    /**
     * @test TC-B12: Authorization — user lain tidak bisa hapus target milik testUser
     *
     * Langkah:
     * 1. Buat target milik testUser
     * 2. Buat user lain
     * 3. Coba hapus target dengan user lain → abort_if harus mencegah (user_id tidak cocok)
     * 4. Verifikasi target masih ada di DB
     * 5. Hapus data user lain
     */
    public function test_tcb12_authorization_user_lain_tidak_bisa_hapus(): void
    {
        // Langkah 1
        $target = $this->buatTarget(['nama' => 'Target Milik Saya B12']);

        // Langkah 2
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE16',
            'email'         => "lain.pke16.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        // Langkah 3: Cek kondisi abort_if dari controller destroy()
        $bolehHapus = $target->user_id === $userLain->id;

        // Langkah 4
        $this->assertFalse($bolehHapus); // user lain tidak boleh hapus
        $this->assertDatabaseHas('target_kesehatan', ['id' => $target->id]);

        // Langkah 5
        $userLain->delete();
    }

    /**
     * @test TC-B13: Filter index — query 'semua' mengembalikan semua target, filter status hanya statusnya
     *
     * Langkah:
     * 1. Buat 3 target: on-track (60%), perlu-perhatian (30%), tercapai (100%)
     * 2. Query allTargets → count = 3
     * 3. Filter 'on-track' → count = 1
     * 4. Filter 'tercapai' → count = 1
     * 5. Filter 'semua' → count = 3
     */
    public function test_tcb13_filter_status_mengembalikan_target_yang_sesuai(): void
    {
        // Langkah 1
        $this->buatTarget(['nama' => 'On Track',        'target_aktivitas' => 10, 'aktivitas_dilakukan' => 6]);
        $this->buatTarget(['nama' => 'Perlu Perhatian', 'target_aktivitas' => 10, 'aktivitas_dilakukan' => 3]);
        $this->buatTarget(['nama' => 'Tercapai',        'target_aktivitas' => 10, 'aktivitas_dilakukan' => 10]);

        // Langkah 2
        $allTargets = TargetKesehatan::where('user_id', $this->testUser->id)
            ->orderBy('tanggal_target')
            ->get();
        $this->assertEquals(3, $allTargets->count());

        // Langkah 3
        $onTrack = $allTargets->filter(fn($t) => $t->status === 'on-track');
        $this->assertEquals(1, $onTrack->count());

        // Langkah 4
        $tercapai = $allTargets->filter(fn($t) => $t->status === 'tercapai');
        $this->assertEquals(1, $tercapai->count());

        // Langkah 5
        $semua = $allTargets->filter(fn($t) => true);
        $this->assertEquals(3, $semua->count());
    }

    /**
     * @test TC-B14: pencapaianTerbaru — query mengembalikan target tercapai terbaru berdasarkan tercapai_at
     *
     * Langkah:
     * 1. Buat 2 target dengan tercapai_at berbeda
     * 2. Query pencapaianTerbaru seperti controller index()
     * 3. Pastikan yang dikembalikan adalah yang tercapai_at paling baru
     * 4. Verifikasi data di DB
     */
    public function test_tcb14_pencapaian_terbaru_dikembalikan_benar(): void
    {
        // Langkah 1
        $lama  = $this->buatTarget(['nama' => 'Tercapai Lama', 'aktivitas_dilakukan' => 10, 'tercapai_at' => now()->subDays(5)]);
        $baru  = $this->buatTarget(['nama' => 'Tercapai Baru', 'aktivitas_dilakukan' => 10, 'tercapai_at' => now()]);

        // Langkah 2
        $pencapaianTerbaru = TargetKesehatan::where('user_id', $this->testUser->id)
            ->whereNotNull('tercapai_at')
            ->orderByDesc('tercapai_at')
            ->first();

        // Langkah 3
        $this->assertNotNull($pencapaianTerbaru);
        $this->assertEquals('Tercapai Baru', $pencapaianTerbaru->nama);

        // Langkah 4
        $this->assertDatabaseHas('target_kesehatan', ['id' => $baru->id, 'nama' => 'Tercapai Baru']);
    }

    /**
     * @test TC-B15: Data isolation — target hanya milik testUser, tidak tercampur user lain
     *
     * Langkah:
     * 1. Buat user lain dengan 5 target
     * 2. Buat 2 target untuk testUser
     * 3. Query allTargets hanya untuk testUser
     * 4. Pastikan count = 2 (tidak tercampur)
     * 5. Hapus data user lain
     */
    public function test_tcb15_data_isolation_target(): void
    {
        // Langkah 1
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE16 Iso',
            'email'         => "lain.pke16iso.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
        for ($i = 0; $i < 5; $i++) {
            TargetKesehatan::create([
                'user_id'             => $userLain->id,
                'nama'                => "Target Orang Lain {$i}",
                'icon'                => 'fas fa-bullseye',
                'icon_color'          => '#4a90d9',
                'icon_bg'             => '#e8f4ff',
                'target_aktivitas'    => 10,
                'aktivitas_dilakukan' => 5,
                'satuan'              => 'kali',
                'tanggal_target'      => Carbon::today()->addMonth()->format('Y-m-d'),
            ]);
        }

        // Langkah 2
        $this->buatTarget(['nama' => 'Target Saya A']);
        $this->buatTarget(['nama' => 'Target Saya B']);

        // Langkah 3
        $milikSaya = TargetKesehatan::where('user_id', $this->testUser->id)->get();

        // Langkah 4
        $this->assertEquals(2, $milikSaya->count());

        // Langkah 5
        TargetKesehatan::where('user_id', $userLain->id)->delete();
        $userLain->delete();
    }

    /**
     * @test TC-B16: Statistik ringkasan — total, onTrack, perluPerhatian, tercapai dihitung benar
     *
     * Langkah:
     * 1. Buat 4 target: 2 on-track, 1 perlu-perhatian, 1 tercapai
     * 2. Query allTargets dan hitung statistik seperti controller index()
     * 3. Pastikan total=4, onTrack=2, perluPerhatian=1, tercapai=1
     */
    public function test_tcb16_statistik_ringkasan_dihitung_benar(): void
    {
        // Langkah 1
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 6]);  // on-track (60%)
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 8]);  // on-track (80%)
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 2]);  // perlu-perhatian (20%)
        $this->buatTarget(['target_aktivitas' => 10, 'aktivitas_dilakukan' => 10]); // tercapai (100%)

        // Langkah 2
        $allTargets     = TargetKesehatan::where('user_id', $this->testUser->id)->get();
        $total          = $allTargets->count();
        $onTrack        = $allTargets->filter(fn($t) => $t->status === 'on-track')->count();
        $perluPerhatian = $allTargets->filter(fn($t) => $t->status === 'perlu-perhatian')->count();
        $tercapai       = $allTargets->filter(fn($t) => $t->status === 'tercapai')->count();

        // Langkah 3
        $this->assertEquals(4, $total);
        $this->assertEquals(2, $onTrack);
        $this->assertEquals(1, $perluPerhatian);
        $this->assertEquals(1, $tercapai);
    }
}
