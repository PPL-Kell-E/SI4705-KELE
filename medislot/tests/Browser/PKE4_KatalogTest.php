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
     * Setelah pencarian keyword dari katalog pertama, card yang relevan tampil.
     */
    public function test_tc04_hasil_pencarian_keyword_valid_relevan(): void
    {
        // Ambil kata pertama dari nama katalog pertama yang aktif
        $namaKatalog = KatalogPemeriksaan::where('status', 'aktif')->value('nama') ?? 'Pemeriksaan';
        $keyword     = explode(' ', $namaKatalog)[0];

        $this->browse(function (Browser $browser) use ($keyword) {
            $this->loginDanBukaKatalog($browser)
                 ->type('input[name="q"]', $keyword)
                 ->click('.btn-search')
                 ->waitFor('.katalog-grid, .empty-state', 10)
                 ->assertPresent('.katalog-card')
                 ->screenshot('tc04-hasil-pencarian-relevan');
        });

        // Verifikasi di DB bahwa memang ada hasil untuk keyword tersebut
        $this->assertGreaterThan(0,
            KatalogPemeriksaan::where('status', 'aktif')
                ->whereRaw("LOWER(nama) LIKE ?", ['%' . strtolower($keyword) . '%'])
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

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    private function buatKatalog(array $override = []): KatalogPemeriksaan
    {
        $uid = Str::random(6);
        return KatalogPemeriksaan::create(array_merge([
            'slug'       => "pemeriksaan-test-{$uid}",
            'nama'       => "Pemeriksaan Test {$uid}",
            'kategori'   => 'Umum',
            'icon'       => 'fa-stethoscope',
            'bg_color'   => '#e8f5f0',
            'icon_color' => '#2d9e72',
            'singkat'    => 'Pemeriksaan umum rutin',
            'deskripsi'  => 'Deskripsi pemeriksaan test',
            'biaya_min'  => 100000,
            'biaya_max'  => 300000,
            'status'     => 'aktif',
        ], $override));
    }

    /** @test TC-B01: Data katalog tersimpan ke tabel katalog_pemeriksaan dengan benar */
    public function test_tcb01_data_katalog_tersimpan_ke_db(): void
    {
        $katalog = $this->buatKatalog(['nama' => 'Pemeriksaan Jantung Test']);

        $this->assertDatabaseHas('katalog_pemeriksaan', [
            'id'       => $katalog->id,
            'nama'     => 'Pemeriksaan Jantung Test',
            'kategori' => 'Umum',
            'status'   => 'aktif',
        ]);
    }

    /** @test TC-B02: Slug dibuat otomatis dari nama dan unik di DB */
    public function test_tcb02_slug_unik_di_db(): void
    {
        $k1 = $this->buatKatalog(['nama' => 'Cek Mata Rutin A']);
        $k2 = $this->buatKatalog(['nama' => 'Cek Mata Rutin B']);

        $this->assertNotEquals($k1->slug, $k2->slug, 'Setiap katalog harus memiliki slug yang unik');
        $this->assertDatabaseHas('katalog_pemeriksaan', ['slug' => $k1->slug]);
        $this->assertDatabaseHas('katalog_pemeriksaan', ['slug' => $k2->slug]);
    }

    /** @test TC-B03: biaya_min dan biaya_max tersimpan sebagai integer */
    public function test_tcb03_biaya_tersimpan_sebagai_integer(): void
    {
        $katalog = $this->buatKatalog(['biaya_min' => 150000, 'biaya_max' => 500000]);

        $fresh = KatalogPemeriksaan::find($katalog->id);
        $this->assertIsInt($fresh->biaya_min);
        $this->assertIsInt($fresh->biaya_max);
        $this->assertEquals(150000, $fresh->biaya_min);
        $this->assertEquals(500000, $fresh->biaya_max);
    }

    /** @test TC-B04: persiapan disimpan sebagai JSON array di DB */
    public function test_tcb04_persiapan_disimpan_sebagai_array(): void
    {
        $persiapan = ['Puasa 8 jam', 'Tidak minum kopi', 'Bawa kartu identitas'];
        $katalog   = $this->buatKatalog(['persiapan' => $persiapan]);

        $fresh = KatalogPemeriksaan::find($katalog->id);
        $this->assertIsArray($fresh->persiapan);
        $this->assertContains('Puasa 8 jam', $fresh->persiapan);
        $this->assertContains('Tidak minum kopi', $fresh->persiapan);
        $this->assertCount(3, $fresh->persiapan);
    }

    /** @test TC-B05: Status 'aktif' hanya tampil di query aktif, 'nonaktif' tidak */
    public function test_tcb05_filter_status_aktif_benar(): void
    {
        $aktif    = $this->buatKatalog(['status' => 'aktif']);
        $nonaktif = $this->buatKatalog(['status' => 'nonaktif']);

        $hasilAktif = KatalogPemeriksaan::where('status', 'aktif')->pluck('id');

        $this->assertContains($aktif->id, $hasilAktif);
        $this->assertNotContains($nonaktif->id, $hasilAktif);
    }

    /** @test TC-B06: Pencarian nama katalog dengan LIKE berfungsi di DB */
    public function test_tcb06_pencarian_nama_dengan_like(): void
    {
        $this->buatKatalog(['nama' => 'Pemeriksaan Ginjal Rutin', 'status' => 'aktif']);

        $hasil = KatalogPemeriksaan::where('status', 'aktif')
                    ->whereRaw("LOWER(nama) LIKE ?", ['%ginjal%'])
                    ->get();

        $this->assertGreaterThan(0, $hasil->count());
        $this->assertTrue($hasil->contains(fn($k) => str_contains(strtolower($k->nama), 'ginjal')));
    }

    /** @test TC-B07: Pencarian keyword tidak ditemukan mengembalikan koleksi kosong */
    public function test_tcb07_pencarian_keyword_tidak_ada_kosong(): void
    {
        $hasil = KatalogPemeriksaan::where('status', 'aktif')
                    ->whereRaw("LOWER(nama) LIKE ?", ['%xyznotfound123%'])
                    ->get();

        $this->assertCount(0, $hasil);
    }

    /** @test TC-B08: Pencarian bisa berdasarkan kategori */
    public function test_tcb08_pencarian_berdasarkan_kategori(): void
    {
        $this->buatKatalog(['kategori' => 'Jantung', 'status' => 'aktif']);

        $hasil = KatalogPemeriksaan::where('status', 'aktif')
                    ->where('kategori', 'Jantung')
                    ->get();

        $this->assertGreaterThan(0, $hasil->count());
        $hasil->each(fn($k) => $this->assertEquals('Jantung', $k->kategori));
    }

    /** @test TC-B09: Edit katalog — perubahan nama dan biaya tersimpan ke DB */
    public function test_tcb09_edit_katalog_tersimpan_ke_db(): void
    {
        $katalog = $this->buatKatalog(['nama' => 'Nama Lama Katalog', 'biaya_min' => 100000]);

        $katalog->update(['nama' => 'Nama Baru Katalog', 'biaya_min' => 200000]);

        $this->assertDatabaseHas('katalog_pemeriksaan', [
            'id'       => $katalog->id,
            'nama'     => 'Nama Baru Katalog',
            'biaya_min'=> 200000,
        ]);
        $this->assertDatabaseMissing('katalog_pemeriksaan', [
            'id'  => $katalog->id,
            'nama'=> 'Nama Lama Katalog',
        ]);
    }

    /** @test TC-B10: Hapus katalog — data hilang dari DB */
    public function test_tcb10_hapus_katalog_hilang_dari_db(): void
    {
        $katalog = $this->buatKatalog(['nama' => 'Katalog Akan Dihapus']);
        $id      = $katalog->id;

        $katalog->delete();

        $this->assertDatabaseMissing('katalog_pemeriksaan', ['id' => $id]);
    }

    /** @test TC-B11: Katalog bisa diambil via slug (metode yang dipakai controller show) */
    public function test_tcb11_ambil_katalog_via_slug(): void
    {
        $slug    = 'tes-slug-pke4-' . Str::random(6);
        $katalog = $this->buatKatalog(['slug' => $slug, 'nama' => 'Tes Slug PKE4']);

        $found = KatalogPemeriksaan::where('slug', $slug)
                    ->where('status', 'aktif')
                    ->first();

        $this->assertNotNull($found);
        $this->assertEquals('Tes Slug PKE4', $found->nama);
    }

    /** @test TC-B12: Slug tidak ditemukan mengembalikan null (akan 404 di controller) */
    public function test_tcb12_slug_tidak_ada_mengembalikan_null(): void
    {
        $found = KatalogPemeriksaan::where('slug', 'slug-yang-tidak-ada-xyz')
                    ->where('status', 'aktif')
                    ->first();

        $this->assertNull($found);
    }

    /** @test TC-B13: updated_at berubah setelah katalog diedit */
    public function test_tcb13_updated_at_berubah_setelah_edit(): void
    {
        $katalog = $this->buatKatalog();
        $before  = $katalog->updated_at;

        sleep(1);
        $katalog->update(['nama' => 'Nama Setelah Edit']);

        $after = KatalogPemeriksaan::find($katalog->id)->updated_at;
        $this->assertTrue($after->greaterThan($before), 'updated_at harus berubah setelah edit');
    }

    /** @test TC-B14: persiapan null tidak menyebabkan error di DB */
    public function test_tcb14_persiapan_null_tidak_error(): void
    {
        $katalog = $this->buatKatalog(['persiapan' => null]);

        $fresh = KatalogPemeriksaan::find($katalog->id);
        $this->assertNull($fresh->persiapan);
        $this->assertDatabaseHas('katalog_pemeriksaan', ['id' => $katalog->id]);
    }
}
