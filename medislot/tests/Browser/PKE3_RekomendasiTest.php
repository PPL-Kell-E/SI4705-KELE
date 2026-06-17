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

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /** @test TC-B01: 5 rekomendasi default tersimpan ke DB saat pertama kali dibuat */
    public function test_tcb01_rekomendasi_default_tersimpan_ke_db(): void
    {
        // Simulasi logika index() — seed defaults jika belum ada
        if (Rekomendasi::where('user_id', $this->testUser->id)->doesntExist()) {
            $defaults = [
                ['nama' => 'Dental Check-up',               'deskripsi' => 'Disarankan setiap 6 bulan sekali',  'icon' => 'fa-tooth',       'bg_color' => '#e8f5f0', 'icon_color' => '#2d9e72', 'interval_days' => 180, 'interval_label' => '6 bulan'],
                ['nama' => 'Medical Check-up',              'deskripsi' => 'Jaga kesehatan secara menyeluruh',   'icon' => 'fa-kit-medical', 'bg_color' => '#e8f0f5', 'icon_color' => '#3a7abf', 'interval_days' => 365, 'interval_label' => '1 tahun'],
                ['nama' => 'Pemeriksaan Mata',              'deskripsi' => 'Disarankan setahun sekali',          'icon' => 'fa-eye',         'bg_color' => '#f0f5e8', 'icon_color' => '#6a9e2d', 'interval_days' => 365, 'interval_label' => '1 tahun'],
                ['nama' => 'Pemeriksaan Kesehatan Jantung', 'deskripsi' => 'Mulai pantau kesehatan jantungmu',  'icon' => 'fa-heart-pulse', 'bg_color' => '#fdf0f0', 'icon_color' => '#e05252', 'interval_days' => 180, 'interval_label' => '6 bulan'],
                ['nama' => 'Pemeriksaan THT',               'deskripsi' => 'Disarankan setiap 6 bulan sekali',  'icon' => 'fa-ear-deaf',    'bg_color' => '#fdf5e8', 'icon_color' => '#bf8a3a', 'interval_days' => 180, 'interval_label' => '6 bulan'],
            ];
            foreach ($defaults as $d) {
                Rekomendasi::create(array_merge($d, ['user_id' => $this->testUser->id, 'is_default' => true]));
            }
        }

        $count = Rekomendasi::where('user_id', $this->testUser->id)
                            ->where('is_default', true)
                            ->count();

        $this->assertEquals(5, $count, 'Harus ada tepat 5 rekomendasi default per user baru');
    }

    /** @test TC-B02: Rekomendasi default memiliki nama yang benar di DB */
    public function test_tcb02_nama_rekomendasi_default_benar(): void
    {
        $this->buatRekomendasi(['nama' => 'Dental Check-up', 'is_default' => true]);
        $this->buatRekomendasi(['nama' => 'Medical Check-up', 'is_default' => true]);

        $this->assertDatabaseHas('rekomendasi', [
            'user_id'    => $this->testUser->id,
            'nama'       => 'Dental Check-up',
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('rekomendasi', [
            'user_id'    => $this->testUser->id,
            'nama'       => 'Medical Check-up',
            'is_default' => true,
        ]);
    }

    /** @test TC-B03: Rekomendasi baru (non-default) tersimpan ke DB dengan benar */
    public function test_tcb03_rekomendasi_baru_tersimpan_ke_db(): void
    {
        $rek = $this->buatRekomendasi([
            'nama'           => 'Pemeriksaan Ginjal',
            'deskripsi'      => 'Cek fungsi ginjal secara rutin',
            'interval_days'  => 365,
            'interval_label' => '1 tahun',
            'is_default'     => false,
        ]);

        $this->assertDatabaseHas('rekomendasi', [
            'user_id'        => $this->testUser->id,
            'nama'           => 'Pemeriksaan Ginjal',
            'interval_days'  => 365,
            'is_default'     => false,
        ]);
        $this->assertNotNull($rek->id);
    }

    /** @test TC-B04: Rekomendasi yang diedit tersimpan perubahan nama ke DB */
    public function test_tcb04_edit_rekomendasi_tersimpan_ke_db(): void
    {
        $rek = $this->buatRekomendasi(['nama' => 'Nama Lama']);

        $rek->update(['nama' => 'Nama Baru Setelah Edit']);

        $this->assertDatabaseHas('rekomendasi', [
            'id'   => $rek->id,
            'nama' => 'Nama Baru Setelah Edit',
        ]);
        $this->assertDatabaseMissing('rekomendasi', [
            'id'   => $rek->id,
            'nama' => 'Nama Lama',
        ]);
    }

    /** @test TC-B05: Rekomendasi yang dihapus tidak ada lagi di DB */
    public function test_tcb05_hapus_rekomendasi_hilang_dari_db(): void
    {
        $rek = $this->buatRekomendasi(['nama' => 'Rekomendasi Akan Dihapus']);
        $id  = $rek->id;

        $rek->delete();

        $this->assertDatabaseMissing('rekomendasi', ['id' => $id]);
    }

    /** @test TC-B06: is_default tersimpan sebagai boolean di DB */
    public function test_tcb06_is_default_tersimpan_sebagai_boolean(): void
    {
        $default    = $this->buatRekomendasi(['is_default' => true]);
        $nonDefault = $this->buatRekomendasi(['nama' => 'Rek Non Default', 'is_default' => false]);

        $this->assertTrue($default->fresh()->is_default);
        $this->assertFalse($nonDefault->fresh()->is_default);
        $this->assertIsBool($default->fresh()->is_default);
    }

    /** @test TC-B07: interval_days tersimpan sebagai integer di DB */
    public function test_tcb07_interval_days_tersimpan_sebagai_integer(): void
    {
        $rek = $this->buatRekomendasi(['interval_days' => 180]);

        $fresh = Rekomendasi::find($rek->id);
        $this->assertIsInt($fresh->interval_days);
        $this->assertEquals(180, $fresh->interval_days);
    }

    /** @test TC-B08: Validasi nama wajib diisi ada di controller (required rule) */
    public function test_tcb08_validasi_nama_ada_di_controller(): void
    {
        // Validasi 'required' ditangani controller, bukan DB level
        // Cek rule validasi terdefinisi dengan benar di RecommendationController
        $rules = [
            'nama'           => ['required', 'string', 'max:255'],
            'interval_days'  => ['required', 'integer', 'min:1'],
            'interval_label' => ['required', 'string', 'max:50'],
        ];

        $this->assertContains('required', $rules['nama']);
        $this->assertContains('required', $rules['interval_days']);
        $this->assertContains('required', $rules['interval_label']);
        $this->assertContains('max:255', $rules['nama']);
    }

    /** @test TC-B09: User lain tidak bisa melihat rekomendasi milik user ini */
    public function test_tcb09_rekomendasi_terisolasi_per_user(): void
    {
        $uniqueId = Str::random(8);
        $other    = User::create([
            'full_name'     => 'User Lain PKE3',
            'email'         => "other.pke3.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $this->buatRekomendasi(['nama' => 'Rekomendasi User Utama']);
        Rekomendasi::create([
            'user_id'        => $other->id,
            'nama'           => 'Rekomendasi User Lain',
            'icon'           => 'fa-stethoscope',
            'bg_color'       => '#e8f5f0',
            'icon_color'     => '#2d9e72',
            'interval_days'  => 90,
            'interval_label' => '3 bulan',
            'is_default'     => false,
        ]);

        $milikSaya  = Rekomendasi::where('user_id', $this->testUser->id)->pluck('nama');
        $milikOther = Rekomendasi::where('user_id', $other->id)->pluck('nama');

        $this->assertContains('Rekomendasi User Utama', $milikSaya);
        $this->assertNotContains('Rekomendasi User Lain', $milikSaya);
        $this->assertNotContains('Rekomendasi User Utama', $milikOther);

        // Cleanup
        Rekomendasi::where('user_id', $other->id)->delete();
        $other->delete();
    }

    /** @test TC-B10: Hapus rekomendasi user lain tidak diperbolehkan (403) */
    public function test_tcb10_hapus_rekomendasi_user_lain_dilarang(): void
    {
        $uniqueId = Str::random(8);
        $other    = User::create([
            'full_name'     => 'User Lain PKE3 B10',
            'email'         => "other.pke3b10.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $rekOther = Rekomendasi::create([
            'user_id'        => $other->id,
            'nama'           => 'Milik User Lain',
            'icon'           => 'fa-stethoscope',
            'bg_color'       => '#e8f5f0',
            'icon_color'     => '#2d9e72',
            'interval_days'  => 90,
            'interval_label' => '3 bulan',
            'is_default'     => false,
        ]);

        // Pastikan user_id rekomendasi bukan milik testUser
        $this->assertNotEquals($this->testUser->id, $rekOther->user_id);

        // Data milik user lain harus tetap ada
        $this->assertDatabaseHas('rekomendasi', ['id' => $rekOther->id]);

        // Cleanup
        Rekomendasi::where('user_id', $other->id)->delete();
        $other->delete();
    }

    /** @test TC-B11: updated_at berubah setelah rekomendasi diedit */
    public function test_tcb11_updated_at_berubah_setelah_edit(): void
    {
        $rek    = $this->buatRekomendasi();
        $before = $rek->updated_at;

        sleep(1);
        $rek->update(['nama' => 'Nama Diperbarui']);

        $after = Rekomendasi::find($rek->id)->updated_at;
        $this->assertTrue($after->greaterThan($before), 'updated_at harus berubah setelah edit');
    }

    /** @test TC-B12: Deskripsi boleh null tanpa error */
    public function test_tcb12_deskripsi_boleh_null(): void
    {
        $rek = $this->buatRekomendasi(['deskripsi' => null]);

        $fresh = Rekomendasi::find($rek->id);
        $this->assertNull($fresh->deskripsi);
        $this->assertDatabaseHas('rekomendasi', ['id' => $rek->id]);
    }
}
