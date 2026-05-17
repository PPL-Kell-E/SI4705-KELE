<?php

namespace App\Http\Controllers;

use App\Models\TargetKesehatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetKesehatanController extends Controller
{
    public function index(Request $request)
    {
        $filter  = $request->get('filter', 'semua');
        $userId  = Auth::id();

        $allTargets = TargetKesehatan::where('user_id', $userId)
            ->orderBy('tanggal_target')
            ->get();

        $targets = $filter === 'semua'
            ? $allTargets
            : $allTargets->filter(fn($t) => $t->status === $filter)->values();

        $total           = $allTargets->count();
        $onTrack         = $allTargets->filter(fn($t) => $t->status === 'on-track')->count();
        $perluPerhatian  = $allTargets->filter(fn($t) => $t->status === 'perlu-perhatian')->count();
        $tercapai        = $allTargets->filter(fn($t) => $t->status === 'tercapai')->count();

        $pencapaianTerbaru = TargetKesehatan::where('user_id', $userId)
            ->whereNotNull('tercapai_at')
            ->orderByDesc('tercapai_at')
            ->first();

        return view('target-kesehatan.index', compact(
            'targets', 'filter', 'total', 'onTrack', 'perluPerhatian', 'tercapai', 'pencapaianTerbaru'
        ));
    }

    public function create()
    {
        return view('target-kesehatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'                => ['required', 'string', 'max:255'],
            'deskripsi'           => ['nullable', 'string', 'max:500'],
            'icon'                => ['required', 'string', 'max:60'],
            'icon_color'          => ['required', 'string', 'max:20'],
            'icon_bg'             => ['required', 'string', 'max:20'],
            'target_aktivitas'    => ['required', 'integer', 'min:1'],
            'aktivitas_dilakukan' => ['required', 'integer', 'min:0'],
            'satuan'              => ['required', 'string', 'max:50'],
            'tanggal_target'      => ['required', 'date', 'after:today'],
        ]);

        $validated['user_id'] = Auth::id();

        if ($validated['aktivitas_dilakukan'] >= $validated['target_aktivitas']) {
            $validated['tercapai_at'] = now();
        }

        TargetKesehatan::create($validated);

        return redirect()->route('target-kesehatan.index')
            ->with('success', 'Target kesehatan berhasil ditambahkan!');
    }

    public function edit(TargetKesehatan $target)
    {
        abort_if($target->user_id !== Auth::id(), 403);
        return view('target-kesehatan.edit', compact('target'));
    }

    public function update(Request $request, TargetKesehatan $target)
    {
        abort_if($target->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'nama'                => ['required', 'string', 'max:255'],
            'deskripsi'           => ['nullable', 'string', 'max:500'],
            'icon'                => ['required', 'string', 'max:60'],
            'icon_color'          => ['required', 'string', 'max:20'],
            'icon_bg'             => ['required', 'string', 'max:20'],
            'target_aktivitas'    => ['required', 'integer', 'min:1'],
            'aktivitas_dilakukan' => ['required', 'integer', 'min:0'],
            'satuan'              => ['required', 'string', 'max:50'],
            'tanggal_target'      => ['required', 'date'],
        ]);

        if ($validated['aktivitas_dilakukan'] >= $validated['target_aktivitas']
            && $target->tercapai_at === null) {
            $validated['tercapai_at'] = now();
        }

        if ($validated['aktivitas_dilakukan'] < $validated['target_aktivitas']) {
            $validated['tercapai_at'] = null;
        }

        $target->update($validated);

        return redirect()->route('target-kesehatan.index')
            ->with('success', 'Target kesehatan berhasil diperbarui!');
    }

    public function destroy(TargetKesehatan $target)
    {
        abort_if($target->user_id !== Auth::id(), 403);
        $target->delete();

        return redirect()->route('target-kesehatan.index')
            ->with('success', 'Target kesehatan berhasil dihapus!');
    }
}
