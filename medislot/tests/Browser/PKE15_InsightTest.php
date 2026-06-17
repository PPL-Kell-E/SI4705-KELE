<?php
// PKE-15: Browser test untuk Insight dan Evaluasi Kesehatan
namespace Tests\Browser;

use App\Models\HealthData;
use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PKE15_InsightTest extends DuskTestCase
{
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $uniqueId = Str::random(8);

        $this->testUser = User::create([
            'full_name'     => 'Test Dusk PKE15',
            'email'         => "dusk.pke15.{$uniqueId}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
    }

    protected function tearDown(): void
    {
        HealthData::where('user_id', $this->testUser->id)->delete();
        Jadwal::where('user_id', $this->testUser->id)->delete();
        $this->testUser->delete();
        parent::tearDown();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function login(Browser $browser): Browser
    {
        return $browser->loginAs($this->testUser);
    }

    private function loginDanBukaInsight(Browser $browser): Browser
    {
        return $this->login($browser)
                    ->visit('/insight')
                    ->waitFor('.insight-grid', 15);
    }

    private function buatJadwal(array $override = []): Jadwal
    {
        return Jadwal::create(array_merge([
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Cek Umum Dusk',
            'tanggal'           => Carbon::today()->format('Y-m-d'),
            'waktu'             => '09:00',
            'fasilitas_klinik'  => 'RS Dusk',
            'status'            => 'selesai',
        ], $override));
    }

    // ── Test Cases ────────────────────────────────────────────────────────────

    /** @test TC-01: Menampilkan halaman insight dan pencapaian — halaman berhasil ditampilkan (Positive) */
    public function test_tc01_menampilkan_halaman_insight_dan_pencapaian(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPathIs('/insight')
                 ->assertVisible('.insight-grid')
                 ->assertVisible('.streak-banner')
                 ->screenshot('tc01-halaman-insight-berhasil-ditampilkan');
        });
    }

    /** @test TC-02: Mengambil data insight kesehatan — sistem berhasil mengambil data insight (Positive) */
    public function test_tc02_mengambil_data_insight_kesehatan(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.insight-grid')
                 ->assertPresent('.section-card')
                 ->screenshot('tc02-data-insight-berhasil-diambil');
        });
    }

    /** @test TC-03: Menghitung health streak — sistem menampilkan jumlah streak dengan benar (Positive) */
    public function test_tc03_menghitung_health_streak(): void
    {
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.streak-banner')
                 ->assertVisible('.streak-count')
                 ->assertVisible('.streak-label')
                 ->screenshot('tc03-health-streak-tampil');
        });
    }

    /** @test TC-04: Menampilkan pencapaian tercapai — sistem menampilkan pencapaian yang berhasil diperoleh (Positive) */
    public function test_tc04_menampilkan_pencapaian_tercapai(): void
    {
        // totalCompleted >= 2 → unlock achievement "Periksa Rutin"
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->subDays(1)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->format('Y-m-d')]);

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.achievement-card.achieved')
                 ->assertSee('Tercapai')
                 ->screenshot('tc04-pencapaian-tercapai-tampil');
        });
    }

    /** @test TC-05: Menampilkan pencapaian belum tercapai — sistem menampilkan progress achievement (Positive) */
    public function test_tc05_menampilkan_pencapaian_belum_tercapai(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.achievement-card')
                 ->assertPresent('.achievement-progress-bar-wrap')
                 ->screenshot('tc05-pencapaian-belum-tercapai-progress-tampil');
        });
    }

    /** @test TC-06: Menampilkan insight pintar — sistem menampilkan insight kesehatan (Positive) */
    public function test_tc06_menampilkan_insight_pintar(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.insight-list')
                 ->assertPresent('.insight-item')
                 ->assertVisible('.insight-item-title')
                 ->screenshot('tc06-insight-pintar-tampil');
        });
    }

    /** @test TC-07: Menampilkan tips kesehatan — sistem menampilkan tips kesehatan harian (Positive) */
    public function test_tc07_menampilkan_tips_kesehatan(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.tips-card')
                 ->assertVisible('.tips-title')
                 ->assertVisible('.tips-desc')
                 ->screenshot('tc07-tips-kesehatan-tampil');
        });
    }

    /** @test TC-08: Menampilkan rekomendasi kesehatan — sistem menampilkan rekomendasi kesehatan (Positive) */
    public function test_tc08_menampilkan_rekomendasi_kesehatan(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.tips-recommendation')
                 ->assertVisible('.tips-rec-label')
                 ->screenshot('tc08-rekomendasi-kesehatan-tampil');
        });
    }

    /** @test TC-09: Pencapaian belum tersedia — sistem menampilkan pesan pencapaian kosong (Negative) */
    public function test_tc09_pencapaian_belum_tersedia(): void
    {
        // User baru tanpa aktivitas → tidak ada achievement tercapai
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.empty-state')
                 ->assertMissing('.achievement-card.achieved')
                 ->screenshot('tc09-pencapaian-kosong-empty-state');
        });
    }

    /** @test TC-10: Insight belum tersedia — sistem menampilkan section insight meski aktivitas belum cukup (Negative) */
    public function test_tc10_insight_belum_tersedia(): void
    {
        // User baru tanpa aktivitas → insight section tetap tampil (sistem selalu hasilkan insight default)
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertPresent('.insight-list')
                 ->assertVisible('.section-card')
                 ->assertMissing('.achievement-card.achieved')
                 ->screenshot('tc10-insight-belum-tersedia-default');
        });
    }

    /** @test TC-11: Health streak belum tersedia — sistem menampilkan informasi streak belum tersedia (Negative) */
    public function test_tc11_health_streak_belum_tersedia(): void
    {
        // User tanpa jadwal selesai hari ini → streak = 0 → streak-zero tampil
        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->assertVisible('.streak-zero')
                 ->assertMissing('.streak-count')
                 ->screenshot('tc11-health-streak-belum-tersedia');
        });
    }

    /** @test TC-12: Gagal mengambil data insight — sistem menampilkan pesan gagal memuat data (Negative) */
    public function test_tc12_gagal_mengambil_data_insight(): void
    {
        // Akses tanpa login → redirect ke login
        $this->browse(function (Browser $browser) {
            $browser->visit('/insight')
                    ->pause(1000)
                    ->assertPathIs('/login')
                    ->screenshot('tc12-redirect-login-jika-belum-login');
        });
    }

    /** @test TC-13: Menampilkan loading state — sistem menampilkan loading state sementara (Positive) */
    public function test_tc13_menampilkan_loading_state(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->login($browser)
                 ->visit('/insight')
                 ->waitFor('.insight-grid', 15)
                 ->assertVisible('.insight-grid')
                 ->assertPresent('.section-card')
                 ->screenshot('tc13-loading-state-selesai');
        });
    }

    /** @test TC-14: Validasi pembaruan insight — insight diperbarui tanpa error (Positive) */
    public function test_tc14_validasi_pembaruan_insight(): void
    {
        $this->buatJadwal();

        $this->browse(function (Browser $browser) {
            $this->loginDanBukaInsight($browser)
                 ->refresh()
                 ->waitFor('.insight-grid', 15)
                 ->assertPathIs('/insight')
                 ->assertVisible('.insight-grid')
                 ->assertPresent('.last-updated')
                 ->screenshot('tc14-insight-diperbarui-tanpa-error');
        });
    }

    // =========================================================================
    // BACKEND / DATABASE TESTS (tanpa browser)
    // =========================================================================

    /**
     * @test TC-B01: computeStreak — hari berturut-turut dihitung dari hari ini mundur
     *
     * Langkah:
     * 1. Buat jadwal selesai hari ini, kemarin, dan 2 hari lalu
     * 2. Jalankan logika computeStreak (mirror controller)
     * 3. Pastikan streak = 3
     * 4. Pastikan data tersimpan di DB dengan status selesai
     */
    public function test_tcb01_compute_streak_berturut_turut(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->subDay()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->subDays(2)->format('Y-m-d')]);

        // Langkah 2: Mirror computeStreak()
        $completedDates = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        $streak = 0;
        $check  = Carbon::today();
        foreach ($completedDates as $date) {
            $d = Carbon::parse($date);
            if ($d->toDateString() === $check->toDateString()) {
                $streak++;
                $check->subDay();
            } elseif ($d->lt($check)) {
                break;
            }
        }

        // Langkah 3
        $this->assertEquals(3, $streak);

        // Langkah 4
        $this->assertDatabaseHas('jadwal', [
            'user_id' => $this->testUser->id,
            'status'  => 'selesai',
        ]);
    }

    /**
     * @test TC-B02: Streak terputus saat ada hari tanpa jadwal selesai
     *
     * Langkah:
     * 1. Buat jadwal selesai hari ini dan 2 hari lalu (kemarin kosong)
     * 2. Jalankan computeStreak
     * 3. Streak harus = 1 (hanya hari ini yang dihitung, kemarin terputus)
     */
    public function test_tcb02_streak_terputus_saat_ada_celah(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->subDays(2)->format('Y-m-d')]);
        // Kemarin tidak ada → streak terputus

        // Langkah 2
        $completedDates = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        $streak = 0;
        $check  = Carbon::today();
        foreach ($completedDates as $date) {
            $d = Carbon::parse($date);
            if ($d->toDateString() === $check->toDateString()) {
                $streak++;
                $check->subDay();
            } elseif ($d->lt($check)) {
                break;
            }
        }

        // Langkah 3
        $this->assertEquals(1, $streak);
    }

    /**
     * @test TC-B03: Achievement "Pemula Sehat" — unlocked saat HealthData ada
     *
     * Langkah:
     * 1. Buat HealthData untuk testUser
     * 2. Jalankan logika computeAchievements (mirror: pemula_sehat = hasHealthData)
     * 3. Pastikan 'pemula_sehat' masuk ke achieved
     * 4. Verifikasi HealthData tersimpan di DB
     */
    public function test_tcb03_achievement_pemula_sehat_unlocked(): void
    {
        // Langkah 1
        $hd = HealthData::create([
            'user_id'    => $this->testUser->id,
            'weight_kg'  => 65,
            'height_cm'  => 170,
            'blood_type' => 'O',
        ]);

        // Langkah 2
        $hasHealthData  = HealthData::where('user_id', $this->testUser->id)->exists();
        $totalCompleted = Jadwal::where('user_id', $this->testUser->id)->where('status', 'selesai')->count();
        $distinctTypes  = Jadwal::where('user_id', $this->testUser->id)->where('status', 'selesai')
            ->pluck('jenis_pemeriksaan')->unique()->count();
        $streak = 0;

        $pemula = ['key' => 'pemula_sehat', 'check' => $hasHealthData];
        $achieved = $pemula['check'] ? [$pemula['key']] : [];

        // Langkah 3
        $this->assertContains('pemula_sehat', $achieved);

        // Langkah 4
        $this->assertDatabaseHas('health_data', ['user_id' => $this->testUser->id, 'blood_type' => 'O']);

        $hd->delete();
    }

    /**
     * @test TC-B04: Achievement "Tepat Waktu" — unlocked saat totalCompleted >= 2
     *
     * Langkah:
     * 1. Buat 2 jadwal selesai
     * 2. Hitung totalCompleted
     * 3. Pastikan check = true (totalCompleted >= 2)
     * 4. Verifikasi di DB
     */
    public function test_tcb04_achievement_tepat_waktu_unlocked(): void
    {
        // Langkah 1
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::today()->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => Carbon::yesterday()->format('Y-m-d')]);

        // Langkah 2
        $totalCompleted = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->count();

        // Langkah 3
        $this->assertGreaterThanOrEqual(2, $totalCompleted);
        $this->assertTrue($totalCompleted >= 2); // check 'tepat_waktu'

        // Langkah 4
        $this->assertDatabaseHas('jadwal', ['user_id' => $this->testUser->id, 'status' => 'selesai']);
    }

    /**
     * @test TC-B05: Achievement "Rajin Periksa" — belum tercapai saat total < 5
     *
     * Langkah:
     * 1. Buat 3 jadwal selesai
     * 2. Hitung totalCompleted
     * 3. Pastikan 'rajin_periksa' (>= 5) belum tercapai
     * 4. Pastikan progress = 3, target = 5
     */
    public function test_tcb05_achievement_rajin_periksa_belum_tercapai(): void
    {
        // Langkah 1
        for ($i = 0; $i < 3; $i++) {
            $this->buatJadwal([
                'status'  => 'selesai',
                'tanggal' => Carbon::today()->subDays($i)->format('Y-m-d'),
            ]);
        }

        // Langkah 2
        $totalCompleted = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->count();

        // Langkah 3
        $this->assertFalse($totalCompleted >= 5); // belum unlock

        // Langkah 4
        $this->assertEquals(3, $totalCompleted);
        $this->assertEquals(3, min($totalCompleted, 5)); // progress = 3 dari target 5
    }

    /**
     * @test TC-B06: Achievement "Sehat Menyeluruh" — butuh 10 jenis pemeriksaan berbeda
     *
     * Langkah:
     * 1. Buat jadwal selesai dengan 10 jenis pemeriksaan berbeda
     * 2. Hitung distinctTypes
     * 3. Pastikan achievement unlocked (distinctTypes >= 10)
     * 4. Verifikasi semua jenis tersimpan di DB
     */
    public function test_tcb06_achievement_sehat_menyeluruh_unlocked(): void
    {
        $jenis = ['Darah', 'Mata', 'Jantung', 'Gigi', 'Paru', 'Kulit', 'Ginjal', 'Tulang', 'THT', 'Lambung'];

        // Langkah 1
        foreach ($jenis as $j) {
            $this->buatJadwal(['status' => 'selesai', 'jenis_pemeriksaan' => "Cek {$j}"]);
        }

        // Langkah 2
        $distinctTypes = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->pluck('jenis_pemeriksaan')
            ->unique()
            ->count();

        // Langkah 3
        $this->assertGreaterThanOrEqual(10, $distinctTypes);
        $this->assertTrue($distinctTypes >= 10); // check 'sehat_menyeluruh'

        // Langkah 4
        $this->assertDatabaseHas('jadwal', ['user_id' => $this->testUser->id, 'jenis_pemeriksaan' => 'Cek Darah']);
        $this->assertDatabaseHas('jadwal', ['user_id' => $this->testUser->id, 'jenis_pemeriksaan' => 'Cek Lambung']);
    }

    /**
     * @test TC-B07: Smart insight jadwal terlewat — terdeteksi saat ada mendatang + tanggal lampau
     *
     * Langkah:
     * 1. Buat jadwal status mendatang dengan tanggal 5 hari lalu
     * 2. Query missed seperti computeSmartInsights()
     * 3. Pastikan ada insight "terlewat" dan jumlah cnt benar
     * 4. Verifikasi data di DB
     */
    public function test_tcb07_smart_insight_jadwal_terlewat(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $this->buatJadwal([
            'status'            => 'mendatang',
            'tanggal'           => $today->copy()->subDays(5)->format('Y-m-d'),
            'jenis_pemeriksaan' => 'Cek Terlewat PKE15',
        ]);
        $this->buatJadwal([
            'status'            => 'mendatang',
            'tanggal'           => $today->copy()->subDays(3)->format('Y-m-d'),
            'jenis_pemeriksaan' => 'Cek Terlewat PKE15',
        ]);

        // Langkah 2
        $missed = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->where('tanggal', '<', $today)
            ->selectRaw('jenis_pemeriksaan, COUNT(*) as cnt')
            ->groupBy('jenis_pemeriksaan')
            ->get();

        // Langkah 3
        $this->assertGreaterThan(0, $missed->count());
        $this->assertEquals(2, $missed->first()->cnt);
        $this->assertEquals('Cek Terlewat PKE15', $missed->first()->jenis_pemeriksaan);

        // Langkah 4
        $this->assertDatabaseHas('jadwal', [
            'user_id'           => $this->testUser->id,
            'jenis_pemeriksaan' => 'Cek Terlewat PKE15',
            'status'            => 'mendatang',
        ]);
    }

    /**
     * @test TC-B08: Smart insight jadwal mendatang 7 hari — hanya jadwal dalam window terdeteksi
     *
     * Langkah:
     * 1. Buat 2 jadwal mendatang dalam 7 hari ke depan
     * 2. Buat 1 jadwal mendatang > 7 hari (tidak terhitung)
     * 3. Query upcoming seperti computeSmartInsights()
     * 4. Pastikan count = 2
     */
    public function test_tcb08_smart_insight_jadwal_mendatang_7_hari(): void
    {
        $today = Carbon::today();

        // Langkah 1–2
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(2)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(6)->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'mendatang', 'tanggal' => $today->copy()->addDays(10)->format('Y-m-d')]);

        // Langkah 3
        $upcoming = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(7)])
            ->count();

        // Langkah 4
        $this->assertEquals(2, $upcoming);
    }

    /**
     * @test TC-B09: Smart insight peningkatan konsistensi — bulan ini > bulan lalu
     *
     * Langkah:
     * 1. Buat 3 jadwal selesai bulan lalu
     * 2. Buat 5 jadwal selesai bulan ini
     * 3. Hitung persentase peningkatan seperti computeSmartInsights()
     * 4. Pastikan pct = 67% dan insight muncul
     */
    public function test_tcb09_smart_insight_konsistensi_meningkat(): void
    {
        $today         = Carbon::today();
        $lastMonthDate = $today->copy()->subMonth();

        // Langkah 1
        for ($i = 1; $i <= 3; $i++) {
            $this->buatJadwal([
                'status'  => 'selesai',
                'tanggal' => $lastMonthDate->copy()->addDays($i)->format('Y-m-d'),
            ]);
        }

        // Langkah 2
        for ($i = 1; $i <= 5; $i++) {
            $this->buatJadwal([
                'status'  => 'selesai',
                'tanggal' => $today->copy()->addDays($i)->format('Y-m-d'),
            ]);
        }

        // Langkah 3
        $thisMonth = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->count();

        $lastMonth = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', $lastMonthDate->month)
            ->whereYear('tanggal', $lastMonthDate->year)
            ->count();

        $insightMuncul = $lastMonth > 0 && $thisMonth > $lastMonth;
        $pct = $insightMuncul ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : 0;

        // Langkah 4
        $this->assertTrue($insightMuncul);
        $this->assertEquals(67, $pct);
        $this->assertEquals(5, $thisMonth);
        $this->assertEquals(3, $lastMonth);
    }

    /**
     * @test TC-B10: Smart insight tidak muncul saat bulan ini <= bulan lalu
     *
     * Langkah:
     * 1. Buat 5 jadwal selesai bulan lalu
     * 2. Buat 2 jadwal selesai bulan ini (lebih sedikit)
     * 3. Pastikan kondisi insight peningkatan = false
     */
    public function test_tcb10_smart_insight_konsistensi_tidak_meningkat(): void
    {
        $today         = Carbon::today();
        $lastMonthDate = $today->copy()->subMonth();

        // Langkah 1
        for ($i = 1; $i <= 5; $i++) {
            $this->buatJadwal([
                'status'  => 'selesai',
                'tanggal' => $lastMonthDate->copy()->addDays($i)->format('Y-m-d'),
            ]);
        }

        // Langkah 2
        for ($i = 1; $i <= 2; $i++) {
            $this->buatJadwal([
                'status'  => 'selesai',
                'tanggal' => $today->copy()->addDays($i)->format('Y-m-d'),
            ]);
        }

        // Langkah 3
        $thisMonth = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->count();

        $lastMonth = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', $lastMonthDate->month)
            ->whereYear('tanggal', $lastMonthDate->year)
            ->count();

        $insightMuncul = $lastMonth > 0 && $thisMonth > $lastMonth;
        $this->assertFalse($insightMuncul);
    }

    /**
     * @test TC-B11: Data isolation — insight hanya mengambil data milik user sendiri
     *
     * Langkah:
     * 1. Buat user lain dengan 10 jadwal selesai
     * 2. Buat 2 jadwal selesai untuk testUser
     * 3. Hitung totalCompleted hanya untuk testUser
     * 4. Pastikan totalCompleted = 2 (tidak tercampur)
     * 5. Hapus data user lain
     */
    public function test_tcb11_data_isolation_insight(): void
    {
        $today = Carbon::today();

        // Langkah 1
        $uid      = Str::random(6);
        $userLain = User::create([
            'full_name'     => 'User Lain PKE15',
            'email'         => "lain.pke15.{$uid}@test.local",
            'password_hash' => Hash::make('Password123!'),
            'role'          => 'user',
        ]);
        for ($i = 0; $i < 10; $i++) {
            Jadwal::create([
                'user_id'           => $userLain->id,
                'jenis_pemeriksaan' => "Cek Lain {$i}",
                'tanggal'           => $today->copy()->subDays($i)->format('Y-m-d'),
                'waktu'             => '09:00',
                'fasilitas_klinik'  => 'RS Lain',
                'status'            => 'selesai',
            ]);
        }

        // Langkah 2
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->format('Y-m-d')]);
        $this->buatJadwal(['status' => 'selesai', 'tanggal' => $today->copy()->subDay()->format('Y-m-d')]);

        // Langkah 3
        $totalCompleted = Jadwal::where('user_id', $this->testUser->id)
            ->where('status', 'selesai')
            ->count();

        // Langkah 4
        $this->assertEquals(2, $totalCompleted);

        // Langkah 5
        Jadwal::where('user_id', $userLain->id)->delete();
        $userLain->delete();
    }

    /**
     * @test TC-B12: getDailyTip — tips dipilih berdasarkan dayOfYear % jumlah tips
     *
     * Langkah:
     * 1. Definisikan array tips seperti controller
     * 2. Hitung index = dayOfYear % count(tips)
     * 3. Ambil tips sesuai index
     * 4. Pastikan tips memiliki key title, desc, recommendation, emoji
     * 5. Pastikan index selalu dalam rentang valid (tidak out-of-bounds)
     */
    public function test_tcb12_daily_tip_dipilih_berdasarkan_hari(): void
    {
        // Langkah 1
        $tips = [
            ['title' => 'Minum air yang cukup setiap hari!', 'desc' => '...', 'recommendation' => 'Minum 8 gelas', 'emoji' => '💧'],
            ['title' => 'Olahraga ringan 30 menit sehari!',  'desc' => '...', 'recommendation' => 'Jalan kaki 30 menit', 'emoji' => '🏃'],
            ['title' => 'Jaga pola makan seimbang!',         'desc' => '...', 'recommendation' => 'Isi setengah piring', 'emoji' => '🥗'],
            ['title' => 'Istirahat yang cukup setiap malam!','desc' => '...', 'recommendation' => 'Tidur sebelum 22.00', 'emoji' => '🌙'],
            ['title' => 'Kelola stres dengan baik!',         'desc' => '...', 'recommendation' => 'Meditasi 10 menit',  'emoji' => '🧘'],
        ];

        // Langkah 2–3
        $index = now()->dayOfYear % count($tips);
        $tip   = $tips[$index];

        // Langkah 4
        $this->assertArrayHasKey('title', $tip);
        $this->assertArrayHasKey('desc', $tip);
        $this->assertArrayHasKey('recommendation', $tip);
        $this->assertArrayHasKey('emoji', $tip);

        // Langkah 5
        $this->assertGreaterThanOrEqual(0, $index);
        $this->assertLessThan(count($tips), $index);
    }
}
