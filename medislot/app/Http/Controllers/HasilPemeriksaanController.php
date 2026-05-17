<?php

namespace App\Http\Controllers;

use App\Models\HasilPemeriksaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasilPemeriksaanController extends Controller
{
    public function index()
    {
        $hasilList = HasilPemeriksaan::where('user_id', Auth::id())
            ->orderByDesc('tanggal_pemeriksaan')
            ->orderByDesc('created_at')
            ->get();

        return view('hasil-pemeriksaan.index', compact('hasilList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_pemeriksaan'   => ['required', 'string', 'max:255'],
            'tanggal_pemeriksaan' => ['required', 'date'],
            'fasilitas_kesehatan' => ['nullable', 'string', 'max:255'],
            'nama_dokter'         => ['nullable', 'string', 'max:255'],
            'hasil_pemeriksaan'   => ['required', 'string'],
            'catatan_tambahan'    => ['nullable', 'string'],
        ]);

        $validated['user_id'] = Auth::id();

        HasilPemeriksaan::create($validated);

        return redirect()->route('hasil-pemeriksaan.index')
            ->with('success', 'Hasil pemeriksaan berhasil disimpan.');
    }

    public function update(Request $request, HasilPemeriksaan $hasilPemeriksaan)
    {
        abort_if($hasilPemeriksaan->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'jenis_pemeriksaan'   => ['required', 'string', 'max:255'],
            'tanggal_pemeriksaan' => ['required', 'date'],
            'fasilitas_kesehatan' => ['nullable', 'string', 'max:255'],
            'nama_dokter'         => ['nullable', 'string', 'max:255'],
            'hasil_pemeriksaan'   => ['required', 'string'],
            'catatan_tambahan'    => ['nullable', 'string'],
        ]);

        $hasilPemeriksaan->update($validated);

        return redirect()->route('hasil-pemeriksaan.index')
            ->with('success', 'Hasil pemeriksaan berhasil diperbarui.');
    }

    public function destroy(HasilPemeriksaan $hasilPemeriksaan)
    {
        abort_if($hasilPemeriksaan->user_id !== Auth::id(), 403);
        $hasilPemeriksaan->delete();

        return redirect()->route('hasil-pemeriksaan.index')
            ->with('success', 'Hasil pemeriksaan berhasil dihapus.');
    }
}
