<?php

namespace App\Http\Controllers;

use App\Models\KatalogPemeriksaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KatalogController extends Controller
{
    private array $iconOptions = [
        'fa-stethoscope'  => 'Stetoskop (Umum)',
        'fa-wave-square'  => 'Gelombang (EKG)',
        'fa-heart-pulse'  => 'Detak Jantung',
        'fa-heart'        => 'Jantung',
        'fa-tooth'        => 'Gigi',
        'fa-ear-deaf'     => 'THT',
        'fa-eye'          => 'Mata',
        'fa-eye-slash'    => 'Mata (Glaukoma)',
        'fa-vial'         => 'Tabung Lab',
        'fa-flask'        => 'Labu Lab',
        'fa-hand-dots'    => 'Kulit',
        'fa-x-ray'        => 'X-Ray',
        'fa-lungs'        => 'Paru-Paru',
        'fa-brain'        => 'Otak',
        'fa-bone'         => 'Tulang',
        'fa-microscope'   => 'Mikroskop',
        'fa-syringe'      => 'Suntikan',
        'fa-pills'        => 'Obat',
    ];

    private array $kategoriOptions = [
        'Jantung', 'Umum', 'Gigi', 'THT', 'Mata',
        'Laboratorium', 'Kulit', 'Radiologi', 'Saraf', 'Tulang',
    ];

    public function index(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            return $this->adminIndex($request);
        }

        $query    = strtolower($request->get('q', ''));
        $kategori = $request->get('kategori', '');

        $builder = KatalogPemeriksaan::where('status', 'aktif');

        if ($query) {
            $builder->where(function ($q) use ($query) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$query}%"])
                  ->orWhereRaw('LOWER(kategori) LIKE ?', ["%{$query}%"])
                  ->orWhereRaw('LOWER(singkat) LIKE ?', ["%{$query}%"]);
            });
        }

        if ($kategori) {
            $builder->where('kategori', $kategori);
        }

        $katalog      = $builder->orderBy('kategori')->orderBy('nama')->get();
        $kategoriList = KatalogPemeriksaan::where('status', 'aktif')
                            ->distinct()->orderBy('kategori')->pluck('kategori');

        return view('katalog.index', compact('katalog', 'query', 'kategori', 'kategoriList'));
    }

    private function adminIndex(Request $request)
    {
        $q        = $request->get('q', '');
        $kategori = $request->get('kategori', '');

        $builder = KatalogPemeriksaan::query();

        if ($q) {
            $builder->where(function ($query) use ($q) {
                $query->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($q) . '%'])
                      ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . strtolower($q) . '%']);
            });
        }

        if ($kategori) {
            $builder->where('kategori', $kategori);
        }

        $katalog      = $builder->orderBy('kategori')->orderBy('nama')->paginate(10)->withQueryString();
        $kategoriList = KatalogPemeriksaan::distinct()->orderBy('kategori')->pluck('kategori');
        $iconOptions  = $this->iconOptions;
        $kategoriOptions = $this->kategoriOptions;

        return view('katalog.admin-index', compact('katalog', 'q', 'kategori', 'kategoriList', 'iconOptions', 'kategoriOptions'));
    }

    public function show(string $slug)
    {
        $item = KatalogPemeriksaan::where('slug', $slug)->where('status', 'aktif')->firstOrFail();
        return view('katalog.show', compact('item'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'kategori'  => 'required|string|max:100',
            'singkat'   => 'nullable|string|max:500',
            'deskripsi' => 'nullable|string',
            'icon'      => 'required|string|max:50',
            'bg_color'  => 'required|string|max:20',
            'icon_color'=> 'required|string|max:20',
            'durasi'    => 'nullable|string|max:100',
            'biaya_min' => 'required|integer|min:0',
            'biaya_max' => 'required|integer|min:0',
            'status'    => 'required|in:aktif,nonaktif',
            'persiapan' => 'nullable|string',
        ]);

        $data['slug']      = $this->uniqueSlug(Str::slug($data['nama']));
        $data['persiapan'] = $data['persiapan']
            ? array_filter(array_map('trim', explode("\n", $data['persiapan'])))
            : null;

        KatalogPemeriksaan::create($data);

        return redirect()->route('katalog.index')->with('success', 'Pemeriksaan berhasil ditambahkan.');
    }

    public function update(Request $request, KatalogPemeriksaan $katalog)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:255',
            'kategori'  => 'required|string|max:100',
            'singkat'   => 'nullable|string|max:500',
            'deskripsi' => 'nullable|string',
            'icon'      => 'required|string|max:50',
            'bg_color'  => 'required|string|max:20',
            'icon_color'=> 'required|string|max:20',
            'durasi'    => 'nullable|string|max:100',
            'biaya_min' => 'required|integer|min:0',
            'biaya_max' => 'required|integer|min:0',
            'status'    => 'required|in:aktif,nonaktif',
            'persiapan' => 'nullable|string',
        ]);

        $data['persiapan'] = $data['persiapan']
            ? array_filter(array_map('trim', explode("\n", $data['persiapan'])))
            : null;

        $katalog->update($data);

        return redirect()->route('katalog.index')->with('success', 'Data pemeriksaan berhasil diperbarui.');
    }

    public function destroy(KatalogPemeriksaan $katalog)
    {
        $katalog->delete();
        return redirect()->route('katalog.index')->with('success', 'Data pemeriksaan berhasil dihapus.');
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i    = 1;
        while (KatalogPemeriksaan::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
