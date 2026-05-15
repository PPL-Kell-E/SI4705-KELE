<?php

namespace App\Http\Controllers;

use App\Models\Rekomendasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    private array $defaults = [
        ['nama' => 'Dental Check-up',              'deskripsi' => 'Disarankan setiap 6 bulan sekali',       'icon' => 'fa-tooth',        'bg_color' => '#e8f5f0', 'icon_color' => '#2d9e72', 'interval_days' => 180, 'interval_label' => '6 bulan'],
        ['nama' => 'Medical Check-up',             'deskripsi' => 'Jaga kesehatan secara menyeluruh',        'icon' => 'fa-kit-medical',  'bg_color' => '#e8f0f5', 'icon_color' => '#3a7abf', 'interval_days' => 365, 'interval_label' => '1 tahun'],
        ['nama' => 'Pemeriksaan Mata',             'deskripsi' => 'Disarankan setahun sekali',               'icon' => 'fa-eye',          'bg_color' => '#f0f5e8', 'icon_color' => '#6a9e2d', 'interval_days' => 365, 'interval_label' => '1 tahun'],
        ['nama' => 'Pemeriksaan Kesehatan Jantung','deskripsi' => 'Mulai pantau kesehatan jantungmu',        'icon' => 'fa-heart-pulse',  'bg_color' => '#fdf0f0', 'icon_color' => '#e05252', 'interval_days' => 180, 'interval_label' => '6 bulan'],
        ['nama' => 'Pemeriksaan THT',              'deskripsi' => 'Disarankan setiap 6 bulan sekali',        'icon' => 'fa-ear-deaf',     'bg_color' => '#fdf5e8', 'icon_color' => '#bf8a3a', 'interval_days' => 180, 'interval_label' => '6 bulan'],
    ];

    public function index()
    {
        $userId = Auth::id();

        // Seed defaults jika user belum punya data sama sekali
        if (Rekomendasi::where('user_id', $userId)->doesntExist()) {
            foreach ($this->defaults as $d) {
                Rekomendasi::create(array_merge($d, ['user_id' => $userId, 'is_default' => true]));
            }
        }

        $recommendations = Rekomendasi::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('nama')
            ->get();

        return view('rekomendasi.index', compact('recommendations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'           => ['required', 'string', 'max:255'],
            'deskripsi'      => ['nullable', 'string', 'max:500'],
            'interval_days'  => ['required', 'integer', 'min:1'],
            'interval_label' => ['required', 'string', 'max:50'],
        ]);

        Rekomendasi::create([
            'user_id'        => Auth::id(),
            'nama'           => $validated['nama'],
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'icon'           => 'fa-stethoscope',
            'bg_color'       => '#e8f5f0',
            'icon_color'     => '#2d9e72',
            'interval_days'  => $validated['interval_days'],
            'interval_label' => $validated['interval_label'],
            'is_default'     => false,
        ]);

        return redirect()->route('rekomendasi.index')->with('success', 'Rekomendasi berhasil ditambahkan!');
    }

    public function update(Request $request, Rekomendasi $rekomendasi)
    {
        abort_if($rekomendasi->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'nama'           => ['required', 'string', 'max:255'],
            'deskripsi'      => ['nullable', 'string', 'max:500'],
            'interval_days'  => ['required', 'integer', 'min:1'],
            'interval_label' => ['required', 'string', 'max:50'],
        ]);

        $rekomendasi->update($validated);

        return redirect()->route('rekomendasi.index')->with('success', 'Rekomendasi berhasil diperbarui!');
    }

    public function destroy(Rekomendasi $rekomendasi)
    {
        abort_if($rekomendasi->user_id !== Auth::id(), 403);
        $rekomendasi->delete();
        return redirect()->route('rekomendasi.index')->with('success', 'Rekomendasi berhasil dihapus.');
    }
}
