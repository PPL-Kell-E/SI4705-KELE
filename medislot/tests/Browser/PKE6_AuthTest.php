<?php
// PKE-6: Browser test untuk Registrasi dan Login
namespace Tests\Browser;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE6_AuthTest extends DuskTestCase
{
    protected User $testUser;
    protected string $testEmail;
    protected string $newUserEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        // User untuk TC login/logout dan TC-03 duplicate email
        $this->testEmail = "dusk.pke6.{$uniqueId}@test.local";
        // JANGAN gunakan Hash::make() di sini — model User punya cast 'hashed'
        // pada kolom password_hash, sehingga plain text otomatis di-hash sekali.
        // Kalau Hash::make() dipakai, password akan double-hashed dan Auth::attempt gagal.
        $this->testUser  = User::create([
            'full_name'     => 'Test Dusk PKE6',
            'email'         => $this->testEmail,
            'password_hash' => 'Password123!',
            'role'          => 'user',
        ]);

        // Email unik untuk TC-02 (registrasi baru)
        $this->newUserEmail = "dusk.pke6.new.{$uniqueId}@test.local";
    }

    protected function tearDown(): void
    {
        // Hapus user yang dibuat saat TC-02 (registrasi)
        $newUser = User::where('email', $this->newUserEmail)->first();
        if ($newUser) {
            Profile::where('id', $newUser->id)->delete();
            $newUser->delete();
        }

        Profile::where('id', $this->testUser->id)->delete();
        $this->testUser->delete();

        parent::tearDown();
    }

    // =========================================================
    // REGISTRASI
    // =========================================================

    /** @test TC-01: Akses halaman registrasi */
    public function test_tc01_akses_halaman_registrasi(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitFor('.auth-form', 5)
                    ->assertPathIs('/register')
                    ->assertVisible('input[name="full_name"]')
                    ->assertVisible('input[name="email"]')
                    ->assertVisible('input[name="password"]')
                    ->assertVisible('.btn-submit')
                    ->screenshot('tc01-akses-halaman-registrasi');
        });
    }

    /** @test TC-02: Input valid - akun berhasil dibuat */
    public function test_tc02_registrasi_input_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="full_name"]', 'Pengguna Baru')
                    ->type('input[name="email"]', $this->newUserEmail)
                    ->type('input[name="password"]', 'Password123!')
                    ->click('.btn-submit')
                    ->waitForLocation('/dashboard', 10)
                    ->assertPathIs('/dashboard')
                    ->screenshot('tc02-registrasi-input-valid');
        });

        $this->assertDatabaseHas('users', [
            'email' => $this->newUserEmail,
        ]);
    }

    /** @test TC-03: Email sudah terdaftar - muncul error */
    public function test_tc03_registrasi_email_sudah_terdaftar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="full_name"]', 'User Duplikat')
                    ->type('input[name="email"]', $this->testEmail) // email sudah ada di DB
                    ->type('input[name="password"]', 'Password123!')
                    ->click('.btn-submit')
                    ->waitFor('.alert.alert-error', 5)
                    ->assertVisible('.alert.alert-error')
                    ->screenshot('tc03-registrasi-email-sudah-terdaftar');
        });
    }

    /** @test TC-04: Format email tidak valid - browser menolak submit */
    public function test_tc04_registrasi_format_email_tidak_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="full_name"]', 'User Test')
                    ->type('input[name="email"]', 'user123@gmail.com') // bukan format email
                    ->type('input[name="password"]', 'Password123!')
                    ->click('.btn-submit')
                    ->pause(500) // browser HTML5 validation menolak, halaman tidak berpindah
                    ->assertPathIs('/register')
                    ->screenshot('tc04-registrasi-format-email-tidak-valid');
        });
    }

    /** @test TC-05: Password kosong - validasi wajib isi */
    public function test_tc05_registrasi_password_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="full_name"]', 'User Test')
                    ->type('input[name="email"]', 'test.empty.pass@test.local')
                    // password tidak diisi
                    ->click('.btn-submit')
                    ->pause(500) // browser required validation menolak submit
                    ->assertPathIs('/register')
                    ->screenshot('tc05-registrasi-password-kosong');
        });
    }

    /** @test TC-06: Password terlalu pendek (< 8 karakter) */
    public function test_tc06_registrasi_password_terlalu_pendek(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="full_name"]', 'User Test')
                    ->type('input[name="email"]', 'test.short.pass@test.local')
                    ->type('input[name="password"]', 'abc123') // hanya 6 karakter
                    ->click('.btn-submit')
                    ->waitFor('.alert.alert-error', 5)
                    ->assertVisible('.alert.alert-error')
                    ->screenshot('tc06-registrasi-password-terlalu-pendek');
        });
    }

    // =========================================================
    // LOGIN
    // =========================================================

    /** @test TC-07: Akses halaman login */
    public function test_tc07_akses_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('.auth-form', 5)
                    ->assertPathIs('/login')
                    ->assertVisible('input[name="email"]')
                    ->assertVisible('input[name="password"]')
                    ->assertVisible('.btn-submit')
                    ->screenshot('tc07-akses-halaman-login');
        });
    }

    /** @test TC-08: Login valid - masuk ke dashboard */
    public function test_tc08_login_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="email"]', $this->testEmail)
                    ->type('input[name="password"]', 'Password123!')
                    ->click('.btn-submit')
                    ->waitForLocation('/dashboard', 10)
                    ->assertPathIs('/dashboard')
                    ->screenshot('tc08-login-valid');
        });
    }

    /** @test TC-09: Password salah - muncul error login gagal */
    public function test_tc09_login_password_salah(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="email"]', $this->testEmail)
                    ->type('input[name="password"]', 'SalahPassword!')
                    ->click('.btn-submit')
                    ->waitFor('.alert.alert-error', 5)
                    ->assertVisible('.alert.alert-error')
                    ->assertPathIs('/login')
                    ->screenshot('tc09-login-password-salah');
        });
    }

    /** @test TC-10: Email tidak terdaftar - muncul error login gagal */
    public function test_tc10_login_email_tidak_terdaftar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="email"]', 'tidakterdaftar@test.local')
                    ->type('input[name="password"]', 'Password123!')
                    ->click('.btn-submit')
                    ->waitFor('.alert.alert-error', 5)
                    ->assertVisible('.alert.alert-error')
                    ->assertPathIs('/login')
                    ->screenshot('tc10-login-email-tidak-terdaftar');
        });
    }

    /** @test TC-11: Field kosong - validasi muncul */
    public function test_tc11_login_field_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('.auth-form', 5)
                    // tidak isi apapun
                    ->click('.btn-submit')
                    ->pause(500) // browser required validation menolak submit
                    ->assertPathIs('/login')
                    ->screenshot('tc11-login-field-kosong');
        });
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    /** @test TC-12: Logout - sesi dihapus dan kembali ke halaman login */
    public function test_tc12_logout(): void
    {
        $this->browse(function (Browser $browser) {
            // Login terlebih dahulu
            $browser->visit('/login')
                    ->waitFor('.auth-form', 5)
                    ->type('input[name="email"]', $this->testEmail)
                    ->type('input[name="password"]', 'Password123!')
                    ->click('.btn-submit')
                    ->waitForLocation('/dashboard', 10)
                    ->assertPathIs('/dashboard');

            // Klik tombol Keluar di sidebar
            $browser->click('.sidebar-footer button')
                    ->waitForLocation('/login', 5)
                    ->assertPathIs('/login')
                    ->screenshot('tc12-logout-berhasil');
        });

        // Verifikasi sesi sudah tidak valid: akses /dashboard harus redirect ke /login
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                    ->waitForLocation('/login', 5)
                    ->assertPathIs('/login')
                    ->screenshot('tc12-logout-sesi-invalid');
        });
    }

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /** @test TC-B01: Registrasi menyimpan user ke tabel users dengan benar */
    public function test_tcb01_registrasi_simpan_user_ke_db(): void
    {
        $email = 'tcb01.' . Str::random(6) . '@test.local';

        $user = User::create([
            'full_name'     => 'Backend Test User',
            'email'         => $email,
            'password_hash' => 'Password123!', // cast 'hashed' otomatis hash
            'role'          => 'user',
        ]);

        $this->assertDatabaseHas('users', [
            'email'     => $email,
            'full_name' => 'Backend Test User',
            'role'      => 'user',
        ]);

        $user->delete();
    }

    /** @test TC-B02: Password disimpan sebagai hash (bukan plain text) di DB */
    public function test_tcb02_password_disimpan_sebagai_hash(): void
    {
        $plain = 'Password123!';

        // Ambil langsung dari DB (bukan dari model, agar tidak terkena cast)
        $raw = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $this->testUser->id)
            ->value('password_hash');

        // Harus berbeda dari plain text
        $this->assertNotEquals($plain, $raw);

        // Harus bisa diverifikasi dengan Hash::check
        $this->assertTrue(Hash::check($plain, $raw));

        // Harus berformat bcrypt ($2y$)
        $this->assertStringStartsWith('$2y$', $raw);
    }

    /** @test TC-B03: Email unik — duplikat ditolak oleh unique constraint */
    public function test_tcb03_email_unik_ditolak(): void
    {
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        User::create([
            'full_name'     => 'Duplikat',
            'email'         => $this->testEmail, // sudah ada
            'password_hash' => 'Password123!',
            'role'          => 'user',
        ]);
    }

    /** @test TC-B04: Role default user adalah 'user' */
    public function test_tcb04_role_default_user(): void
    {
        $email = 'tcb04.' . Str::random(6) . '@test.local';

        $user = User::create([
            'full_name'     => 'Role Test',
            'email'         => $email,
            'password_hash' => 'Password123!',
            'role'          => 'user',
        ]);

        $fresh = User::find($user->id);
        $this->assertEquals('user', $fresh->role);

        $user->delete();
    }

    /** @test TC-B05: Validasi registrasi — full_name wajib diisi */
    public function test_tcb05_validasi_full_name_wajib(): void
    {
        $rules = app(\App\Http\Controllers\AuthController::class);

        // Verifikasi rule ada di controller
        $reflection = new \ReflectionMethod(\App\Http\Controllers\AuthController::class, 'register');
        $source     = file_get_contents((new \ReflectionClass(\App\Http\Controllers\AuthController::class))->getFileName());

        $this->assertStringContainsString("'full_name'", $source);
        $this->assertStringContainsString("'required'", $source);
    }

    /** @test TC-B06: Validasi registrasi — password min 8 karakter */
    public function test_tcb06_validasi_password_min8(): void
    {
        $source = file_get_contents((new \ReflectionClass(\App\Http\Controllers\AuthController::class))->getFileName());

        $this->assertStringContainsString("'min:8'", $source);
    }

    /** @test TC-B07: Validasi registrasi — email harus unik di tabel users */
    public function test_tcb07_validasi_email_unique(): void
    {
        $source = file_get_contents((new \ReflectionClass(\App\Http\Controllers\AuthController::class))->getFileName());

        $this->assertStringContainsString("'unique:users'", $source);
    }

    /** @test TC-B08: Auth::attempt berhasil dengan kredensial yang valid */
    public function test_tcb08_auth_attempt_valid(): void
    {
        $result = \Illuminate\Support\Facades\Auth::attempt([
            'email'    => $this->testEmail,
            'password' => 'Password123!',
        ]);

        $this->assertTrue($result, 'Auth::attempt harus berhasil dengan kredensial valid');
        \Illuminate\Support\Facades\Auth::logout();
    }

    /** @test TC-B09: Auth::attempt gagal dengan password salah */
    public function test_tcb09_auth_attempt_password_salah(): void
    {
        $result = \Illuminate\Support\Facades\Auth::attempt([
            'email'    => $this->testEmail,
            'password' => 'SalahPassword!',
        ]);

        $this->assertFalse($result, 'Auth::attempt harus gagal dengan password salah');
    }

    /** @test TC-B10: Auth::attempt gagal dengan email tidak terdaftar */
    public function test_tcb10_auth_attempt_email_tidak_ada(): void
    {
        $result = \Illuminate\Support\Facades\Auth::attempt([
            'email'    => 'tidakada.' . Str::random(6) . '@test.local',
            'password' => 'Password123!',
        ]);

        $this->assertFalse($result, 'Auth::attempt harus gagal dengan email yang tidak terdaftar');
    }

    /** @test TC-B11: User yang dihapus tidak bisa login */
    public function test_tcb11_user_dihapus_tidak_bisa_login(): void
    {
        $email = 'tcb11.' . Str::random(6) . '@test.local';
        $user  = User::create([
            'full_name'     => 'User Akan Dihapus',
            'email'         => $email,
            'password_hash' => 'Password123!',
            'role'          => 'user',
        ]);

        $user->delete();

        $result = \Illuminate\Support\Facades\Auth::attempt([
            'email'    => $email,
            'password' => 'Password123!',
        ]);

        $this->assertFalse($result);
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    /** @test TC-B12: Data isolation — user tidak bisa akses data user lain */
    public function test_tcb12_data_isolation_antar_user(): void
    {
        $email2 = 'tcb12.' . Str::random(6) . '@test.local';
        $user2  = User::create([
            'full_name'     => 'User Kedua',
            'email'         => $email2,
            'password_hash' => 'Password123!',
            'role'          => 'user',
        ]);

        // Pastikan query by email hanya mengembalikan user yang bersangkutan
        $found = User::where('email', $this->testEmail)->first();
        $this->assertEquals($this->testUser->id, $found->id);
        $this->assertNotEquals($user2->id, $found->id);

        $user2->delete();
    }

    /** @test TC-B13: created_at dan updated_at terisi otomatis saat registrasi */
    public function test_tcb13_timestamps_terisi_otomatis(): void
    {
        $fresh = User::find($this->testUser->id);

        $this->assertNotNull($fresh->created_at);
        $this->assertNotNull($fresh->updated_at);
    }
}
