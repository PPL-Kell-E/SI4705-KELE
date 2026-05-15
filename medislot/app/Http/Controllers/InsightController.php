<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\HealthData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InsightController extends Controller
{
    public function index()
    {
        $userId  = Auth::id();
        $user    = Auth::user();

        try {
            $streak         = $this->computeStreak($userId);
            $achievements   = $this->computeAchievements($userId, $streak);
            $smartInsights  = $this->computeSmartInsights($userId);
            $dailyTip       = $this->getDailyTip();
            $lastUpdated    = now()->format('d M Y, H:i');
            $error          = null;
        } catch (\Exception $e) {
            $streak = 0;
            $achievements = ['achieved' => [], 'unachieved' => []];
            $smartInsights = [];
            $dailyTip = null;
            $lastUpdated = null;
            $error = 'Data insight kesehatan gagal dimuat.';
        }

        return view('insight.index', compact(
            'user', 'streak', 'achievements', 'smartInsights', 'dailyTip', 'lastUpdated', 'error'
        ));
    }

    private function computeStreak(string $userId): int
    {
        $completedDates = Jadwal::where('user_id', $userId)
            ->where('status', 'selesai')
            ->orderBy('tanggal', 'desc')
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        if ($completedDates->isEmpty()) return 0;

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

        return $streak;
    }

    private function computeAchievements(string $userId, int $streak): array
    {
        $allJadwal      = Jadwal::where('user_id', $userId)->where('status', 'selesai')->get();
        $totalCompleted = $allJadwal->count();
        $distinctTypes  = $allJadwal->pluck('jenis_pemeriksaan')->unique()->count();
        $hasHealthData  = HealthData::where('user_id', $userId)->exists();

        $achieved   = [];
        $unachieved = [];

        $definitions = [
            [
                'key'      => 'pemula_sehat',
                'name'     => 'Pemula Sehat',
                'desc'     => 'Melengkapi profil medis dasar',
                'icon'     => 'fas fa-star',
                'color'    => '#2d9e72',
                'bg'       => '#e8fff4',
                'check'    => $hasHealthData,
                'progress' => $hasHealthData ? 1 : 0,
                'target'   => 1,
            ],
            [
                'key'      => 'konsisten_1_bulan',
                'name'     => 'Konsisten 1 Bulan',
                'desc'     => 'Login 4 minggu berturut-turut',
                'icon'     => 'fas fa-heartbeat',
                'color'    => '#4a90d9',
                'bg'       => '#e8f4ff',
                'check'    => $streak >= 28,
                'progress' => min($streak, 28),
                'target'   => 28,
            ],
            [
                'key'      => 'tepat_waktu',
                'name'     => 'Tepat Waktu',
                'desc'     => 'Hadir jadwal 2x berturut-turut',
                'icon'     => 'fas fa-clock',
                'color'    => '#e67e22',
                'bg'       => '#fff3e8',
                'check'    => $totalCompleted >= 2,
                'progress' => min($totalCompleted, 2),
                'target'   => 2,
            ],
            [
                'key'      => 'rajin_periksa',
                'name'     => 'Rajin Periksa',
                'desc'     => 'Menyelesaikan 5 pemeriksaan',
                'icon'     => 'fas fa-leaf',
                'color'    => '#27ae60',
                'bg'       => '#e8fff0',
                'check'    => $totalCompleted >= 5,
                'progress' => min($totalCompleted, 5),
                'target'   => 5,
            ],
            [
                'key'      => 'explorer',
                'name'     => 'Explorer',
                'desc'     => 'Menjelajahi semua fitur utama',
                'icon'     => 'fas fa-compass',
                'color'    => '#7c6cde',
                'bg'       => '#f0eeff',
                'check'    => false,
                'progress' => 3,
                'target'   => 8,
            ],
            [
                'key'      => 'disiplin_sehat',
                'name'     => 'Disiplin Sehat',
                'desc'     => 'Konsisten 3 bulan berturut-turut',
                'icon'     => 'fas fa-trophy',
                'color'    => '#e67e22',
                'bg'       => '#fff3e8',
                'check'    => $streak >= 90,
                'progress' => min($streak, 90),
                'target'   => 90,
            ],
            [
                'key'      => 'sehat_menyeluruh',
                'name'     => 'Sehat Menyeluruh',
                'desc'     => 'Selesaikan 10 jenis pemeriksaan berbeda',
                'icon'     => 'fas fa-spa',
                'color'    => '#2d9e72',
                'bg'       => '#e8fff4',
                'check'    => $distinctTypes >= 10,
                'progress' => min($distinctTypes, 10),
                'target'   => 10,
            ],
            [
                'key'      => 'master_sehat',
                'name'     => 'Master Sehat',
                'desc'     => 'Konsisten 6 bulan berturut-turut',
                'icon'     => 'fas fa-crown',
                'color'    => '#c0a030',
                'bg'       => '#fffbe8',
                'check'    => $streak >= 180,
                'progress' => min($streak, 180),
                'target'   => 180,
            ],
        ];

        foreach ($definitions as $def) {
            if ($def['check']) {
                $achieved[] = $def;
            } else {
                $unachieved[] = $def;
            }
        }

        return compact('achieved', 'unachieved');
    }

    private function computeSmartInsights(string $userId): array
    {
        $insights = [];
        $today    = Carbon::today();

        // Jadwal terlewat (mendatang tapi tanggal sudah lewat)
        $missed = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->where('tanggal', '<', $today)
            ->selectRaw('jenis_pemeriksaan, COUNT(*) as cnt')
            ->groupBy('jenis_pemeriksaan')
            ->get();

        foreach ($missed as $m) {
            $insights[] = [
                'icon'       => 'fas fa-tooth',
                'icon_bg'    => '#e8f4ff',
                'icon_color' => '#4a90d9',
                'title'      => "Saatnya periksa {$m->jenis_pemeriksaan}!",
                'desc'       => "Anda melewatkan jadwal pemeriksaan {$m->jenis_pemeriksaan} {$m->cnt} kali berturut-turut.",
            ];
        }

        // Jadwal mendatang dalam 7 hari
        $upcoming = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(7)])
            ->count();

        if ($upcoming > 0) {
            $insights[] = [
                'icon'       => 'fas fa-calendar-check',
                'icon_bg'    => '#fff3e8',
                'icon_color' => '#e67e22',
                'title'      => 'Jangan lewatkan jadwalmu!',
                'desc'       => "Anda memiliki {$upcoming} jadwal pemeriksaan dalam 7 hari ke depan. Jangan lupa untuk hadir tepat waktu.",
            ];
        }

        // Peningkatan konsistensi bulan ini vs bulan lalu
        $thisMonth = Jadwal::where('user_id', $userId)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->count();

        $lastMonthDate = $today->copy()->subMonth();
        $lastMonth = Jadwal::where('user_id', $userId)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', $lastMonthDate->month)
            ->whereYear('tanggal', $lastMonthDate->year)
            ->count();

        if ($lastMonth > 0 && $thisMonth > $lastMonth) {
            $pct = round((($thisMonth - $lastMonth) / $lastMonth) * 100);
            $insights[] = [
                'icon'       => 'fas fa-heartbeat',
                'icon_bg'    => '#e8fff4',
                'icon_color' => '#2d9e72',
                'title'      => 'Konsistensi pemeriksaanmu meningkat!',
                'desc'       => "Kamu sudah {$pct}% lebih konsisten dibanding bulan lalu. Pertahankan kebiasaan baik ini!",
            ];
        }

        // Tips tidur (selalu ada)
        $insights[] = [
            'icon'       => 'fas fa-moon',
            'icon_bg'    => '#f0eeff',
            'icon_color' => '#7c6cde',
            'title'      => 'Tidur cukup, hidup lebih berkualitas.',
            'desc'       => 'Tidur 7–8 jam setiap hari dapat membantu tubuh lebih segar dan sehat.',
        ];

        return $insights;
    }

    private function getDailyTip(): array
    {
        $tips = [
            [
                'title'          => 'Minum air yang cukup setiap hari!',
                'desc'           => 'Air membantu menjaga fungsi tubuh tetap optimal, meningkatkan energi, dan menjaga kesehatan kulit.',
                'recommendation' => 'Minum 8 gelas air per hari (±2 liter)',
                'emoji'          => '💧',
            ],
            [
                'title'          => 'Olahraga ringan 30 menit sehari!',
                'desc'           => 'Aktivitas fisik ringan seperti jalan kaki dapat meningkatkan kesehatan jantung dan mengurangi stres.',
                'recommendation' => 'Jalan kaki 30 menit setelah makan siang',
                'emoji'          => '🏃',
            ],
            [
                'title'          => 'Jaga pola makan seimbang!',
                'desc'           => 'Konsumsi sayuran, buah, protein, dan karbohidrat secara seimbang untuk menjaga kesehatan optimal.',
                'recommendation' => 'Isi setengah piring dengan sayur dan buah',
                'emoji'          => '🥗',
            ],
            [
                'title'          => 'Istirahat yang cukup setiap malam!',
                'desc'           => 'Tidur 7–8 jam membantu tubuh memulihkan energi, meningkatkan konsentrasi, dan memperkuat imunitas.',
                'recommendation' => 'Tidur sebelum pukul 22.00 setiap malam',
                'emoji'          => '🌙',
            ],
            [
                'title'          => 'Kelola stres dengan baik!',
                'desc'           => 'Stres berlebihan dapat mempengaruhi kesehatan fisik dan mental. Luangkan waktu untuk relaksasi.',
                'recommendation' => 'Meditasi 10 menit setiap pagi',
                'emoji'          => '🧘',
            ],
        ];

        return $tips[now()->dayOfYear % count($tips)];
    }
}
