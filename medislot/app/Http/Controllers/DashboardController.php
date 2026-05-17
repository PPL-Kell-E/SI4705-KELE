<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\HealthData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.index');
        }

        $userId = Auth::id();
        $user   = Auth::user();
        $today  = Carbon::today();

        // Stat cards
        $jadwalBulanIni     = Jadwal::where('user_id', $userId)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->count();

        $pengingatAktif     = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(7)])
            ->count();

        $pemeriksaanSelesai = Jadwal::where('user_id', $userId)
            ->where('status', 'selesai')
            ->count();

        $streak = $this->hitungStreak($userId);

        // Pemeriksaan berikutnya
        $jadwalTerdekat = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->where('tanggal', '>=', $today)
            ->orderBy('tanggal')->orderBy('waktu')
            ->first();

        // Progress konsistensi bulan ini
        $jadwalBulanIniList = Jadwal::where('user_id', $userId)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year)
            ->orderBy('tanggal')
            ->get();

        $selesaiBulanIni  = $jadwalBulanIniList->where('status', 'selesai')->count();
        $totalBulanIni    = $jadwalBulanIniList->count();
        $persentase       = $totalBulanIni > 0
            ? round(($selesaiBulanIni / $totalBulanIni) * 100)
            : 0;

        // Insight hari ini (satu insight paling relevan)
        $insightHariIni = $this->getInsightHariIni($userId, $today);

        // Pencapaian terbaru
        $pencapaianTerbaru = $this->getPencapaianTerbaru($userId, $streak, $pemeriksaanSelesai);

        // Aktivitas terbaru (5 jadwal terakhir diupdated)
        $aktivitasTerbaru = Jadwal::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get()
            ->map(function ($j) {
                $icon = match($j->status) {
                    'selesai'  => ['icon' => 'fas fa-check-circle', 'color' => '#2d9e72', 'bg' => '#e8fff4', 'label' => 'Pemeriksaan selesai'],
                    'batal'    => ['icon' => 'fas fa-times-circle', 'color' => '#e74c3c', 'bg' => '#ffe8e8', 'label' => 'Jadwal dibatalkan'],
                    default    => ['icon' => 'fas fa-calendar-plus', 'color' => '#4a90d9', 'bg' => '#e8f4ff', 'label' => 'Jadwal dibuat'],
                };
                return array_merge($icon, [
                    'jenis'  => $j->jenis_pemeriksaan,
                    'tanggal'=> Carbon::parse($j->tanggal)->locale('id')->isoFormat('D MMM YYYY'),
                    'ago'    => Carbon::parse($j->updated_at)->diffForHumans(),
                ]);
            });

        // Tips kesehatan harian
        $tips = $this->getTips();

        return view('dashboard', compact(
            'user',
            'today',
            'jadwalBulanIni',
            'pengingatAktif',
            'pemeriksaanSelesai',
            'streak',
            'jadwalTerdekat',
            'jadwalBulanIniList',
            'selesaiBulanIni',
            'totalBulanIni',
            'persentase',
            'insightHariIni',
            'pencapaianTerbaru',
            'aktivitasTerbaru',
            'tips'
        ));
    }

    private function hitungStreak(string $userId): int
    {
        $streak = 0;
        $bulan  = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 24; $i++) {
            $ada = Jadwal::where('user_id', $userId)
                ->where('status', 'selesai')
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->exists();

            if ($ada) { $streak++; $bulan->subMonth(); }
            else break;
        }

        return $streak;
    }

    private function getInsightHariIni(string $userId, Carbon $today): ?array
    {
        // Cek jadwal terlewat
        $missed = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->where('tanggal', '<', $today)
            ->first();

        if ($missed) {
            return [
                'icon'  => 'fas fa-tooth',
                'color' => '#4a90d9',
                'bg'    => '#e8f4ff',
                'text'  => "Kamu belum melakukan pemeriksaan {$missed->jenis_pemeriksaan} selama beberapa waktu.",
                'sub'   => 'Yuk, jadwalkan pemeriksaan untuk menjaga kesehatanmu!',
            ];
        }

        // Upcoming dalam 3 hari
        $soon = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [$today, $today->copy()->addDays(3)])
            ->first();

        if ($soon) {
            $tgl = Carbon::parse($soon->tanggal)->locale('id')->isoFormat('dddd, D MMM');
            return [
                'icon'  => 'fas fa-bell',
                'color' => '#e67e22',
                'bg'    => '#fff3e8',
                'text'  => "Jadwal {$soon->jenis_pemeriksaan} mendekat!",
                'sub'   => "Pemeriksaan dijadwalkan {$tgl}. Jangan lupa hadir tepat waktu.",
            ];
        }

        return [
            'icon'  => 'fas fa-heartbeat',
            'color' => '#2d9e72',
            'bg'    => '#e8fff4',
            'text'  => 'Semangat menjaga kesehatan hari ini! 💪',
            'sub'   => 'Konsistensi adalah kunci kesehatan jangka panjang.',
        ];
    }

    private function getPencapaianTerbaru(string $userId, int $streak, int $totalSelesai): ?array
    {
        $hasHealthData = HealthData::where('user_id', $userId)->exists();

        if ($streak >= 1 && $totalSelesai >= 5) {
            return [
                'name' => 'Rajin Periksa',
                'desc' => 'Selamat! Kamu berhasil menyelesaikan 5 pemeriksaan.',
                'icon' => 'fas fa-star', 'color' => '#2d9e72',
            ];
        }
        if ($streak >= 1) {
            return [
                'name' => 'Konsisten 1 Bulan',
                'desc' => 'Selamat! Kamu berhasil konsisten memeriksa kesehatan selama 1 bulan.',
                'icon' => 'fas fa-award', 'color' => '#4a90d9',
            ];
        }
        if ($hasHealthData) {
            return [
                'name' => 'Pemula Sehat',
                'desc' => 'Kamu telah melengkapi data kesehatan dasarmu.',
                'icon' => 'fas fa-seedling', 'color' => '#27ae60',
            ];
        }

        return null;
    }

    private function getTips(): array
    {
        return [
            'Minum air putih minimal 8 gelas per hari untuk menjaga metabolisme tubuh.',
            'Periksa tekanan darah secara rutin, terutama jika memiliki riwayat hipertensi.',
            'Lakukan pemeriksaan gigi setiap 6 bulan sekali untuk mencegah masalah gigi.',
            'Tidur cukup 7–8 jam setiap malam untuk memulihkan energi tubuh.',
            'Olahraga ringan 30 menit sehari meningkatkan kesehatan jantung.',
        ];
    }
}
