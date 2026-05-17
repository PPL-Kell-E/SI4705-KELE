<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KatalogPemeriksaan extends Model
{
    protected $table = 'katalog_pemeriksaan';

    protected $fillable = [
        'slug', 'nama', 'kategori', 'icon', 'bg_color', 'icon_color',
        'singkat', 'deskripsi', 'persiapan', 'biaya_min', 'biaya_max',
        'durasi', 'status',
    ];

    protected $casts = [
        'persiapan' => 'array',
        'biaya_min' => 'integer',
        'biaya_max' => 'integer',
    ];
}
