<?php

namespace App\Http\Controllers;

use App\Models\HasilPemeriksaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'semua');
        $dari   = $request->query('dari');
        $sampai = $request->query('sampai');

        try {
            $query = HasilPemeriksaan::where('user_id', Auth::id())
                ->whereNull('hidden_at');

            switch ($filter) {
                case 'minggu_ini':
                    $query->whereBetween('tanggal_pemeriksaan', [
                        Carbon::now()->startOfWeek(Carbon::MONDAY),
                        Carbon::now()->endOfWeek(Carbon::SUNDAY),
                    ]);
                    break;
                case 'bulan_ini':
                    $query->whereYear('tanggal_pemeriksaan', now()->year)
                          ->whereMonth('tanggal_pemeriksaan', now()->month);
                    break;
                case 'tahun_ini':
                    $query->whereYear('tanggal_pemeriksaan', now()->year);
                    break;
                case 'custom':
                    if ($dari)   $query->where('tanggal_pemeriksaan', '>=', $dari);
                    if ($sampai) $query->where('tanggal_pemeriksaan', '<=', $sampai);
                    break;
            }

            $riwayat = $query->orderByDesc('tanggal_pemeriksaan')
                             ->orderByDesc('created_at')
                             ->get();
            $error = null;

        } catch (\Exception $e) {
            $riwayat = collect();
            $error   = 'Data riwayat kesehatan gagal dimuat.';
        }

        return view('riwayat.index', compact('riwayat', 'filter', 'dari', 'sampai', 'error'));
    }

    public function hide(HasilPemeriksaan $hasilPemeriksaan)
    {
        abort_if($hasilPemeriksaan->user_id !== Auth::id(), 403);

        try {
            $hasilPemeriksaan->update(['hidden_at' => now()]);
            return redirect()->route('riwayat.index')
                ->with('success', 'Riwayat berhasil dihapus dari tampilan.');
        } catch (\Exception $e) {
            return redirect()->route('riwayat.index')
                ->with('error', 'Riwayat gagal dihapus.');
        }
    }
}
