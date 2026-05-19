<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'semua');

        $query = Jadwal::where('user_id', Auth::id())->orderBy('tanggal', 'asc');

        if ($filter === 'mendatang') {
            $query->where('status', 'mendatang');
        } elseif ($filter === 'selesai') {
            $query->whereIn('status', ['selesai', 'batal']);
        }

        $jadwals = $query->get();

        return view('jadwal.index', compact('jadwals', 'filter'));
    }

    public function create()
    {
        return view('jadwal.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_pemeriksaan' => ['required', 'string', 'max:255'],
            'fasilitas_klinik'  => ['required', 'string', 'max:255'],
            'tanggal'           => ['required', 'date'],
            'waktu'             => ['required', 'date_format:H:i'],
            'catatan'           => ['nullable', 'string', 'max:500'],
        ]);

        $status = \Carbon\Carbon::parse($validated['tanggal'])->startOfDay()->lt(now()->startOfDay())
            ? 'selesai'
            : 'mendatang';

        Jadwal::create([
            'user_id'           => Auth::id(),
            'jenis_pemeriksaan' => $validated['jenis_pemeriksaan'],
            'fasilitas_klinik'  => $validated['fasilitas_klinik'],
            'tanggal'           => $validated['tanggal'],
            'waktu'             => $validated['waktu'],
            'catatan'           => $validated['catatan'] ?? null,
            'status'            => $status,
        ]);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan');
    }

    public function edit(Jadwal $jadwal)
    {
        abort_if($jadwal->user_id !== Auth::id(), 403);
        return view('jadwal.edit', compact('jadwal'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        abort_if($jadwal->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'jenis_pemeriksaan' => ['required', 'string', 'max:255'],
            'fasilitas_klinik'  => ['required', 'string', 'max:255'],
            'tanggal'           => ['required', 'date'],
            'waktu'             => ['required', 'date_format:H:i'],
            'catatan'           => ['nullable', 'string', 'max:500'],
            'status'            => ['required', 'in:mendatang,selesai,batal'],
        ]);

        $jadwal->update($validated);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diperbarui');
    }

    public function tandaiSelesai(Jadwal $jadwal)
    {
        abort_if($jadwal->user_id !== Auth::id(), 403);
        $jadwal->update(['status' => 'selesai']);
        return redirect()->route('dashboard')->with('success', 'Jadwal berhasil ditandai selesai.');
    }

    public function destroy(Jadwal $jadwal)
    {
        abort_if($jadwal->user_id !== Auth::id(), 403);
        $jadwal->delete();
        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus');
    }
}
