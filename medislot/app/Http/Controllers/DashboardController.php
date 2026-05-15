<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\HealthData;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $hasHealthData = HealthData::where('user_id', $userId)->exists();

        $jadwalTerdekat = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->where('tanggal', '>=', today())
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu', 'asc')
            ->first();

        $selesaiBulanIni = Jadwal::where('user_id', $userId)
            ->where('status', 'selesai')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        $totalJadwalBulanIni = Jadwal::where('user_id', $userId)
            ->whereIn('status', ['mendatang', 'selesai'])
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        $persentaseTarget = $totalJadwalBulanIni > 0
            ? round(($selesaiBulanIni / $totalJadwalBulanIni) * 100)
            : 0;

        $streak = $this->hitungStreak($userId);

        $reminders = Jadwal::where('user_id', $userId)
            ->where('status', 'mendatang')
            ->whereBetween('tanggal', [today(), today()->addDays(7)])
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu', 'asc')
            ->get();

        return view('dashboard', compact(
            'hasHealthData',
            'jadwalTerdekat',
            'selesaiBulanIni',
            'totalJadwalBulanIni',
            'persentaseTarget',
            'streak',
            'reminders'
        ));
    }

    private function hitungStreak(string $userId): int
    {
        $streak = 0;
        $bulan = now()->startOfMonth()->copy();

        for ($i = 0; $i < 24; $i++) {
            $ada = Jadwal::where('user_id', $userId)
                ->where('status', 'selesai')
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->exists();

            if ($ada) {
                $streak++;
                $bulan->subMonth();
            } else {
                break;
            }
        }

        return $streak;
    }
}
