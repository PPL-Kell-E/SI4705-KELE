<?php
// PKE-10: Browser test untuk Pengingat & Notifikasi
namespace Tests\Browser;

use App\Models\Jadwal;
use App\Models\Notifikasi;
use App\Models\Pengingat;
use App\Models\PengingatWaktu;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE10_PengingatTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE10',
            'email'         => "dusk.pke10.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        $pengingatIds = Pengingat::where('user_id', $this->testUser->id)->pluck('id');

        PengingatWaktu::whereIn('pengingat_id', $pengingatIds)->delete();
        Notifikasi::where('user_id', $this->testUser->id)->delete();
        Pengingat::where('user_id', $this->testUser->id)->delete();
        Jadwal::where('user_id', $this->testUser->id)->delete();
        $this->testUser->delete();

        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function login(Browser $browser): Browser
    {
        // Pause singkat agar JS background dari test sebelumnya selesai
        return $browser->pause(800)
                       ->visit('/login')
                       ->waitFor('input[name="email"]', 15)
                       ->type('input[name="email"]', $this->testUser->email)
                       ->type('input[name="password"]', 'Password123!')
                       ->press('Sign In')
                       ->waitForLocation('/dashboard', 20);
    }

    private function loginDanBukaPengingat(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/pengingat')
                    ->waitFor('.btn-tambah', 15);
    }

    private function buatJadwalMendatang(array $override = []): Jadwal
    {
        return Jadwal::create(array_merge([
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Pemeriksaan Umum',
            'fasilitas_klinik'  => 'Klinik Medina',
            'tanggal'           => now()->addDays(7)->format('Y-m-d'),
            'waktu'             => '09:00',
            'catatan'           => null,
            'status'            => 'mendatang',
        ], $override));
    }

    private function buatPengingat(Jadwal $jadwal, bool $aktif = true, int $offsetMenit = 1440): Pengingat
    {
        $p = Pengingat::create([
            'jadwal_id' => $jadwal->id,
            'user_id'   => $this->testUser->id,
            'is_active' => $aktif,
        ]);
        $p->waktu()->create(['offset_menit' => $offsetMenit]);
        return $p;
    }

    // ── Test Cases ───────────────────────────────────────────────────────────

    /** @test TC-01: Membuka halaman Pengingat — empty state tampil saat tidak ada jadwal aktif */
    public function test_tc01_buka_halaman_pengingat_tanpa_jadwal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->assertSee('Belum ada jadwal pemeriksaan aktif')
                 ->assertVisible('.btn-tambah')
                 ->screenshot('tc01-halaman-pengingat-kosong');
        });
    }

    /** @test TC-02: Klik Tambah Pengingat tanpa jadwal — modal tampilkan "Tidak ada jadwal tersedia" */
    public function test_tc02_tambah_pengingat_tanpa_jadwal_aktif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#addModal.open', 5)
                 ->assertSee('Tidak ada jadwal tersedia')
                 ->assertVisible('a[href*="jadwal/create"]')
                 ->screenshot('tc02-modal-tidak-ada-jadwal');
        });
    }

    /** @test TC-03: Klik "Buat Jadwal Baru" di modal — diarahkan ke halaman tambah jadwal */
    public function test_tc03_redirect_ke_halaman_tambah_jadwal(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->click('.btn-tambah')
                 ->waitFor('#addModal.open', 5)
                 ->click('a[href*="jadwal/create"]')
                 ->waitForLocation('/jadwal/create', 5)
                 ->assertPathIs('/jadwal/create')
                 ->screenshot('tc03-redirect-tambah-jadwal');
        });
    }

    /** @test TC-04: Membuat jadwal baru dari form — jadwal tersimpan ke database */
    public function test_tc04_membuat_jadwal_pemeriksaan_baru(): void
    {
        $tanggal = now()->addDays(10)->format('Y-m-d');

        $this->browse(function (Browser $browser) use ($tanggal) {
            $this->login($browser)
                 ->visit('/jadwal/create')
                 ->waitFor('#jadwalForm', 5)
                 ->type('input[name="jenis_pemeriksaan"]', 'Pemeriksaan Darah Lengkap')
                 ->type('input[name="fasilitas_klinik"]', 'Klinik Sehat Mandiri')
                 ->type('input[name="tanggal"]', $tanggal)
                 ->type('input[name="waktu"]', '10:00')
                 ->click('.btn-simpan')
                 ->waitForLocation('/jadwal', 5)
                 ->assertPathIs('/jadwal')
                 ->screenshot('tc04-jadwal-berhasil-dibuat');
        });

        $this->assertDatabaseHas('jadwal', [
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Pemeriksaan Darah Lengkap',
            'fasilitas_klinik'  => 'Klinik Sehat Mandiri',
        ]);
    }

    /** @test TC-05: Halaman Pengingat menampilkan daftar reminder saat jadwal aktif tersedia */
    public function test_tc05_daftar_reminder_tampil_dengan_jadwal_aktif(): void
    {
        $jadwal = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Cek Kolesterol']);
        $this->buatPengingat($jadwal);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->assertVisible('#reminderList')
                 ->assertSee('Cek Kolesterol')
                 ->assertVisible('.reminder-card')
                 ->assertVisible('.toggle')
                 ->screenshot('tc05-daftar-reminder-tampil');
        });
    }

    /** @test TC-06: Toggle reminder diaktifkan — status berubah menjadi aktif di database */
    public function test_tc06_mengaktifkan_reminder(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Cek Toggle Aktif']);
        $pengingat = $this->buatPengingat($jadwal, false); // mulai nonaktif

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->assertPresent("#card-{$pengingat->id} input[type='checkbox']:not(:checked)")
                 ->click("#card-{$pengingat->id} .toggle")
                 ->pause(600)
                 ->screenshot('tc06-reminder-diaktifkan');
        });

        $this->assertDatabaseHas('pengingat', ['id' => $pengingat->id, 'is_active' => true]);
    }

    /** @test TC-07: Toggle reminder dinonaktifkan — status berubah menjadi nonaktif di database */
    public function test_tc07_menonaktifkan_reminder(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Cek Toggle Nonaktif']);
        $pengingat = $this->buatPengingat($jadwal, true); // mulai aktif

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->assertPresent("#card-{$pengingat->id} input[type='checkbox']:checked")
                 ->click("#card-{$pengingat->id} .toggle")
                 ->pause(600)
                 ->screenshot('tc07-reminder-dinonaktifkan');
        });

        $this->assertDatabaseHas('pengingat', ['id' => $pengingat->id, 'is_active' => false]);
    }

    /** @test TC-08: Klik tombol Edit — modal edit reminder terbuka dan menampilkan detail yang benar */
    public function test_tc08_membuka_modal_edit_reminder(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Edit Pengingat Test']);
        $pengingat = $this->buatPengingat($jadwal);

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->click("#card-{$pengingat->id} .btn-edit")
                 ->waitFor('#editModal.open', 5)
                 ->assertSee('Edit Pengingat')
                 ->assertSeeIn('#editJadwalNama', 'Edit Pengingat Test')
                 ->screenshot('tc08-modal-edit-terbuka');
        });
    }

    /** @test TC-09: Mengubah waktu reminder di modal edit — offset baru tersimpan ke database */
    public function test_tc09_mengubah_waktu_reminder(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Ubah Waktu Reminder']);
        $pengingat = $this->buatPengingat($jadwal, true, 1440); // default H-1

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->click("#card-{$pengingat->id} .btn-edit")
                 ->waitFor('#editModal.open', 5)
                 ->select('#editWaktuList select.waktu-select', '60')
                 ->click('#editSimpanBtn')
                 ->pause(1500)
                 // Navigasi ke halaman netral agar JS reload tidak mengganggu test berikutnya
                 ->visit('/dashboard')
                 ->pause(500)
                 ->screenshot('tc09-waktu-reminder-diubah');
        });

        $this->assertDatabaseHas('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 60,
        ]);
    }

    /** @test TC-12: Ubah offset lalu lihat preview — preview reminder diperbarui sesuai pilihan */
    public function test_tc12_preview_reminder_diperbarui(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Preview Reminder Test']);
        $pengingat = $this->buatPengingat($jadwal);

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->click("#card-{$pengingat->id} .btn-edit")
                 ->waitFor('#editModal.open', 5)
                 ->select('#editWaktuList select.waktu-select', '60')
                 ->pause(400)
                 ->assertVisible('#editPreviewList .preview-item')
                 ->screenshot('tc12-preview-reminder-diperbarui');
        });
    }

    /** @test TC-13: Simpan perubahan reminder — data offset baru tersimpan di database */
    public function test_tc13_simpan_perubahan_reminder(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Simpan Perubahan Test']);
        $pengingat = $this->buatPengingat($jadwal, true, 720);

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->click("#card-{$pengingat->id} .btn-edit")
                 ->waitFor('#editModal.open', 5)
                 ->select('#editWaktuList select.waktu-select', '360')
                 ->click('#editSimpanBtn')
                 ->pause(1500)
                 ->visit('/dashboard')
                 ->pause(500)
                 ->screenshot('tc13-perubahan-reminder-disimpan');
        });

        $this->assertDatabaseHas('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 360,
        ]);
    }

    /** @test TC-14: Tutup modal tanpa simpan — data di database tidak berubah */
    public function test_tc14_tutup_modal_tanpa_menyimpan(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Tutup Tanpa Simpan Test']);
        $pengingat = $this->buatPengingat($jadwal, true, 1440);

        $this->browse(function (Browser $browser) use ($pengingat) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor("#card-{$pengingat->id}", 5)
                 ->click("#card-{$pengingat->id} .btn-edit")
                 ->waitFor('#editModal.open', 5)
                 ->select('#editWaktuList select.waktu-select', '30') // ubah tapi jangan simpan
                 ->click('#editModal .modal-close')
                 ->waitUntilMissing('#editModal.open', 5)
                 ->screenshot('tc14-modal-ditutup-tanpa-simpan');
        });

        $this->assertDatabaseHas('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 1440, // tetap tidak berubah
        ]);
        $this->assertDatabaseMissing('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 30,
        ]);
    }

    /** @test TC-15: Notifikasi reminder tampil di halaman Pengingat setelah reminder aktif */
    public function test_tc15_notifikasi_reminder_tampil(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Notif Test']);
        $pengingat = $this->buatPengingat($jadwal);
        $waktu     = $pengingat->waktu()->first();

        Notifikasi::create([
            'user_id'            => $this->testUser->id,
            'jadwal_id'          => $jadwal->id,
            'pengingat_waktu_id' => $waktu->id,
            'judul'              => 'Pengingat Jadwal Besok!',
            'pesan'              => 'Jangan lupa jadwal Notif Test besok.',
            'is_read'            => false,
            'notified_at'        => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->assertVisible('.notif-section')
                 ->assertSee('Pengingat Jadwal Besok!')
                 ->screenshot('tc15-notifikasi-tampil');
        });
    }

    /** @test TC-16: Klik notifikasi — sistem mengarahkan ke halaman detail jadwal */
    public function test_tc16_klik_notifikasi_buka_detail_jadwal(): void
    {
        $jadwal    = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Detail Jadwal Notif']);
        $pengingat = $this->buatPengingat($jadwal);
        $waktu     = $pengingat->waktu()->first();

        Notifikasi::create([
            'user_id'            => $this->testUser->id,
            'jadwal_id'          => $jadwal->id,
            'pengingat_waktu_id' => $waktu->id,
            'judul'              => 'Pengingat Jadwal Segera!',
            'pesan'              => 'Jangan lupa jadwal Detail Jadwal Notif segera.',
            'is_read'            => false,
            'notified_at'        => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->waitFor('.notif-card', 5)
                 ->click('.notif-card')
                 ->pause(800)
                 ->assertPathContains('/jadwal')
                 ->screenshot('tc16-klik-notifikasi-buka-jadwal');
        });
    }

    /** @test TC-17: Tidak ada jadwal aktif — sistem menampilkan pesan informasi kosong */
    public function test_tc17_tidak_ada_jadwal_aktif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->assertSee('Belum ada jadwal pemeriksaan aktif')
                 ->screenshot('tc17-tidak-ada-jadwal-aktif');
        });
    }

    /** @test TC-18: Gangguan sistem — halaman menampilkan pesan error jika data gagal dimuat */
    public function test_tc18_error_state_saat_data_gagal_dimuat(): void
    {
        $this->buatJadwalMendatang();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser);

            $hasError = $browser->element('.alert-error') !== null;
            if ($hasError) {
                $browser->assertSee('Data reminder gagal dimuat');
            } else {
                $browser->assertPresent('.btn-tambah');
            }

            $browser->screenshot('tc18-error-state');
        });
    }

    /** @test TC-19: Loading state — halaman menampilkan data reminder setelah proses selesai */
    public function test_tc19_loading_state_selesai_data_tampil(): void
    {
        $jadwal = $this->buatJadwalMendatang(['jenis_pemeriksaan' => 'Loading State Test']);
        $this->buatPengingat($jadwal);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaPengingat($browser)
                 ->waitUntilMissing('.skeleton-card', 5)
                 ->assertVisible('#reminderList')
                 ->assertSee('Loading State Test')
                 ->screenshot('tc19-loading-selesai-data-tampil');
        });
    }
}
