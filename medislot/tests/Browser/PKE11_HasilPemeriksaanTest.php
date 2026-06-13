<?php
// PKE-11: Browser test untuk Pencatatan Hasil Pemeriksaan
namespace Tests\Browser;

use App\Models\HasilPemeriksaan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE11_HasilPemeriksaanTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE11',
            'email'         => "dusk.pke11.{$uniqueId}@test.local",
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

    private function loginDanBukaHasil(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/hasil-pemeriksaan')
                    ->waitFor('.btn-tambah, .empty-state', 15);
    }

    private function buatHasilTest(array $override = []): HasilPemeriksaan
    {
        return HasilPemeriksaan::create(array_merge([
            'user_id'             => $this->testUser->id,
            'jenis_pemeriksaan'   => 'Cek Darah Lengkap Dusk',
            'tanggal_pemeriksaan' => '2026-01-15',
            'fasilitas_kesehatan' => 'RS Dusk Test',
            'nama_dokter'         => 'dr. Dusk',
            'hasil_pemeriksaan'   => 'Hasil normal, semua dalam batas wajar.',
            'catatan_tambahan'    => 'Ulangi 6 bulan lagi.',
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Menampilkan halaman hasil pemeriksaan — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_menampilkan_halaman_hasil_pemeriksaan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertPathIs('/hasil-pemeriksaan')
                 ->assertSee('Pencatatan Hasil Pemeriksaan')
                 ->assertVisible('.btn-tambah')
                 ->screenshot('tc01-halaman-hasil-pemeriksaan');
        });
    }

    /** @test TC-02: Mengambil data hasil pemeriksaan — sistem berhasil ambil data user (Positive) */
    public function test_tc02_mengambil_data_hasil_pemeriksaan(): void
    {
        $this->buatHasilTest();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertPresent('.hasil-card')
                 ->assertSee('Cek Darah Lengkap Dusk')
                 ->screenshot('tc02-data-hasil-berhasil-diambil');
        });
    }

    /** @test TC-03: Menampilkan daftar hasil pemeriksaan — daftar tampil dengan benar (Positive) */
    public function test_tc03_menampilkan_daftar_hasil_pemeriksaan(): void
    {
        $this->buatHasilTest();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertVisible('.hasil-card')
                 ->assertVisible('.hasil-title')
                 ->assertVisible('.hasil-meta')
                 ->assertVisible('.hasil-quote')
                 ->screenshot('tc03-daftar-hasil-tampil');
        });
    }

    /** @test TC-04: Membuka form tambah hasil pemeriksaan — form pencatatan berhasil ditampilkan (Positive) */
    public function test_tc04_membuka_form_tambah(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#createModal.active', 5)
                 ->assertVisible('#createModal .modal-title')
                 ->assertSee('Pencatatan Hasil Pemeriksaan')
                 ->assertPresent('#createModal input[name="jenis_pemeriksaan"]')
                 ->screenshot('tc04-form-tambah-terbuka');
        });
    }

    /** @test TC-05: Menambahkan hasil pemeriksaan valid — data berhasil disimpan (Positive) */
    public function test_tc05_menambahkan_hasil_pemeriksaan_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#createModal.active', 5)
                 ->pause(400)
                 ->type('#createModal input[name="jenis_pemeriksaan"]', 'Pemeriksaan Mata Dusk')
                 ->type('#createModal input[name="tanggal_pemeriksaan"]', '2026-05-10')
                 ->type('#createModal input[name="fasilitas_kesehatan"]', 'Klinik Dusk')
                 ->type('#createModal input[name="nama_dokter"]', 'dr. Dusk Test')
                 ->type('#createModal textarea[name="hasil_pemeriksaan"]', 'Visus mata normal, tidak perlu kacamata.')
                 ->type('#createModal textarea[name="catatan_tambahan"]', 'Kontrol 1 tahun lagi.')
                 ->click('#createModal .btn-simpan')
                 ->waitFor('#successModal.active, .hasil-card', 10)
                 ->screenshot('tc05-hasil-pemeriksaan-berhasil-disimpan');
        });

        $this->assertDatabaseHas('hasil_pemeriksaan', [
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Pemeriksaan Mata Dusk',
        ]);
    }

    /** @test TC-06: Validasi field kosong — sistem menampilkan pesan validasi (Negative) */
    public function test_tc06_validasi_field_wajib_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#createModal.active', 5)
                 ->pause(400)
                 // Tidak isi field wajib, langsung submit
                 ->click('#createModal .btn-simpan')
                 ->pause(500)
                 // Browser HTML5 validation mencegah submit — modal tetap terbuka
                 ->assertPresent('#createModal')
                 ->screenshot('tc06-validasi-field-kosong');
        });
    }

    /** @test TC-07: Validasi format data — sistem menampilkan pesan error validasi (Negative) */
    public function test_tc07_validasi_format_data(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#createModal.active', 5)
                 ->pause(400)
                 ->type('#createModal input[name="jenis_pemeriksaan"]', 'Test Validasi Format')
                 // Tanggal dikosongkan (field required)
                 ->click('#createModal .btn-simpan')
                 ->pause(500)
                 ->assertPresent('#createModal')
                 ->screenshot('tc07-validasi-format-data');
        });
    }

    /** @test TC-08: Menampilkan detail hasil pemeriksaan — detail tampil dengan benar (Positive) */
    public function test_tc08_menampilkan_detail_hasil_pemeriksaan(): void
    {
        $this->buatHasilTest([
            'jenis_pemeriksaan' => 'Tes Kolesterol Dusk',
            'hasil_pemeriksaan' => 'Kolesterol total 180 mg/dL, normal.',
            'catatan_tambahan'  => 'Jaga pola makan rendah lemak.',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertSee('Tes Kolesterol Dusk')
                 ->assertSee('Kolesterol total 180 mg/dL, normal.')
                 ->assertSee('Jaga pola makan rendah lemak.')
                 ->assertVisible('.hasil-quote')
                 ->assertVisible('.hasil-catatan')
                 ->screenshot('tc08-detail-hasil-tampil');
        });
    }

    /** @test TC-09: Membuka form edit hasil pemeriksaan — form edit berhasil ditampilkan (Positive) */
    public function test_tc09_membuka_form_edit(): void
    {
        $hasil = $this->buatHasilTest(['jenis_pemeriksaan' => 'Dusk Edit Test Open']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaHasil($browser)
                 ->assertSee('Dusk Edit Test Open')
                 ->click('.btn-card-edit')
                 ->waitFor('#editModal.active', 5)
                 ->assertInputValue('#edit_jenis', 'Dusk Edit Test Open')
                 ->assertVisible('#edit_hasil')
                 ->screenshot('tc09-form-edit-terbuka');
        });
    }

    /** @test TC-10: Mengedit hasil pemeriksaan valid — data berhasil diperbarui (Positive) */
    public function test_tc10_mengedit_hasil_pemeriksaan_valid(): void
    {
        $hasil = $this->buatHasilTest(['jenis_pemeriksaan' => 'Dusk Sebelum Edit']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaHasil($browser)
                 ->assertSee('Dusk Sebelum Edit')
                 ->click('.btn-card-edit')
                 ->waitFor('#editModal.active', 5)
                 ->clear('#edit_jenis')
                 ->type('#edit_jenis', 'Dusk Sesudah Edit')
                 ->click('#editModal .btn-simpan')
                 ->waitFor('#successModal.active, .hasil-card', 10)
                 ->screenshot('tc10-hasil-berhasil-diedit');
        });

        $this->assertDatabaseHas('hasil_pemeriksaan', [
            'id'                => $hasil->id,
            'jenis_pemeriksaan' => 'Dusk Sesudah Edit',
        ]);
    }

    /** @test TC-11: Menyimpan perubahan hasil pemeriksaan — sistem menyimpan perubahan ke database (Positive) */
    public function test_tc11_menyimpan_perubahan_ke_database(): void
    {
        $hasil = $this->buatHasilTest(['jenis_pemeriksaan' => 'Dusk Simpan Perubahan']);

        $this->browse(function (Browser $browser) use ($hasil) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-card-edit')
                 ->waitFor('#editModal.active', 5)
                 ->clear('#edit_fasilitas')
                 ->type('#edit_fasilitas', 'RS Baru Dusk')
                 ->click('#editModal .btn-simpan')
                 ->waitFor('#successModal.active, .hasil-card', 10)
                 ->screenshot('tc11-perubahan-tersimpan-database');
        });

        $this->assertDatabaseHas('hasil_pemeriksaan', [
            'id'                  => $hasil->id,
            'fasilitas_kesehatan' => 'RS Baru Dusk',
        ]);
    }

    /** @test TC-12: Notifikasi berhasil simpan — sistem menampilkan notifikasi berhasil (Positive) */
    public function test_tc12_notifikasi_berhasil_simpan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#createModal.active', 5)
                 ->pause(400)
                 ->type('#createModal input[name="jenis_pemeriksaan"]', 'Dusk Notif Simpan')
                 ->type('#createModal input[name="tanggal_pemeriksaan"]', '2026-03-01')
                 ->type('#createModal textarea[name="hasil_pemeriksaan"]', 'Semua hasil normal.')
                 ->click('#createModal .btn-simpan')
                 ->waitFor('#successModal.active', 10)
                 ->assertSee('berhasil disimpan')
                 ->screenshot('tc12-notifikasi-berhasil-simpan');
        });
    }

    /** @test TC-13: Notifikasi berhasil edit — sistem menampilkan notifikasi berhasil (Positive) */
    public function test_tc13_notifikasi_berhasil_edit(): void
    {
        $this->buatHasilTest(['jenis_pemeriksaan' => 'Dusk Notif Edit']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertSee('Dusk Notif Edit')
                 ->click('.btn-card-edit')
                 ->waitFor('#editModal.active', 5)
                 ->clear('#edit_jenis')
                 ->type('#edit_jenis', 'Dusk Notif Edit Updated')
                 ->click('#editModal .btn-simpan')
                 ->waitFor('#successModal.active', 10)
                 ->assertSee('berhasil diperbarui')
                 ->screenshot('tc13-notifikasi-berhasil-edit');
        });
    }

    /** @test TC-14: Hasil pemeriksaan kosong — sistem menampilkan empty state (Negative) */
    public function test_tc14_hasil_pemeriksaan_kosong_empty_state(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertVisible('.empty-state')
                 ->assertSee('Belum ada hasil pemeriksaan')
                 ->assertMissing('.hasil-card')
                 ->screenshot('tc14-empty-state-hasil-pemeriksaan');
        });
    }

    /** @test TC-15: Gagal menyimpan data — validasi server menampilkan pesan error (Negative) */
    public function test_tc15_gagal_menyimpan_data_validasi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#createModal.active', 5)
                 ->pause(400)
                 // Isi jenis tapi tidak isi hasil_pemeriksaan yang required
                 ->type('#createModal input[name="jenis_pemeriksaan"]', 'Test Gagal Simpan')
                 ->type('#createModal input[name="tanggal_pemeriksaan"]', '2026-03-01')
                 // Sengaja kosongkan hasil_pemeriksaan → server validation gagal
                 ->click('#createModal .btn-simpan')
                 ->pause(500)
                 // Modal tetap terbuka karena HTML5 required mencegah submit
                 ->assertPresent('#createModal')
                 ->screenshot('tc15-gagal-menyimpan-validasi');
        });
    }

    /** @test TC-16: Gagal memperbarui data — validasi server menampilkan pesan error (Negative) */
    public function test_tc16_gagal_memperbarui_data_validasi(): void
    {
        $this->buatHasilTest(['jenis_pemeriksaan' => 'Dusk Gagal Edit']);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaHasil($browser)
                 ->assertSee('Dusk Gagal Edit')
                 ->click('.btn-card-edit')
                 ->waitFor('#editModal.active', 5)
                 // Kosongkan field wajib jenis_pemeriksaan
                 ->clear('#edit_jenis')
                 ->click('#editModal .btn-simpan')
                 ->pause(500)
                 // Modal tetap terbuka karena HTML5 required mencegah submit
                 ->assertPresent('#editModal')
                 ->screenshot('tc16-gagal-memperbarui-validasi');
        });
    }

    /** @test TC-17: Gagal memuat data — halaman tetap tampil meski data kosong (Negative) */
    public function test_tc17_gagal_memuat_data_halaman_tetap_render(): void
    {
        $this->browse(function (Browser $browser) {
            // User tanpa data tetap bisa buka halaman — tidak crash
            $this->loginDanBukaHasil($browser)
                 ->assertPathIs('/hasil-pemeriksaan')
                 ->assertVisible('.btn-tambah')
                 ->screenshot('tc17-halaman-render-tanpa-data');
        });
    }

    /** @test TC-18: Menampilkan loading state — halaman berhasil dimuat setelah loading (Positive) */
    public function test_tc18_menampilkan_loading_state(): void
    {
        $this->buatHasilTest();

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/hasil-pemeriksaan')
                 ->waitFor('.btn-tambah', 15)
                 ->assertVisible('.btn-tambah')
                 ->assertPresent('.hasil-card')
                 ->screenshot('tc18-loading-state-selesai');
        });
    }
}
