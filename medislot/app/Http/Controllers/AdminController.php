<?php

namespace App\Http\Controllers;

use App\Models\KatalogPemeriksaan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $totalKatalog   = KatalogPemeriksaan::count();
        $katalogAktif   = KatalogPemeriksaan::where('status', 'aktif')->count();
        $totalUser      = User::where('role', 'user')->count();
        $kategoriCount  = KatalogPemeriksaan::distinct('kategori')->count('kategori');

        $katalogTerbaru = KatalogPemeriksaan::latest()->limit(5)->get();

        return view('admin.index', compact(
            'totalKatalog', 'katalogAktif', 'totalUser', 'kategoriCount', 'katalogTerbaru'
        ));
    }
}
