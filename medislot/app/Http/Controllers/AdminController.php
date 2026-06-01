<?php

namespace App\Http\Controllers;

use App\Models\HasilPemeriksaan;
use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->input('search', '');
        $perPage = (int) $request->input('per_page', 10);
        $tahun   = (int) $request->input('tahun', now()->year);

        if (!in_array($perPage, [10, 25, 50])) $perPage = 10;

        try {
            // ── Stat cards ──
            $totalUser     = User::where('role', 'user')->count();
            $totalJadwal   = Jadwal::count();
            $aktifBulanIni = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->where('users.role', 'user')
                ->where('sessions.last_activity', '>=', now()->startOfMonth()->timestamp)
                ->distinct('sessions.user_id')
                ->count('sessions.user_id');

            // ── Chart: jadwal per bulan (SQLite + PostgreSQL compatible) ──
            $driver = DB::getDriverName();

            if ($driver === 'sqlite') {
                $rawMonthly = Jadwal::selectRaw("CAST(strftime('%m', tanggal) AS INTEGER) AS bulan, COUNT(*) AS total")
                    ->whereRaw("strftime('%Y', tanggal) = ?", [(string) $tahun])
                    ->groupByRaw("strftime('%m', tanggal)")
                    ->orderByRaw("strftime('%m', tanggal)")
                    ->pluck('total', 'bulan');
            } else {
                $rawMonthly = Jadwal::selectRaw("EXTRACT(MONTH FROM tanggal)::int AS bulan, COUNT(*) AS total")
                    ->whereYear('tanggal', $tahun)
                    ->groupByRaw("EXTRACT(MONTH FROM tanggal)::int")
                    ->orderByRaw("EXTRACT(MONTH FROM tanggal)::int")
                    ->pluck('total', 'bulan');
            }

            $chartData = collect(range(1, 12))
                ->mapWithKeys(fn($m) => [$m => (int) ($rawMonthly->get($m, 0))]);

            // ── User list with search + pagination ──
            $usersQuery = User::where('role', 'user')
                ->withCount('hasilPemeriksaan as total_pemeriksaan')
                ->when($search, fn($q) => $q->where(fn($q2) =>
                    $q2->whereRaw('LOWER(full_name) LIKE ?', ['%' . strtolower($search) . '%'])
                       ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                ))
                ->orderByDesc('total_pemeriksaan')
                ->orderBy('full_name');

            $users      = $usersQuery->paginate($perPage)->withQueryString();
            $error      = null;

        } catch (\Exception $e) {
            $totalUser     = 0;
            $totalJadwal   = 0;
            $aktifBulanIni = 0;
            $chartData     = collect(array_fill(1, 12, 0));
            $users         = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
            $error         = 'Data dashboard gagal dimuat.';
        }

        $availableYears = collect(range(now()->year, now()->year - 4));

        return view('admin.index', compact(
            'totalUser', 'totalJadwal', 'aktifBulanIni',
            'chartData', 'users', 'search', 'perPage',
            'tahun', 'availableYears', 'error'
        ));
    }
}
