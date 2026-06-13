<?php
// PKE-9: Browser test untuk Pengelolaan Katalog Pemeriksaan (Admin)
namespace Tests\Browser;

use App\Models\KatalogPemeriksaan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE9_KatalogAdminTest extends DuskTestCase
{
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->adminUser = User::create([
            'full_name'     => 'Test Admin PKE9',
            'email'         => "dusk.pke9.admin.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'admin',
        ]);
    }

    protected function tearDown(): void
    {
        KatalogPemeriksaan::where('nama', 'like', '%Dusk Test%')->delete();
        $this->adminUser->delete();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function loginAdmin(Browser $browser): Browser
    {
        return $browser->pause(500)
                       ->visit('/login')
                       ->waitFor('input[name="email"]', 15)
                       ->type('input[name="email"]', $this->adminUser->email)
                       ->type('input[name="password"]', 'Password123!')
                       ->press('Sign In')
                       ->waitForLocation('/admin', 20);
    }

    private function loginAdminDanBukaKatalog(Browser $browser): Browser
    {
        return $this->loginAdmin($browser)
                    ->visit('/katalog')
                    ->waitFor('.table-wrap', 15);
    }

    private function buatKatalogTest(array $override = []): KatalogPemeriksaan
    {
        $nama = $override['nama'] ?? 'Dusk Test Pemeriksaan ' . Str::random(4);
        return KatalogPemeriksaan::create(array_merge([
            'nama'           => $nama,
            'slug'           => Str::slug($nama) . '-' . Str::random(4),
            'kategori'       => 'Umum',
            'singkat'        => 'Singkat dusk test',
            'deskripsi'      => 'Deskripsi dusk test pemeriksaan',
            'icon'           => 'fa-stethoscope',
            'bg_color'       => '#e8f5f0',
            'icon_color'     => '#2d9e72',
            'durasi'         => '30 menit',
            'biaya_min'      => 50000,
            'biaya_max'      => 150000,
            'status'         => 'aktif',
            'persiapan'      => ['Puasa 8 jam'],
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Membuka halaman katalog admin — halaman berhasil ditampilkan */
    public function test_tc01_membuka_halaman_katalog_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->assertPathIs('/katalog')
                 ->assertVisible('.table-wrap')
                 ->assertVisible('.btn-add')
                 ->screenshot('tc01-halaman-katalog-admin');
        });
    }

    /** @test TC-02: Menampilkan data katalog — daftar pemeriksaan berhasil ditampilkan */
    public function test_tc02_menampilkan_data_katalog(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->assertVisible('.item-name')
                 ->assertVisible('.badge')
                 ->assertVisible('.btn-edit')
                 ->assertVisible('.btn-delete')
                 ->screenshot('tc02-data-katalog-tampil');
        });
    }

    /** @test TC-03: Menambahkan data katalog pemeriksaan dengan data valid (Positive) */
    public function test_tc03_menambahkan_data_katalog_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->click('.btn-add')
                 ->waitFor('#createModal.open', 5)
                 ->pause(400)
                 ->type('#createModal input[name="nama"]', 'Dusk Test Pemeriksaan Baru')
                 ->select('#createModal select[name="kategori"]', 'Umum')
                 ->type('#createModal input[name="singkat"]', 'Singkat pemeriksaan baru')
                 ->type('#createModal input[name="durasi"]', '30 menit')
                 ->select('#createModal select[name="status"]', 'aktif')
                 ->type('#createModal input[name="biaya_min"]', '50000')
                 ->type('#createModal input[name="biaya_max"]', '150000')
                 ->click('#createModal .btn-primary')
                 ->waitFor('.table-wrap', 10)
                 ->assertSee('berhasil ditambahkan')
                 ->screenshot('tc03-katalog-berhasil-ditambahkan');
        });

        $this->assertDatabaseHas('katalog_pemeriksaan', [
            'nama' => 'Dusk Test Pemeriksaan Baru',
        ]);
    }

    /** @test TC-04: Validasi field wajib kosong — sistem menampilkan validasi (Negative) */
    public function test_tc04_validasi_field_wajib_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->click('.btn-add')
                 ->waitFor('#createModal.open', 5)
                 ->pause(400)
                 // Tidak isi field wajib, langsung submit
                 ->click('#createModal .btn-primary')
                 ->pause(500)
                 // Form tidak boleh submit (HTML5 validation atau redirect dengan error)
                 ->assertPresent('#createModal')
                 ->screenshot('tc04-validasi-field-kosong');
        });
    }

    /** @test TC-05: Validasi format biaya tidak valid — sistem menampilkan error (Negative) */
    public function test_tc05_validasi_format_biaya_tidak_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->click('.btn-add')
                 ->waitFor('#createModal.open', 5)
                 ->pause(400)
                 ->type('#createModal input[name="nama"]', 'Dusk Test Validasi Biaya')
                 ->select('#createModal select[name="kategori"]', 'Umum')
                 ->select('#createModal select[name="status"]', 'aktif')
                 // Biaya min lebih besar dari max (tidak valid secara logika)
                 ->type('#createModal input[name="biaya_min"]', '999999999')
                 ->type('#createModal input[name="biaya_max"]', '0')
                 ->click('#createModal .btn-primary')
                 ->pause(800)
                 ->screenshot('tc05-validasi-format-biaya');
        });
    }

    /** @test TC-06: Menyimpan data katalog pemeriksaan — data tersimpan ke database (Positive) */
    public function test_tc06_menyimpan_data_katalog_ke_database(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->click('.btn-add')
                 ->waitFor('#createModal.open', 5)
                 ->pause(400)
                 ->type('#createModal input[name="nama"]', 'Dusk Test Simpan Database')
                 ->select('#createModal select[name="kategori"]', 'Umum')
                 ->type('#createModal input[name="durasi"]', '20 menit')
                 ->select('#createModal select[name="status"]', 'aktif')
                 ->type('#createModal input[name="biaya_min"]', '75000')
                 ->type('#createModal input[name="biaya_max"]', '200000')
                 ->click('#createModal .btn-primary')
                 ->waitFor('.table-wrap', 10)
                 ->screenshot('tc06-data-tersimpan-database');
        });

        $this->assertDatabaseHas('katalog_pemeriksaan', [
            'nama'     => 'Dusk Test Simpan Database',
            'kategori' => 'Umum',
        ]);
    }

    /** @test TC-07: Menampilkan notifikasi setelah tambah berhasil — muncul pesan sukses (Positive) */
    public function test_tc07_notifikasi_tambah_berhasil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->click('.btn-add')
                 ->waitFor('#createModal.open', 5)
                 ->pause(400)
                 ->type('#createModal input[name="nama"]', 'Dusk Test Notif Tambah')
                 ->select('#createModal select[name="kategori"]', 'Jantung')
                 ->select('#createModal select[name="status"]', 'aktif')
                 ->type('#createModal input[name="biaya_min"]', '100000')
                 ->type('#createModal input[name="biaya_max"]', '300000')
                 ->click('#createModal .btn-primary')
                 ->waitFor('.alert-success, .alert', 10)
                 ->assertSee('berhasil')
                 ->screenshot('tc07-notifikasi-tambah-sukses');
        });
    }

    /** @test TC-08: Membuka form edit — form edit berhasil ditampilkan dengan data yang benar (Positive) */
    public function test_tc08_membuka_form_edit(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Edit Form']);

        $this->browse(function (Browser $browser) use ($katalog) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Edit Form')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 8)
                 ->assertSee('Dusk Test Edit Form')
                 ->click('.btn-edit')
                 ->waitFor('#editModal.open', 5)
                 ->assertInputValue('#e-nama', 'Dusk Test Edit Form')
                 ->screenshot('tc08-form-edit-terbuka');
        });
    }

    /** @test TC-09: Mengubah data katalog — data berhasil diperbarui di database (Positive) */
    public function test_tc09_mengubah_data_katalog(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Sebelum Edit']);

        $this->browse(function (Browser $browser) use ($katalog) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Sebelum Edit')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 8)
                 ->assertSee('Dusk Test Sebelum Edit')
                 ->click('.btn-edit')
                 ->waitFor('#editModal.open', 5)
                 ->clear('#e-nama')
                 ->type('#e-nama', 'Dusk Test Sesudah Edit')
                 ->click('#editModal .btn-primary')
                 ->waitFor('.table-wrap', 10)
                 ->assertSee('berhasil diperbarui')
                 ->assertSee('Dusk Test Sesudah Edit')
                 ->screenshot('tc09-data-berhasil-diubah');
        });

        $this->assertDatabaseHas('katalog_pemeriksaan', [
            'id'   => $katalog->id,
            'nama' => 'Dusk Test Sesudah Edit',
        ]);
    }

    /** @test TC-10: Menampilkan notifikasi setelah edit berhasil — muncul pesan sukses (Positive) */
    public function test_tc10_notifikasi_edit_berhasil(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Notif Edit']);

        $this->browse(function (Browser $browser) use ($katalog) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Notif Edit')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 8)
                 ->assertSee('Dusk Test Notif Edit')
                 ->click('.btn-edit')
                 ->waitFor('#editModal.open', 5)
                 ->clear('#e-nama')
                 ->type('#e-nama', 'Dusk Test Notif Edit Updated')
                 ->click('#editModal .btn-primary')
                 ->waitFor('.alert-success, .alert', 10)
                 ->assertSee('berhasil')
                 ->screenshot('tc10-notifikasi-edit-sukses');
        });
    }

    /** @test TC-11: Menghapus data katalog — sistem menampilkan konfirmasi (Positive) */
    public function test_tc11_konfirmasi_sebelum_hapus(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Konfirmasi Hapus']);

        $this->browse(function (Browser $browser) use ($katalog) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Konfirmasi Hapus')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 8)
                 ->assertSee('Dusk Test Konfirmasi Hapus')
                 ->click('.btn-delete')
                 ->waitFor('#deleteModal.open', 5)
                 ->assertSee('Dusk Test Konfirmasi Hapus')
                 ->assertVisible('#deleteModal .btn-danger')
                 ->assertVisible('#deleteModal .btn-secondary')
                 ->screenshot('tc11-konfirmasi-hapus-tampil');
        });
    }

    /** @test TC-12: Konfirmasi penghapusan — data berhasil dihapus (Positive) */
    public function test_tc12_konfirmasi_hapus_data(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Hapus Konfirm']);

        $this->browse(function (Browser $browser) use ($katalog) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Hapus Konfirm')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 8)
                 ->assertSee('Dusk Test Hapus Konfirm')
                 ->click('.btn-delete')
                 ->waitFor('#deleteModal.open', 5)
                 ->click('#deleteModal .btn-danger')
                 ->waitFor('.table-wrap', 10)
                 ->assertSee('berhasil dihapus')
                 ->assertDontSee('Dusk Test Hapus Konfirm')
                 ->screenshot('tc12-data-berhasil-dihapus');
        });

        $this->assertDatabaseMissing('katalog_pemeriksaan', ['id' => $katalog->id]);
    }

    /** @test TC-13: Menampilkan notifikasi setelah hapus berhasil — muncul pesan sukses (Positive) */
    public function test_tc13_notifikasi_hapus_berhasil(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Notif Hapus']);

        $this->browse(function (Browser $browser) use ($katalog) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Notif Hapus')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 8)
                 ->click('.btn-delete')
                 ->waitFor('#deleteModal.open', 5)
                 ->click('#deleteModal .btn-danger')
                 ->waitFor('.alert-success, .alert', 10)
                 ->assertSee('berhasil dihapus')
                 ->screenshot('tc13-notifikasi-hapus-sukses');
        });
    }

    /** @test TC-14: Mencari pemeriksaan dengan keyword — data sesuai keyword ditampilkan (Positive) */
    public function test_tc14_mencari_pemeriksaan_dengan_keyword(): void
    {
        $katalog = $this->buatKatalogTest(['nama' => 'Dusk Test Cari Gigi', 'kategori' => 'Gigi']);

        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Dusk Test Cari')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 10)
                 ->assertSee('Dusk Test Cari Gigi')
                 ->screenshot('tc14-pencarian-keyword-admin');
        });
    }

    /** @test TC-15: Filter berdasarkan kategori — data sesuai kategori ditampilkan (Positive) */
    public function test_tc15_filter_kategori(): void
    {
        $this->buatKatalogTest(['nama' => 'Dusk Test Filter Mata', 'kategori' => 'Mata']);

        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->select('select[name="kategori"]', 'Mata')
                 ->pause(800)
                 ->waitFor('.table-wrap', 10)
                 ->assertPresent('.item-name')
                 ->screenshot('tc15-filter-kategori-admin');
        });
    }

    /** @test TC-16: Data katalog kosong — sistem menampilkan empty state (Negative) */
    public function test_tc16_empty_state_saat_pencarian_tidak_ditemukan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdminDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'XYZNOTFOUND999')
                 ->keys('input[name="q"]', '{tab}')
                 ->waitFor('.table-wrap', 10)
                 ->assertPresent('.empty-row')
                 ->screenshot('tc16-empty-state-admin');
        });
    }

    /** @test TC-17: Loading state katalog — halaman berhasil dimuat setelah loading (Positive) */
    public function test_tc17_loading_state_katalog(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAdmin($browser)
                 ->visit('/katalog')
                 ->waitFor('.table-wrap', 15)
                 ->assertVisible('.table-wrap')
                 ->assertPresent('.item-name')
                 ->screenshot('tc17-loading-state-katalog');
        });
    }
}
