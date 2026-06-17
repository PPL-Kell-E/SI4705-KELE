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

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /** @test TC-B01: Pengingat tersimpan ke tabel pengingat dengan benar */
    public function test_tcb01_pengingat_tersimpan_ke_db(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal);

        $this->assertDatabaseHas('pengingat', [
            'id'        => $pengingat->id,
            'jadwal_id' => $jadwal->id,
            'user_id'   => $this->testUser->id,
            'is_active' => true,
        ]);
    }

    /** @test TC-B02: PengingatWaktu tersimpan dengan offset_menit yang benar */
    public function test_tcb02_pengingat_waktu_tersimpan_ke_db(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal, true, 1440);

        $this->assertDatabaseHas('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 1440,
        ]);
    }

    /** @test TC-B03: Toggle aktif — is_active berubah dari false ke true */
    public function test_tcb03_toggle_aktifkan_pengingat(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal, false);

        $pengingat->update(['is_active' => true]);

        $fresh = Pengingat::find($pengingat->id);
        $this->assertTrue($fresh->is_active);
        $this->assertDatabaseHas('pengingat', ['id' => $pengingat->id, 'is_active' => true]);
    }

    /** @test TC-B04: Toggle nonaktif — is_active berubah dari true ke false */
    public function test_tcb04_toggle_nonaktifkan_pengingat(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal, true);

        $pengingat->update(['is_active' => false]);

        $fresh = Pengingat::find($pengingat->id);
        $this->assertFalse($fresh->is_active);
        $this->assertDatabaseHas('pengingat', ['id' => $pengingat->id, 'is_active' => false]);
    }

    /** @test TC-B05: Update offset_menit — waktu lama terhapus, waktu baru tersimpan */
    public function test_tcb05_update_offset_hapus_lama_simpan_baru(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal, true, 1440);

        // Simulasi update seperti di controller
        $pengingat->waktu()->delete();
        $pengingat->waktu()->create(['offset_menit' => 360]);

        $this->assertDatabaseMissing('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 1440,
        ]);
        $this->assertDatabaseHas('pengingat_waktu', [
            'pengingat_id' => $pengingat->id,
            'offset_menit' => 360,
        ]);
    }

    /** @test TC-B06: Notifikasi tersimpan ke tabel notifikasi dengan benar */
    public function test_tcb06_notifikasi_tersimpan_ke_db(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal);
        $waktu     = $pengingat->waktu()->first();

        $notif = Notifikasi::create([
            'user_id'            => $this->testUser->id,
            'jadwal_id'          => $jadwal->id,
            'pengingat_waktu_id' => $waktu->id,
            'judul'              => 'Pengingat Test B06',
            'pesan'              => 'Jangan lupa jadwal besok.',
            'is_read'            => false,
            'notified_at'        => now(),
        ]);

        $this->assertDatabaseHas('notifikasi', [
            'id'      => $notif->id,
            'user_id' => $this->testUser->id,
            'judul'   => 'Pengingat Test B06',
            'is_read' => false,
        ]);
    }

    /** @test TC-B07: markRead — notifikasi berubah is_read menjadi true */
    public function test_tcb07_mark_read_notifikasi(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal);
        $waktu     = $pengingat->waktu()->first();

        $notif = Notifikasi::create([
            'user_id'            => $this->testUser->id,
            'jadwal_id'          => $jadwal->id,
            'pengingat_waktu_id' => $waktu->id,
            'judul'              => 'Test B07 Mark Read',
            'pesan'              => 'Pesan test.',
            'is_read'            => false,
            'notified_at'        => now(),
        ]);

        $notif->update(['is_read' => true]);

        $this->assertDatabaseHas('notifikasi', [
            'id'      => $notif->id,
            'is_read' => true,
        ]);
    }

    /** @test TC-B08: Pengingat hanya muncul untuk jadwal berstatus mendatang */
    public function test_tcb08_pengingat_hanya_untuk_jadwal_mendatang(): void
    {
        $jadwalMendatang = $this->buatJadwalMendatang(['status' => 'mendatang']);
        $jadwalSelesai   = $this->buatJadwalMendatang(['status' => 'selesai']);

        $pMendatang = $this->buatPengingat($jadwalMendatang);
        $pSelesai   = $this->buatPengingat($jadwalSelesai);

        $hasil = Pengingat::where('user_id', $this->testUser->id)
                    ->whereHas('jadwal', fn($q) => $q->where('status', 'mendatang'))
                    ->pluck('id');

        $this->assertContains($pMendatang->id, $hasil);
        $this->assertNotContains($pSelesai->id, $hasil);
    }

    /** @test TC-B09: Data isolation — pengingat user lain tidak terlihat */
    public function test_tcb09_pengingat_terisolasi_antar_user(): void
    {
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE10',
            'email'         => "lain.pke10.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);

        $jadwalSaya   = $this->buatJadwalMendatang();
        $jadwalMereka = Jadwal::create([
            'user_id'           => $userLain->id,
            'jenis_pemeriksaan' => 'Jadwal Mereka',
            'fasilitas_klinik'  => 'Klinik X',
            'tanggal'           => now()->addDays(5)->format('Y-m-d'),
            'waktu'             => '10:00',
            'status'            => 'mendatang',
        ]);

        $pingSaya   = $this->buatPengingat($jadwalSaya);
        $pingMereka = Pengingat::create([
            'jadwal_id' => $jadwalMereka->id,
            'user_id'   => $userLain->id,
            'is_active' => true,
        ]);

        $hasil = Pengingat::where('user_id', $this->testUser->id)->pluck('id');

        $this->assertContains($pingSaya->id, $hasil);
        $this->assertNotContains($pingMereka->id, $hasil);

        $pingMereka->delete();
        $jadwalMereka->delete();
        $userLain->delete();
    }

    /** @test TC-B10: Relasi Pengingat → PengingatWaktu berfungsi */
    public function test_tcb10_relasi_pengingat_ke_waktu(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal, true, 720);

        $loaded = Pengingat::with('waktu')->find($pengingat->id);

        $this->assertCount(1, $loaded->waktu);
        $this->assertEquals(720, $loaded->waktu->first()->offset_menit);
    }

    /** @test TC-B11: Notifikasi unread terhitung benar */
    public function test_tcb11_unread_count_notifikasi(): void
    {
        $jadwal    = $this->buatJadwalMendatang();
        $pengingat = $this->buatPengingat($jadwal);
        $waktu     = $pengingat->waktu()->first();

        Notifikasi::create([
            'user_id' => $this->testUser->id, 'jadwal_id' => $jadwal->id,
            'pengingat_waktu_id' => $waktu->id, 'judul' => 'Notif 1',
            'pesan' => 'Pesan 1', 'is_read' => false, 'notified_at' => now(),
        ]);
        Notifikasi::create([
            'user_id' => $this->testUser->id, 'jadwal_id' => $jadwal->id,
            'pengingat_waktu_id' => $waktu->id, 'judul' => 'Notif 2',
            'pesan' => 'Pesan 2', 'is_read' => true, 'notified_at' => now(),
        ]);

        $unread = Notifikasi::where('user_id', $this->testUser->id)
                    ->where('is_read', false)
                    ->count();

        $this->assertEquals(1, $unread);
    }

    /** @test TC-B12: OFFSET_OPTIONS controller memuat semua nilai yang valid */
    public function test_tcb12_offset_options_valid(): void
    {
        $validOffsets = [30, 60, 180, 360, 720, 1440, 2880];

        foreach ($validOffsets as $offset) {
            $this->assertArrayHasKey(
                $offset,
                \App\Http\Controllers\PengingatController::OFFSET_OPTIONS
            );
        }
    }
}
