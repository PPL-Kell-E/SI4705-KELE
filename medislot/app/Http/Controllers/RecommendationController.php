<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    private array $recommendations = [
        [
            'key'            => 'dental',
            'name'           => 'Dental Check-up',
            'description'    => 'Disarankan setiap 6 bulan sekali',
            'icon'           => 'fa-tooth',
            'bg_color'       => '#e8f5f0',
            'icon_color'     => '#2d9e72',
            'interval_days'  => 180,
            'interval_label' => '6 bulan',
        ],
        [
            'key'            => 'medical',
            'name'           => 'Medical Check-up',
            'description'    => 'Jaga kesehatan secara menyeluruh',
            'icon'           => 'fa-kit-medical',
            'bg_color'       => '#e8f0f5',
            'icon_color'     => '#3a7abf',
            'interval_days'  => 365,
            'interval_label' => '1 tahun',
        ],
        [
            'key'            => 'mata',
            'name'           => 'Pemeriksaan Mata',
            'description'    => 'Disarankan setahun sekali',
            'icon'           => 'fa-eye',
            'bg_color'       => '#f0f5e8',
            'icon_color'     => '#6a9e2d',
            'interval_days'  => 365,
            'interval_label' => '1 tahun',
        ],
        [
            'key'            => 'jantung',
            'name'           => 'Pemeriksaan Kesehatan Jantung',
            'description'    => 'Mulai pantau kesehatan jantungmu',
            'icon'           => 'fa-heart-pulse',
            'bg_color'       => '#fdf0f0',
            'icon_color'     => '#e05252',
            'interval_days'  => 180,
            'interval_label' => '6 bulan',
        ],
        [
            'key'            => 'tht',
            'name'           => 'Pemeriksaan THT',
            'description'    => 'Disarankan setiap 6 bulan sekali',
            'icon'           => 'fa-ear-deaf',
            'bg_color'       => '#fdf5e8',
            'icon_color'     => '#bf8a3a',
            'interval_days'  => 180,
            'interval_label' => '6 bulan',
        ],
    ];

    public function index()
    {
        return view('rekomendasi.index', [
            'recommendations' => $this->recommendations,
        ]);
    }
}
