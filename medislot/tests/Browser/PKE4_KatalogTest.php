<?php
// PKE-4: Browser test untuk Katalog Jenis Pemeriksaan (TS.KAT.001 – TS.KAT.007)
// Mencakup: menampilkan daftar, pencarian valid/invalid, detail pemeriksaan, navigasi kembali
namespace Tests\Browser;

use App\Models\KatalogPemeriksaan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE4_KatalogTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE4',
            'email'         => "dusk.pke4.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
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

    private function loginDanBukaKatalog(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/katalog')
                    ->waitFor('.katalog-grid, .empty-state', 15);
    }

    private function getSlugPertama(): string
    {
        return KatalogPemeriksaan::where('status', 'aktif')->first()?->slug ?? 'pemeriksaan-gigi';
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /**
     * @test TC-01 (TS.KAT.001) – Menampilkan daftar pemeriksaan (Positive)
     * Login → pilih menu Katalog Pemeriksaan → daftar pemeriksaan tampil dalam bentuk card.
     */
    public function test_tc01_menampilkan_daftar_pemeriksaan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaKatalog($browser)
                 ->assertPathIs('/katalog')
                 ->assertVisible('.katalog-grid')
                 ->assertPresent('.katalog-card')
                 ->screenshot('tc01-daftar-pemeriksaan-tampil');
        });
    }

    /**
     * @test TC-02 (TS.KAT.001) – Halaman katalog berhasil dimuat dengan data pemeriksaan (Positive)
     * Halaman katalog menampilkan daftar pemeriksaan lengkap dengan informasi kategori dan nama.
     */
    public function test_tc02_katalog_memuat_data_pemeriksaan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaKatalog($browser)
                 ->assertVisible('.katalog-card')
                 ->assertVisible('.card-kategori')
                 ->assertPresent('.result-info')
                 ->screenshot('tc02-data-pemeriksaan-dimuat');
        });
    }

    /**
     * @test TC-03 (TS.KAT.002) – Mencari pemeriksaan dengan keyword valid "Gigi" (Positive)
     * Input keyword "Gigi" → klik Cari → hasil pencarian terkait gigi tampil.
     */
    public function test_tc03_cari_keyword_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Gigi')
                 ->click('.btn-search')
                 ->waitFor('.katalog-grid, .empty-state', 10)
                 ->assertInputValue('input[name="q"]', 'gigi') // controller lowercase input
                 ->screenshot('tc03-hasil-pencarian-gigi');
        });
    }

    /**
     * @test TC-04 (TS.KAT.002) – Hasil pencarian keyword valid menampilkan data yang relevan (Positive)
     * Setelah pencarian "Gigi", card yang tampil mengandung kata "Gigi".
     */
    public function test_tc04_hasil_pencarian_keyword_valid_relevan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'Gigi')
                 ->click('.btn-search')
                 ->waitFor('.katalog-grid, .empty-state', 10)
                 ->assertPresent('.katalog-card')
                 ->screenshot('tc04-hasil-pencarian-relevan');
        });

        // Verifikasi di DB bahwa ada katalog bertema Gigi
        $this->assertGreaterThan(0,
            KatalogPemeriksaan::where('status', 'aktif')
                ->whereRaw("LOWER(nama) LIKE '%gigi%'")
                ->orWhereRaw("LOWER(kategori) LIKE '%gigi%'")
                ->count()
        );
    }

    /**
     * @test TC-05 (TS.KAT.003) – Mencari dengan keyword tidak valid "XYZ123" (Negative)
     * Input "XYZ123" → klik Cari → sistem menampilkan pesan tidak ada data.
     */
    public function test_tc05_cari_keyword_tidak_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaKatalog($browser)
                 ->type('input[name="q"]', 'XYZ123')
                 ->click('.btn-search')
                 ->waitFor('.empty-state', 10)
                 ->assertSee('Tidak ada pemeriksaan yang cocok')
                 ->assertMissing('.katalog-card')
                 ->screenshot('tc05-keyword-tidak-ditemukan');
        });
    }

    /**
     * @test TC-06 (TS.KAT.004) – Membuka halaman detail pemeriksaan (Positive)
     * Klik salah satu card pemeriksaan → halaman detail terbuka dengan nama pemeriksaan.
     */
    public function test_tc06_membuka_detail_pemeriksaan(): void
    {
        $slug = $this->getSlugPertama();

        $this->browse(function (Browser $browser) use ($slug) {
            $this->loginDanBukaKatalog($browser)
                 ->click('.katalog-card')
                 ->waitFor('.detail-wrap', 10)
                 ->assertPathContains('/katalog/')
                 ->assertVisible('.detail-header-info')
                 ->screenshot('tc06-detail-pemeriksaan-terbuka');
        });
    }

    /**
     * @test TC-07 (TS.KAT.004) – Halaman detail menampilkan informasi lengkap pemeriksaan (Positive)
     * Halaman detail memuat nama, deskripsi, persiapan, dan estimasi biaya.
     */
    public function test_tc07_detail_menampilkan_informasi_lengkap(): void
    {
        $slug = $this->getSlugPertama();

        $this->browse(function (Browser $browser) use ($slug) {
            $this->login($browser)
                 ->visit("/katalog/{$slug}")
                 ->waitFor('.detail-wrap', 10)
                 ->assertVisible('.detail-header-info')
                 ->assertVisible('.detail-card')
                 ->screenshot('tc07-informasi-detail-lengkap');
        });
    }

    /**
     * @test TC-08 (TS.KAT.004) – Deskripsi pemeriksaan tampil di halaman detail (Positive)
     * Bagian "Tentang Pemeriksaan Ini" berisi deskripsi yang dapat dibaca.
     */
    public function test_tc08_deskripsi_tampil_di_detail(): void
    {
        $item = KatalogPemeriksaan::where('status', 'aktif')
                    ->whereNotNull('deskripsi')
                    ->first();

        $this->browse(function (Browser $browser) use ($item) {
            $this->login($browser)
                 ->visit("/katalog/{$item->slug}")
                 ->waitFor('.detail-wrap', 10)
                 ->assertSee('Tentang Pemeriksaan Ini')
                 ->assertVisible('.detail-card p')
                 ->screenshot('tc08-deskripsi-pemeriksaan-tampil');
        });
    }

    /**
     * @test TC-09 (TS.KAT.004) – Persiapan pemeriksaan tampil di halaman detail (Positive)
     * Bagian "Persiapan Sebelum Pemeriksaan" berisi daftar langkah persiapan.
     */
    public function test_tc09_persiapan_tampil_di_detail(): void
    {
        $item = KatalogPemeriksaan::where('status', 'aktif')
                    ->whereNotNull('persiapan')
                    ->first();

        $this->browse(function (Browser $browser) use ($item) {
            $this->login($browser)
                 ->visit("/katalog/{$item->slug}")
                 ->waitFor('.detail-wrap', 10)
                 ->assertSee('Persiapan Sebelum Pemeriksaan')
                 ->assertVisible('.persiapan-list')
                 ->screenshot('tc09-persiapan-pemeriksaan-tampil');
        });
    }

    /**
     * @test TC-10 (TS.KAT.004) – Estimasi biaya tampil di halaman detail (Positive)
     * Bagian "Estimasi Biaya" menampilkan range harga dalam format Rupiah.
     */
    public function test_tc10_estimasi_biaya_tampil_di_detail(): void
    {
        $item = KatalogPemeriksaan::where('status', 'aktif')
                    ->where('biaya_min', '>', 0)
                    ->first();

        $this->browse(function (Browser $browser) use ($item) {
            $this->login($browser)
                 ->visit("/katalog/{$item->slug}")
                 ->waitFor('.detail-wrap', 10)
                 ->assertSee('Estimasi Biaya')
                 ->assertVisible('.biaya-wrap')
                 ->assertSee('Rp')
                 ->screenshot('tc10-estimasi-biaya-tampil');
        });
    }

    /**
     * @test TC-11 (TS.KAT.005) – Kembali ke katalog dari halaman detail (Positive)
     * Klik tombol "Kembali ke Katalog" → sistem mengarahkan kembali ke /katalog.
     */
    public function test_tc11_kembali_ke_katalog_dari_detail(): void
    {
        $slug = $this->getSlugPertama();

        $this->browse(function (Browser $browser) use ($slug) {
            $this->login($browser)
                 ->visit("/katalog/{$slug}")
                 ->waitFor('.detail-wrap', 10)
                 ->assertSee('Kembali ke Katalog')
                 ->click('a[href*="/katalog"]')
                 ->waitForLocation('/katalog', 10)
                 ->assertPathIs('/katalog')
                 ->assertVisible('.katalog-grid')
                 ->screenshot('tc11-kembali-ke-katalog');
        });
    }

    /**
     * @test TC-12 (TS.KAT.007) – Pencarian tanpa keyword menampilkan semua pemeriksaan (Positive)
     * Kolom pencarian dikosongkan → klik Cari → seluruh daftar pemeriksaan tampil kembali.
     */
    public function test_tc12_pencarian_tanpa_keyword_tampilkan_semua(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaKatalog($browser)
                 ->clear('input[name="q"]')
                 ->click('.btn-search')
                 ->waitFor('.katalog-grid', 10)
                 ->assertVisible('.katalog-card')
                 ->assertPathIs('/katalog')
                 ->screenshot('tc12-pencarian-kosong-tampilkan-semua');
        });
    }
}
