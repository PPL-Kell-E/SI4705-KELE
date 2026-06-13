<?php
// PKE-3: Browser test untuk Rekomendasi Jadwal Pemeriksaan (TS.REK.001 – TS.REK.003)
namespace Tests\Browser;

use App\Models\Rekomendasi;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE3_RekomendasiTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE3 Rekomendasi',
            'email'         => "dusk.pke3.rek.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        Rekomendasi::where('user_id', $this->testUser->id)->delete();
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

    private function loginDanBukaRekomendasi(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/rekomendasi')
                    ->waitFor('.rec-grid, .btn-add', 15);
    }

    private function buatRekomendasi(array $override = []): Rekomendasi
    {
        return Rekomendasi::create(array_merge([
            'user_id'        => $this->testUser->id,
            'nama'           => 'Pemeriksaan Umum Test',
            'deskripsi'      => 'Deskripsi test rekomendasi',
            'icon'           => 'fa-stethoscope',
            'bg_color'       => '#e8f5f0',
            'icon_color'     => '#2d9e72',
            'interval_days'  => 180,
            'interval_label' => '6 bulan',
            'is_default'     => false,
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /**
     * @test TC-01 (TS.REK.001) – Menampilkan daftar rekomendasi pemeriksaan (Positive)
     * Login → buka menu Rekomendasi → sistem menampilkan daftar rekomendasi
     * beserta nama pemeriksaan dan tanggal rekomendasi.
     */
    public function test_tc01_menampilkan_daftar_rekomendasi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertVisible('.rec-grid')
                 ->assertVisible('.rec-card')
                 ->assertVisible('.rec-name')
                 ->assertVisible('.rec-next-date')
                 ->screenshot('tc01-daftar-rekomendasi-tampil');
        });
    }

    /**
     * @test TC-02 (TS.REK.001) – Daftar rekomendasi default otomatis dibuat saat pertama login (Positive)
     * User baru → buka rekomendasi → 5 rekomendasi default langsung tersedia.
     */
    public function test_tc02_rekomendasi_default_otomatis_dibuat(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertSee('Dental Check-up')
                 ->assertSee('Medical Check-up')
                 ->assertVisible('.rec-default-badge')
                 ->screenshot('tc02-rekomendasi-default-tersedia');
        });

        $this->assertEquals(5, Rekomendasi::where('user_id', $this->testUser->id)
                                          ->where('is_default', true)->count());
    }

    /**
     * @test TC-03 (TS.REK.001) – Sistem menampilkan nama dan tanggal rekomendasi berikutnya (Positive)
     * Setiap card rekomendasi menampilkan nama pemeriksaan dan info tanggal berikutnya.
     */
    public function test_tc03_nama_dan_tanggal_rekomendasi_tampil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertVisible('.rec-name')
                 ->assertVisible('.rec-next-date-label')
                 ->assertVisible('.rec-next-date-value')
                 ->assertSeeIn('.rec-next-date-label', 'Pemeriksaan berikutnya')
                 ->screenshot('tc03-nama-dan-tanggal-tampil');
        });
    }

    /**
     * @test TC-04 (TS.REK.002) – Melihat detail rekomendasi pemeriksaan (Positive)
     * Buka menu Rekomendasi → card rekomendasi tampil → nama pemeriksaan tampil.
     */
    public function test_tc04_detail_rekomendasi_tampil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertVisible('.rec-card')
                 ->assertVisible('.rec-name')
                 ->assertVisible('.rec-desc')
                 ->assertVisible('.rec-interval')
                 ->screenshot('tc04-detail-rekomendasi-tampil');
        });
    }

    /**
     * @test TC-05 (TS.REK.002) – Alasan medis rekomendasi tampil berdasarkan kondisi pengguna (Positive)
     * Deskripsi/alasan medis setiap rekomendasi tampil di card.
     */
    public function test_tc05_alasan_medis_tampil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertVisible('.rec-desc')
                 ->assertSee('Disarankan setiap 6 bulan sekali')
                 ->screenshot('tc05-alasan-medis-tampil');
        });
    }

    /**
     * @test TC-06 (TS.REK.002) – Informasi manfaat / interval pemeriksaan tampil (Positive)
     * Label interval pemeriksaan (contoh: "6 bulan", "1 tahun") tampil di setiap card.
     */
    public function test_tc06_informasi_interval_tampil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertVisible('.rec-interval')
                 ->assertSee('Setiap')
                 ->screenshot('tc06-informasi-interval-tampil');
        });
    }

    /**
     * @test TC-07 (TS.REK.003) – Menghitung rekomendasi berdasarkan data pengguna (Positive)
     * Sistem memproses data user → buka halaman rekomendasi → rekomendasi sesuai kondisi muncul.
     */
    public function test_tc07_rekomendasi_dihitung_berdasarkan_data_pengguna(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertVisible('.rec-grid')
                 ->assertPresent('.rec-card')
                 ->screenshot('tc07-rekomendasi-berdasarkan-data-pengguna');
        });

        $this->assertGreaterThan(0, Rekomendasi::where('user_id', $this->testUser->id)->count());
    }

    /**
     * @test TC-08 (TS.REK.003) – Sistem memproses usia pengguna untuk rekomendasi (Positive)
     * Halaman rekomendasi berhasil dimuat → perhitungan berdasarkan data dijalankan.
     */
    public function test_tc08_sistem_memproses_data_untuk_rekomendasi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertPathIs('/rekomendasi')
                 ->assertVisible('.rec-card')
                 ->assertVisible('.rec-next-date-value')
                 ->screenshot('tc08-sistem-proses-data-rekomendasi');
        });
    }

    /**
     * @test TC-09 – Menambah rekomendasi baru melalui modal Tambah (Positive)
     * Klik "Tambah Rekomendasi" → isi form → simpan → rekomendasi baru muncul di daftar.
     */
    public function test_tc09_menambah_rekomendasi_baru(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->click('.btn-add')
                 ->waitFor('#addModal.open', 5)
                 ->type('input[name="nama"]', 'Pemeriksaan Ginjal')
                 ->type('textarea[name="deskripsi"]', 'Cek fungsi ginjal secara rutin')
                 ->type('input[name="interval_days"]', '365')
                 ->type('input[name="interval_label"]', '1 tahun')
                 ->click('.btn-save')
                 ->waitForLocation('/rekomendasi', 10)
                 ->assertSee('Rekomendasi berhasil ditambahkan')
                 ->assertSee('Pemeriksaan Ginjal')
                 ->screenshot('tc09-rekomendasi-baru-ditambahkan');
        });

        $this->assertDatabaseHas('rekomendasi', [
            'user_id' => $this->testUser->id,
            'nama'    => 'Pemeriksaan Ginjal',
        ]);
    }

    /**
     * @test TC-10 – Membuka modal edit rekomendasi (Positive)
     * Klik tombol "Edit" → modal edit terbuka dengan data rekomendasi yang benar.
     */
    public function test_tc10_membuka_modal_edit_rekomendasi(): void
    {
        $rek = $this->buatRekomendasi(['nama' => 'Tes Edit Rekomendasi']);

        $this->browse(function (Browser $browser) use ($rek) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertSee('Tes Edit Rekomendasi')
                 ->click('.btn-edit')
                 ->waitFor('#editModal.open', 5)
                 ->assertInputValue('#editNama', 'Tes Edit Rekomendasi')
                 ->screenshot('tc10-modal-edit-terbuka');
        });
    }

    /**
     * @test TC-11 – Mengedit rekomendasi yang sudah ada (Positive)
     * Buka modal edit → ubah nama → simpan → nama baru tampil di daftar.
     */
    public function test_tc11_mengedit_rekomendasi(): void
    {
        $rek = $this->buatRekomendasi(['nama' => 'Nama Lama Rekomendasi']);

        $this->browse(function (Browser $browser) use ($rek) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertSee('Nama Lama Rekomendasi')
                 ->click('.btn-edit')
                 ->waitFor('#editModal.open', 5)
                 ->clear('#editNama')
                 ->type('#editNama', 'Nama Baru Rekomendasi')
                 ->click('#editModal .btn-save')
                 ->waitForLocation('/rekomendasi', 10)
                 ->assertSee('Rekomendasi berhasil diperbarui')
                 ->assertSee('Nama Baru Rekomendasi')
                 ->screenshot('tc11-rekomendasi-berhasil-diedit');
        });

        $this->assertDatabaseHas('rekomendasi', [
            'id'   => $rek->id,
            'nama' => 'Nama Baru Rekomendasi',
        ]);
    }

    /**
     * @test TC-12 – Menghapus rekomendasi (Positive)
     * Klik tombol hapus → konfirmasi → rekomendasi dihapus dari daftar dan database.
     */
    public function test_tc12_menghapus_rekomendasi(): void
    {
        $rek = $this->buatRekomendasi(['nama' => 'Rekomendasi Akan Dihapus']);

        $this->browse(function (Browser $browser) use ($rek) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertSee('Rekomendasi Akan Dihapus')
                 ->click('.btn-delete')
                 ->acceptDialog()
                 ->waitForLocation('/rekomendasi', 10)
                 ->assertSee('Rekomendasi berhasil dihapus')
                 ->assertDontSee('Rekomendasi Akan Dihapus')
                 ->screenshot('tc12-rekomendasi-berhasil-dihapus');
        });

        $this->assertDatabaseMissing('rekomendasi', ['id' => $rek->id]);
    }

    /**
     * @test TC-13 – Membatalkan hapus rekomendasi (Positive)
     * Klik tombol hapus → dismiss konfirmasi → rekomendasi tetap ada.
     */
    public function test_tc13_batal_hapus_rekomendasi(): void
    {
        $rek = $this->buatRekomendasi(['nama' => 'Rekomendasi Tetap Ada']);

        $this->browse(function (Browser $browser) use ($rek) {
            $this->loginDanBukaRekomendasi($browser)
                 ->assertSee('Rekomendasi Tetap Ada')
                 ->click('.btn-delete')
                 ->dismissDialog()
                 ->assertSee('Rekomendasi Tetap Ada')
                 ->screenshot('tc13-batal-hapus-rekomendasi');
        });

        $this->assertDatabaseHas('rekomendasi', ['id' => $rek->id]);
    }

    /**
     * @test TC-14 – Tombol Jadwalkan mengarahkan ke form tambah jadwal (Positive)
     * Klik "Jadwalkan" → diarahkan ke /jadwal/create dengan tanggal terisi otomatis.
     */
    public function test_tc14_tombol_jadwalkan_ke_form_jadwal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaRekomendasi($browser)
                 ->click('.btn-jadwal')
                 ->waitForLocation('/jadwal/create', 10)
                 ->assertPathIs('/jadwal/create')
                 ->screenshot('tc14-redirect-ke-form-jadwal');
        });
    }
}
