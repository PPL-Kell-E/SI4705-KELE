<?php
// PKE-2: Browser test untuk Data Kesehatan Dasar
namespace Tests\Browser;

use App\Models\HealthData;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\DataKesehatanPage;
use Tests\DuskTestCase;

class PKE2_DataKesehatanTest extends DuskTestCase
{
    protected User $testUser;
    protected Profile $testProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE2',
            'email'         => "dusk.pke2.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $this->testProfile = Profile::create([
            'id'     => $this->testUser->id,
            'name'   => 'Test PKE2',
            'age'    => 25,
            'gender' => 'Laki-laki',
        ]);
    }

    protected function tearDown(): void
    {
        HealthData::where('user_id', $this->testUser->id)->delete();
        Profile::destroy($this->testUser->id);
        $this->testUser->delete();

        parent::tearDown();
    }

    private function loginAndGoToDataKesehatan(Browser $browser): Browser
    {
        return $browser->visit('/login')
                       ->waitFor('input[name="email"]', 5)
                       ->type('input[name="email"]', $this->testUser->email)
                       ->type('input[name="password"]', 'Password123!')
                       ->press('Sign In')
                       ->waitForLocation('/dashboard', 5)
                       ->visit('/data-kesehatan')
                       ->waitFor('.dk-card', 5);
    }

    private function createHealthData(array $override = []): HealthData
    {
        return HealthData::create(array_merge([
            'user_id'            => $this->testUser->id,
            'height_cm'          => 170,
            'weight_kg'          => 65,
            'blood_type'         => 'A',
            'allergies'          => ['Debu'],
            'chronic_conditions' => ['Asma'],
            'recorded_at'        => now(),
        ], $override));
    }

    // -------------------------------------------------------
    // TC-01: Menampilkan form
    // -------------------------------------------------------

    /** @test TC-01: Halaman Data Kesehatan menampilkan form dan tombol yang dibutuhkan */
    public function test_tc01_menampilkan_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->assertPathIs('/data-kesehatan')
                 ->assertSee('Buku Catatan Medis')
                 ->assertSee('Buat Catatan Baru')
                 ->assertVisible('.dk-card')
                 ->assertVisible('.dk-section')
                 ->screenshot('tc01-menampilkan-form');
        });
    }

    // -------------------------------------------------------
    // TC-02: Klik "Buat Catatan Baru"
    // -------------------------------------------------------

    /** @test TC-02: Klik tombol "Buat Catatan Baru" membuka form modal */
    public function test_tc02_klik_buat_catatan_baru(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage)
                 ->assertSee('Buat Catatan Baru');

            (new DataKesehatanPage)->openModal($browser);

            $browser->assertVisible('#inputModal')
                    ->screenshot('tc02-modal-terbuka');
        });
    }

    // -------------------------------------------------------
    // TC-03: Input data valid
    // -------------------------------------------------------

    /** @test TC-03: Semua field diisi dengan data valid, form menerima input */
    public function test_tc03_input_data_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputTB', '170')
                    ->type('@inputBB', '65')
                    ->select('@selectBlood', 'A')
                    ->type('@inputAlergi', 'Debu')
                    ->type('@inputCheckup', '2025-01-01')
                    ->type('@inputRiwayat', 'Asma')
                    ->screenshot('tc03-input-data-valid');
        });
    }

    // -------------------------------------------------------
    // TC-04: Klik Simpan
    // -------------------------------------------------------

    /** @test TC-04: Klik tombol "Selesai" memproses data */
    public function test_tc04_klik_simpan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputTB', '170')
                    ->type('@inputBB', '65')
                    ->select('@selectBlood', 'B')
                    ->screenshot('tc04-before-simpan');

            (new DataKesehatanPage)->submitForm($browser);

            $browser->screenshot('tc04-klik-simpan-diproses');
        });
    }

    // -------------------------------------------------------
    // TC-05: Data tersimpan ke database
    // -------------------------------------------------------

    /** @test TC-05: Setelah simpan, data masuk ke database */
    public function test_tc05_data_tersimpan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputTB', '168')
                    ->type('@inputBB', '60')
                    ->select('@selectBlood', 'O');

            (new DataKesehatanPage)->submitForm($browser);

            $browser->screenshot('tc05-data-tersimpan');
        });

        $this->assertDatabaseHas('health_data', [
            'user_id'   => $this->testUser->id,
            'height_cm' => 168,
            'weight_kg' => 60,
        ]);
    }

    // -------------------------------------------------------
    // TC-06: Notifikasi sukses muncul
    // -------------------------------------------------------

    /** @test TC-06: Setelah simpan berhasil, muncul pesan "Data berhasil disimpan" */
    public function test_tc06_notifikasi_sukses(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputTB', '175')
                    ->type('@inputBB', '70')
                    ->select('@selectBlood', 'AB');

            (new DataKesehatanPage)->submitForm($browser);

            $browser->assertSee('Data berhasil disimpan')
                    ->screenshot('tc06-notifikasi-sukses');
        });
    }

    // -------------------------------------------------------
    // TC-07: Auto-fill data saat form dibuka kembali
    // -------------------------------------------------------

    /** @test TC-07: Saat form dibuka lagi, data terakhir otomatis terisi */
    public function test_tc07_autofill_data(): void
    {
        $this->createHealthData([
            'height_cm' => 172,
            'weight_kg' => 68,
            'blood_type' => 'B',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->assertInputValue('@inputTB', '172')
                    ->assertInputValue('@inputBB', '68')
                    ->assertSelected('@selectBlood', 'B')
                    ->screenshot('tc07-autofill-data');
        });
    }

    // -------------------------------------------------------
    // TC-08: Input non-angka TB ditolak browser
    // -------------------------------------------------------

    /** @test TC-08: Mengetik huruf di field TB ditolak oleh input type=number */
    public function test_tc08_input_non_angka_tb_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputTB', 'abc');

            $value = $browser->value('@inputTB');

            $browser->screenshot('tc08-input-non-angka-tb');

            $this->assertEmpty(
                $value,
                "Field TB type=number seharusnya menolak 'abc', mendapat: '{$value}'"
            );
        });
    }

    // -------------------------------------------------------
    // TC-09: Input non-angka BB ditolak browser
    // -------------------------------------------------------

    /** @test TC-09: Mengetik simbol di field BB ditolak oleh input type=number */
    public function test_tc09_input_non_angka_bb_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputBB', '!@#');

            $value = $browser->value('@inputBB');

            $browser->screenshot('tc09-input-non-angka-bb');

            $this->assertEmpty(
                $value,
                "Field BB type=number seharusnya menolak '!@#', mendapat: '{$value}'"
            );
        });
    }

    // -------------------------------------------------------
    // TC-10: Input angka valid TB dan BB diterima
    // -------------------------------------------------------

    /** @test TC-10: TB=170 dan BB=65 diterima oleh form */
    public function test_tc10_input_angka_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->type('@inputTB', '170')
                    ->type('@inputBB', '65');

            $tb = $browser->value('@inputTB');
            $bb = $browser->value('@inputBB');

            $browser->screenshot('tc10-input-angka-valid');

            $this->assertEquals('170', $tb, "Field TB seharusnya menerima 170");
            $this->assertEquals('65',  $bb, "Field BB seharusnya menerima 65");
        });
    }

    // -------------------------------------------------------
    // TC-11: Field kosong tetap bisa disimpan
    // -------------------------------------------------------

    /** @test TC-11: TB/BB tidak diisi, sistem tetap menerima input (semua field nullable) */
    public function test_tc11_field_kosong_tetap_tersimpan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->select('@selectBlood', 'A')
                    ->type('@inputAlergi', 'Serbuk bunga');

            (new DataKesehatanPage)->submitForm($browser);

            $browser->assertSee('Data berhasil disimpan')
                    ->screenshot('tc11-field-kosong-tersimpan');
        });
    }

    // -------------------------------------------------------
    // TC-12: Simpan dengan semua field kosong
    // -------------------------------------------------------

    /** @test TC-12: Klik "Selesai" tanpa isi field apapun, data tetap tersimpan */
    public function test_tc12_simpan_semua_field_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);
            (new DataKesehatanPage)->submitForm($browser);

            $browser->assertSee('Data berhasil disimpan')
                    ->screenshot('tc12-simpan-field-kosong');
        });

        $this->assertDatabaseHas('health_data', [
            'user_id' => $this->testUser->id,
        ]);
    }

    // -------------------------------------------------------
    // TC-13: Tampilkan data kosong tetap kosong
    // -------------------------------------------------------

    /** @test TC-13: Field yang tidak diisi sebelumnya tetap kosong saat dibuka kembali */
    public function test_tc13_tampilkan_data_kosong(): void
    {
        $this->createHealthData([
            'height_cm' => null,
            'weight_kg' => null,
            'blood_type' => 'O',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $tb = $browser->value('@inputTB');
            $bb = $browser->value('@inputBB');

            $browser->screenshot('tc13-data-kosong-tetap-kosong');

            $this->assertEmpty($tb, "Field TB yang kosong seharusnya tetap kosong");
            $this->assertEmpty($bb, "Field BB yang kosong seharusnya tetap kosong");
        });
    }

    // -------------------------------------------------------
    // TC-14: Klik "Edit Catatan"
    // -------------------------------------------------------

    /** @test TC-14: Tombol berubah menjadi "Edit catatan" saat data sudah ada, klik membuka modal */
    public function test_tc14_klik_edit_catatan(): void
    {
        $this->createHealthData();

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->assertSee('Edit catatan');

            (new DataKesehatanPage)->openModal($browser);

            $browser->assertVisible('#inputModal')
                    ->screenshot('tc14-klik-edit-catatan');
        });
    }

    // -------------------------------------------------------
    // TC-15: Auto-fill edit
    // -------------------------------------------------------

    /** @test TC-15: Form edit terbuka dengan data sebelumnya sudah terisi otomatis */
    public function test_tc15_autofill_edit(): void
    {
        $this->createHealthData([
            'height_cm'  => 165,
            'weight_kg'  => 55,
            'blood_type' => 'AB',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->assertInputValue('@inputTB', '165')
                    ->assertInputValue('@inputBB', '55')
                    ->assertSelected('@selectBlood', 'AB')
                    ->screenshot('tc15-autofill-edit');
        });
    }

    // -------------------------------------------------------
    // TC-16: Ubah data
    // -------------------------------------------------------

    /** @test TC-16: User mengubah beberapa field, data baru diterima form */
    public function test_tc16_ubah_data(): void
    {
        $this->createHealthData([
            'height_cm' => 160,
            'weight_kg' => 50,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->clear('@inputTB')
                    ->type('@inputTB', '163')
                    ->clear('@inputBB')
                    ->type('@inputBB', '53')
                    ->screenshot('tc16-ubah-data');

            $this->assertEquals('163', $browser->value('@inputTB'));
            $this->assertEquals('53',  $browser->value('@inputBB'));
        });
    }

    // -------------------------------------------------------
    // TC-17: Simpan perubahan ke database
    // -------------------------------------------------------

    /** @test TC-17: Klik "Selesai" setelah ubah data, database diperbarui */
    public function test_tc17_simpan_perubahan(): void
    {
        $this->createHealthData([
            'height_cm' => 160,
            'weight_kg' => 50,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->clear('@inputTB')
                    ->type('@inputTB', '175')
                    ->clear('@inputBB')
                    ->type('@inputBB', '72');

            (new DataKesehatanPage)->submitForm($browser);

            $browser->assertSee('Data berhasil diedit')
                    ->screenshot('tc17-simpan-perubahan');
        });

        $this->assertDatabaseHas('health_data', [
            'user_id'   => $this->testUser->id,
            'height_cm' => 175,
            'weight_kg' => 72,
        ]);
    }

    // -------------------------------------------------------
    // TC-18: Verifikasi update tampil di halaman
    // -------------------------------------------------------

    /** @test TC-18: Setelah update, buka kembali halaman, data terbaru tampil */
    public function test_tc18_verifikasi_update(): void
    {
        $this->createHealthData([
            'height_cm' => 160,
            'weight_kg' => 50,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage);

            (new DataKesehatanPage)->openModal($browser);

            $browser->clear('@inputTB')
                    ->type('@inputTB', '178')
                    ->clear('@inputBB')
                    ->type('@inputBB', '75');

            (new DataKesehatanPage)->submitForm($browser);

            $browser->click('@btnContinue')
                    ->waitFor('.dk-card', 5)
                    ->assertSee('178')
                    ->assertSee('75')
                    ->screenshot('tc18-verifikasi-update');
        });
    }

    // -------------------------------------------------------
    // TC-19: Klik "Export PDF"
    // -------------------------------------------------------

    /** @test TC-19: Tombol Export PDF tersedia dan mengarah ke halaman export */
    public function test_tc19_klik_export_pdf(): void
    {
        $this->createHealthData();

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser)
                 ->on(new DataKesehatanPage)
                 ->assertVisible('@btnExport')
                 ->screenshot('tc19-tombol-export-tersedia');
        });
    }

    // -------------------------------------------------------
    // TC-20: Preview PDF — data tampil di halaman export
    // -------------------------------------------------------

    /** @test TC-20: Halaman export menampilkan data sesuai rekam medis */
    public function test_tc20_preview_pdf(): void
    {
        $this->createHealthData([
            'height_cm'  => 170,
            'weight_kg'  => 65,
            'blood_type' => 'A',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser);

            $browser->visit('/data-kesehatan/export')
                    ->waitFor('.page', 5)
                    ->assertSee('170')
                    ->assertSee('65')
                    ->assertSee('A')
                    ->screenshot('tc20-preview-pdf');
        });
    }

    // -------------------------------------------------------
    // TC-21: Cetak PDF — tombol print tersedia
    // -------------------------------------------------------

    /** @test TC-21: Halaman export memiliki tombol cetak dokumen */
    public function test_tc21_cetak_pdf(): void
    {
        $this->createHealthData();

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToDataKesehatan($browser);

            $browser->visit('/data-kesehatan/export')
                    ->waitFor('.page', 5)
                    ->assertVisible('.btn-print')
                    ->screenshot('tc21-cetak-pdf');
        });
    }

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /** @test TC-B01: Data health tersimpan ke tabel health_data dengan field yang benar */
    public function test_tcb01_data_tersimpan_ke_tabel_health_data(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            [
                'height_cm'   => 170,
                'weight_kg'   => 65,
                'blood_type'  => 'A',
                'recorded_at' => now(),
            ]
        );

        $this->assertDatabaseHas('health_data', [
            'user_id'    => $this->testUser->id,
            'blood_type' => 'A',
        ]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertEquals(170, (float) $record->height_cm);
        $this->assertEquals(65,  (float) $record->weight_kg);
    }

    /** @test TC-B02: updateOrCreate tidak membuat duplikat — hanya 1 record per user */
    public function test_tcb02_update_or_create_tidak_duplikat(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['height_cm' => 160, 'weight_kg' => 55, 'recorded_at' => now()]
        );
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['height_cm' => 175, 'weight_kg' => 72, 'recorded_at' => now()]
        );

        $count = HealthData::where('user_id', $this->testUser->id)->count();
        $this->assertEquals(1, $count, 'Harus hanya ada 1 record health_data per user');

        $this->assertDatabaseHas('health_data', [
            'user_id'   => $this->testUser->id,
            'height_cm' => 175,
            'weight_kg' => 72,
        ]);
    }

    /** @test TC-B03: Field height_cm dan weight_kg tersimpan sebagai numeric */
    public function test_tcb03_height_weight_tersimpan_sebagai_numeric(): void
    {
        $this->createHealthData(['height_cm' => 168, 'weight_kg' => 60]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsNumeric($record->height_cm);
        $this->assertIsNumeric($record->weight_kg);
        $this->assertEquals(168, (float) $record->height_cm);
        $this->assertEquals(60,  (float) $record->weight_kg);
    }

    /** @test TC-B04: Allergies dari string CSV disimpan sebagai JSON array */
    public function test_tcb04_allergies_disimpan_sebagai_array(): void
    {
        $allergies = array_map('trim', explode(',', 'Debu, Serbuk Bunga, Kacang'));

        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['allergies' => $allergies, 'recorded_at' => now()]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsArray($record->allergies);
        $this->assertContains('Debu', $record->allergies);
        $this->assertContains('Serbuk Bunga', $record->allergies);
        $this->assertContains('Kacang', $record->allergies);
    }

    /** @test TC-B05: Chronic conditions disimpan sebagai JSON array */
    public function test_tcb05_chronic_conditions_disimpan_sebagai_array(): void
    {
        $conditions = array_map('trim', explode(',', 'Asma, Diabetes'));

        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['chronic_conditions' => $conditions, 'recorded_at' => now()]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsArray($record->chronic_conditions);
        $this->assertContains('Asma', $record->chronic_conditions);
        $this->assertContains('Diabetes', $record->chronic_conditions);
    }

    /** @test TC-B06: Field nullable (height, weight, blood_type) boleh null tanpa error */
    public function test_tcb06_field_nullable_boleh_null(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            [
                'height_cm'   => null,
                'weight_kg'   => null,
                'blood_type'  => null,
                'recorded_at' => now(),
            ]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertNotNull($record, 'Record harus tetap tersimpan meski field null');
        $this->assertNull($record->height_cm);
        $this->assertNull($record->weight_kg);
        $this->assertNull($record->blood_type);
    }

    /** @test TC-B07: allergies kosong tersimpan sebagai array kosong bukan null */
    public function test_tcb07_allergies_kosong_tersimpan_sebagai_array_kosong(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['allergies' => [], 'recorded_at' => now()]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsArray($record->allergies);
        $this->assertEmpty($record->allergies);
    }

    /** @test TC-B08: BMI dihitung otomatis dari height dan weight */
    public function test_tcb08_bmi_dihitung_otomatis(): void
    {
        $this->createHealthData(['height_cm' => 170, 'weight_kg' => 65]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();

        // BMI = 65 / (1.70^2) = 22.5
        $expectedBmi = round(65 / (1.70 * 1.70), 1);
        $this->assertEquals($expectedBmi, $record->bmi);
    }

    /** @test TC-B09: Kategori BMI Normal untuk TB 170 BB 65 */
    public function test_tcb09_kategori_bmi_normal(): void
    {
        $this->createHealthData(['height_cm' => 170, 'weight_kg' => 65]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertEquals('Normal', $record->bmi_category);
    }

    /** @test TC-B10: Kategori BMI Kekurangan Berat Badan untuk BB sangat rendah */
    public function test_tcb10_kategori_bmi_kekurangan(): void
    {
        $this->createHealthData(['height_cm' => 170, 'weight_kg' => 45]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertEquals('Kekurangan Berat Badan', $record->bmi_category);
    }

    /** @test TC-B11: Kategori BMI Obesitas untuk BB sangat tinggi */
    public function test_tcb11_kategori_bmi_obesitas(): void
    {
        $this->createHealthData(['height_cm' => 170, 'weight_kg' => 100]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertEquals('Obesitas', $record->bmi_category);
    }

    /** @test TC-B12: BMI null jika height atau weight tidak diisi */
    public function test_tcb12_bmi_null_jika_height_atau_weight_kosong(): void
    {
        $this->createHealthData(['height_cm' => null, 'weight_kg' => null]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertNull($record->bmi);
        $this->assertEquals('-', $record->bmi_category);
    }

    /** @test TC-B13: recorded_at tersimpan sebagai datetime yang valid */
    public function test_tcb13_recorded_at_tersimpan_sebagai_datetime(): void
    {
        $waktu = now();
        $this->createHealthData(['recorded_at' => $waktu]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertNotNull($record->recorded_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $record->recorded_at);
    }

    /** @test TC-B14: Data update tidak mengubah health_data user lain */
    public function test_tcb14_update_tidak_mengubah_data_user_lain(): void
    {
        $uniqueId = Str::random(8);
        $other    = User::create([
            'full_name'     => 'User Lain PKE2',
            'email'         => "other.pke2.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
        HealthData::create([
            'user_id'    => $other->id,
            'height_cm'  => 155,
            'weight_kg'  => 50,
            'recorded_at'=> now(),
        ]);

        // Update data testUser
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['height_cm' => 180, 'weight_kg' => 80, 'recorded_at' => now()]
        );

        // Data user lain tidak berubah
        $otherRecord = HealthData::where('user_id', $other->id)->first();
        $this->assertEquals(155, (float) $otherRecord->height_cm);
        $this->assertEquals(50,  (float) $otherRecord->weight_kg);

        // Cleanup
        HealthData::where('user_id', $other->id)->delete();
        $other->delete();
    }
}
