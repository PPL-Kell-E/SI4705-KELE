<?php

namespace Tests\Browser;

use App\Models\HealthData;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ProfilePage;
use Tests\DuskTestCase;

class PKE1_ProfilTest extends DuskTestCase
{
    protected User $testUser;
    protected Profile $testProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE1',
            'email'         => "dusk.pke1.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $this->testProfile = Profile::create([
            'id'      => $this->testUser->id,
            'name'    => 'Budi Santoso',
            'age'     => 30,
            'gender'  => 'Laki-laki',
            'phone'   => '081234567890',
            'address' => 'Jl. Sudirman No. 1, Jakarta',
        ]);
    }

    protected function tearDown(): void
    {
        HealthData::where('user_id', $this->testUser->id)->delete();
        Profile::destroy($this->testUser->id);
        $this->testUser->delete();

        parent::tearDown();
    }

    private function loginAndGoToProfile(Browser $browser): Browser
    {
        return $browser->visit('/login')
                       ->waitFor('input[name="email"]', 5)
                       ->type('input[name="email"]', $this->testUser->email)
                       ->type('input[name="password"]', 'Password123!')
                       ->press('Sign In')
                       ->waitForLocation('/dashboard', 5)
                       ->visit('/profile')
                       ->waitFor('#viewMode', 5);
    }

    /** @test TC-01: Akses halaman profil */
    public function test_tc01_akses_halaman_profil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->assertSee('Profil Saya')
                 ->assertPathIs('/profile')
                 ->screenshot('tc01-akses-halaman-profil');
        });
    }

    /** @test TC-02: Data profil ditampilkan */
    public function test_tc02_data_profil_tampil(): void
    {
        $this->testProfile->update([
            'name'   => 'Andi Wijaya',
            'age'    => 25,
            'gender' => 'Laki-laki',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->assertSee('Andi Wijaya')
                 ->assertSee('25')
                 ->assertSee('Laki-laki')
                 ->screenshot('tc02-data-profil-tampil');
        });
    }

    /** @test TC-03: Mode read-only saat pertama dibuka */
    public function test_tc03_mode_read_only_saat_dibuka(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser);

            $browser->assertVisible('#viewMode')
                    ->assertMissing('#editMode')
                    ->assertVisible('#topbarBtnEdit')
                    ->screenshot('tc03-mode-read-only');
        });
    }

    /** @test TC-04: Profil tidak lengkap tampil tanpa error */
    public function test_tc04_profil_tidak_lengkap_tampil_tanpa_error(): void
    {
        $this->testProfile->update([
            'phone'      => null,
            'address'    => null,
            'birth_date' => null,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->assertDontSee('500')
                 ->assertDontSee('Whoops')
                 ->assertSee('-')
                 ->screenshot('tc04-profil-tidak-lengkap');
        });
    }

    /** @test TC-05: Klik Edit, form muncul */
    public function test_tc05_klik_ubah_profil_form_muncul(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->assertInEditMode($browser)
                 ->screenshot('tc05-form-edit-muncul');
        });
    }

    /** @test TC-06: Auto-fill data saat masuk mode edit */
    public function test_tc06_autofill_saat_masuk_edit_mode(): void
    {
        $this->testProfile->update([
            'name'   => 'Siti Rahayu',
            'age'    => 28,
            'gender' => 'Perempuan',
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->assertInputValue('@inputName', 'Siti Rahayu')
                 ->assertInputValue('@inputAge', '28')
                 ->assertSelected('@selectGender', 'Perempuan')
                 ->screenshot('tc06-autofill-edit-mode');
        });
    }

    /** @test TC-07: Input valid diterima */
    public function test_tc07_input_valid_data_diterima(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->clear('@inputName')
                 ->type('@inputName', 'Budi')
                 ->clear('@inputAge')
                 ->type('@inputAge', '21')
                 ->select('@selectGender', 'Laki-laki')
                 ->screenshot('tc07-before-submit-valid');

            (new ProfilePage)->clickSave($browser);

            $browser->assertPathIs('/profile')
                    ->assertDontSee('wajib diisi')
                    ->screenshot('tc07-input-valid-diterima');
        });
    }

    /** @test TC-10: Usia non-angka ditolak browser */
    public function test_tc10_usia_non_angka_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->clear('@inputAge')
                 ->type('@inputAge', 'abc');

            $ageValue = $browser->value('@inputAge');

            $browser->screenshot('tc10-usia-non-angka-ditolak');

            $this->assertEmpty(
                $ageValue,
                "Input type=number seharusnya menolak 'abc', namun mendapat: '{$ageValue}'"
            );
        });
    }

    /** @test TC-15: Simpan valid tersimpan ke database */
    public function test_tc15_simpan_valid_tersimpan_ke_database(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->clear('@inputName')
                 ->type('@inputName', 'Citra Dewi')
                 ->clear('@inputAge')
                 ->type('@inputAge', '32')
                 ->select('@selectGender', 'Perempuan')
                 ->clickSave($browser)
                 ->assertPathIs('/profile')
                 ->screenshot('tc15-simpan-valid-ke-database');
        });

        $this->assertDatabaseHas('profiles', [
            'id'     => $this->testUser->id,
            'name'   => 'Citra Dewi',
            'age'    => 32,
            'gender' => 'Perempuan',
        ]);
    }

    /** @test TC-16: Tidak simpan, data tidak berubah */
    public function test_tc16_tidak_simpan_data_tidak_berubah(): void
    {
        $this->testProfile->update([
            'name' => 'Nama Asli',
            'age'  => 40,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->clear('@inputName')
                 ->type('@inputName', 'Nama Diubah Sementara')
                 ->screenshot('tc16-before-cancel')
                 ->clickCancel($browser)
                 ->assertInViewMode($browser)
                 ->screenshot('tc16-setelah-batal-view-mode');
        });

        $this->assertDatabaseHas('profiles', [
            'id'   => $this->testUser->id,
            'name' => 'Nama Asli',
        ]);
    }

    /** @test TC-17: Notifikasi sukses muncul setelah simpan */
    public function test_tc17_notifikasi_sukses_muncul(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->clear('@inputName')
                 ->type('@inputName', 'Dedi Kurnia')
                 ->clear('@inputAge')
                 ->type('@inputAge', '45')
                 ->select('@selectGender', 'Laki-laki')
                 ->clickSave($browser)
                 ->waitForLocation('/profile', 5)
                 ->waitForText('Profil berhasil diperbarui', 5)
                 ->screenshot('tc17-notifikasi-sukses');
        });
    }

    /** @test TC-18: Data terupdate setelah refresh */
    public function test_tc18_data_terupdate_setelah_refresh(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAndGoToProfile($browser)
                 ->on(new ProfilePage)
                 ->clickEdit($browser)
                 ->clear('@inputName')
                 ->type('@inputName', 'Eka Putri')
                 ->clear('@inputAge')
                 ->type('@inputAge', '19')
                 ->select('@selectGender', 'Perempuan')
                 ->clickSave($browser)
                 ->waitForLocation('/profile', 5)
                 ->waitForText('Profil berhasil diperbarui', 5);

            $browser->refresh()
                    ->waitFor('#viewMode', 5)
                    ->assertSee('Eka Putri')
                    ->assertSee('19')
                    ->assertSee('Perempuan')
                    ->screenshot('tc18-data-terupdate-setelah-refresh');
        });
    }

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /** @test TC-B01: Profil dibuat otomatis (firstOrCreate) jika belum ada di DB */
    public function test_tcb01_profil_dibuat_otomatis_jika_belum_ada(): void
    {
        // Pastikan profil belum ada
        Profile::destroy($this->testUser->id);
        $this->assertDatabaseMissing('profiles', ['id' => $this->testUser->id]);

        // firstOrCreate dipanggil saat GET /profile (simulasikan via model langsung)
        Profile::firstOrCreate(
            ['id' => $this->testUser->id],
            ['name' => $this->testUser->full_name ?? 'Test', 'age' => 1, 'gender' => 'Lainnya']
        );

        $this->assertDatabaseHas('profiles', ['id' => $this->testUser->id]);
    }

    /** @test TC-B02: Data profil (name, age, gender) tersimpan ke tabel profiles dengan benar */
    public function test_tcb02_data_profil_tersimpan_ke_tabel_profiles(): void
    {
        $this->testProfile->update([
            'name'   => 'Citra Dewi',
            'age'    => 32,
            'gender' => 'Perempuan',
        ]);

        $this->assertDatabaseHas('profiles', [
            'id'     => $this->testUser->id,
            'name'   => 'Citra Dewi',
            'age'    => 32,
            'gender' => 'Perempuan',
        ]);
    }

    /** @test TC-B03: Field opsional (phone, address) tersimpan jika diisi */
    public function test_tcb03_field_opsional_tersimpan_jika_diisi(): void
    {
        $this->testProfile->update([
            'phone'   => '087700000000',
            'address' => 'Jl. Merdeka No. 10, Bandung',
        ]);

        $this->assertDatabaseHas('profiles', [
            'id'      => $this->testUser->id,
            'phone'   => '087700000000',
            'address' => 'Jl. Merdeka No. 10, Bandung',
        ]);
    }

    /** @test TC-B04: Data health (blood_type, height, weight) tersimpan ke tabel health_data */
    public function test_tcb04_data_kesehatan_tersimpan_ke_health_data(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            [
                'blood_type'  => 'A+',
                'height_cm'   => 170,
                'weight_kg'   => 65,
                'recorded_at' => now(),
            ]
        );

        $this->assertDatabaseHas('health_data', [
            'user_id'    => $this->testUser->id,
            'blood_type' => 'A+',
        ]);

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertEquals(170, (float) $record->height_cm);
        $this->assertEquals(65,  (float) $record->weight_kg);
    }

    /** @test TC-B05: Alergi disimpan sebagai JSON array, bukan string mentah */
    public function test_tcb05_allergies_disimpan_sebagai_array(): void
    {
        $allergies = array_map('trim', explode(',', 'Debu, Serbuk Bunga'));

        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['allergies' => $allergies, 'recorded_at' => now()]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsArray($record->allergies);
        $this->assertContains('Debu', $record->allergies);
        $this->assertContains('Serbuk Bunga', $record->allergies);
    }

    /** @test TC-B06: Kondisi kronis disimpan sebagai JSON array */
    public function test_tcb06_chronic_conditions_disimpan_sebagai_array(): void
    {
        $conditions = array_map('trim', explode(',', 'Diabetes, Hipertensi'));

        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['chronic_conditions' => $conditions, 'recorded_at' => now()]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsArray($record->chronic_conditions);
        $this->assertContains('Diabetes', $record->chronic_conditions);
        $this->assertContains('Hipertensi', $record->chronic_conditions);
    }

    /** @test TC-B07: updateOrCreate tidak membuat duplikat health_data */
    public function test_tcb07_health_data_diupdate_bukan_duplikat(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['blood_type' => 'B', 'recorded_at' => now()]
        );
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['blood_type' => 'O+', 'recorded_at' => now()]
        );

        $count = HealthData::where('user_id', $this->testUser->id)->count();
        $this->assertEquals(1, $count, 'Harus hanya ada 1 record health_data per user');

        $this->assertDatabaseHas('health_data', [
            'user_id'    => $this->testUser->id,
            'blood_type' => 'O+',
        ]);
    }

    /** @test TC-B08: UUID profil sesuai dengan user_id */
    public function test_tcb08_uuid_profil_sesuai_user_id(): void
    {
        $this->assertEquals($this->testUser->id, $this->testProfile->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $this->testProfile->id
        );
    }

    /** @test TC-B09: Kolom age tersimpan sebagai integer bukan string */
    public function test_tcb09_kolom_age_tersimpan_sebagai_integer(): void
    {
        $this->testProfile->update(['age' => 27]);

        $fresh = Profile::find($this->testUser->id);
        $this->assertIsInt($fresh->age);
        $this->assertEquals(27, $fresh->age);
    }

    /** @test TC-B10: Model Profile memiliki relasi hasMany ke HealthData */
    public function test_tcb10_relasi_profile_ke_health_data(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['blood_type' => 'AB', 'recorded_at' => now()]
        );

        $latest = $this->testProfile->latestHealthData;
        $this->assertNotNull($latest);
        $this->assertEquals('AB', $latest->blood_type);
    }

    /** @test TC-B11: updated_at profil berubah setelah data diperbarui */
    public function test_tcb11_updated_at_berubah_setelah_update(): void
    {
        $before = Profile::find($this->testUser->id)->updated_at;

        sleep(1);
        $this->testProfile->update(['name' => 'Nama Berubah']);

        $after = Profile::find($this->testUser->id)->updated_at;
        $this->assertTrue(
            $after->greaterThan($before),
            'updated_at seharusnya lebih baru setelah profil diperbarui'
        );
    }

    /** @test TC-B12: Update profil tidak mengubah data user lain */
    public function test_tcb12_update_tidak_mengubah_profil_user_lain(): void
    {
        $uniqueId = Str::random(8);
        $other    = User::create([
            'full_name'     => 'User Lain PKE1',
            'email'         => "other.pke1.{$uniqueId}@test.local",
            'password_hash' => \Illuminate\Support\Facades\Hash::make('Password123!'),
            'role'          => 'user',
        ]);
        $otherProfile = Profile::create([
            'id'     => $other->id,
            'name'   => 'Nama User Lain',
            'age'    => 40,
            'gender' => 'Laki-laki',
        ]);

        // Update hanya profil testUser
        $this->testProfile->update(['name' => 'Profil Saya Diubah']);

        // Profil user lain tidak berubah
        $this->assertDatabaseHas('profiles', [
            'id'   => $other->id,
            'name' => 'Nama User Lain',
        ]);

        // Cleanup
        $otherProfile->delete();
        $other->delete();
    }

    /** @test TC-B13: Profil dengan field opsional null tidak menyebabkan error di DB */
    public function test_tcb13_field_null_tidak_menyebabkan_error(): void
    {
        $this->testProfile->update([
            'phone'      => null,
            'address'    => null,
            'birth_date' => null,
            'avatar_url' => null,
        ]);

        $fresh = Profile::find($this->testUser->id);
        $this->assertNull($fresh->phone);
        $this->assertNull($fresh->address);
        $this->assertNull($fresh->birth_date);
        $this->assertDatabaseHas('profiles', ['id' => $this->testUser->id]);
    }

    /** @test TC-B14: HealthData dengan allergies kosong tersimpan sebagai array kosong */
    public function test_tcb14_allergies_kosong_tersimpan_sebagai_array_kosong(): void
    {
        HealthData::updateOrCreate(
            ['user_id' => $this->testUser->id],
            ['allergies' => [], 'recorded_at' => now()]
        );

        $record = HealthData::where('user_id', $this->testUser->id)->first();
        $this->assertIsArray($record->allergies);
        $this->assertEmpty($record->allergies);
    }
}
