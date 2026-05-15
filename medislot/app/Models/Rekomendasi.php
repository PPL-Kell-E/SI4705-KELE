<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekomendasi extends Model
{
    protected $table = 'rekomendasi';

    protected $fillable = [
        'user_id', 'nama', 'deskripsi', 'icon',
        'bg_color', 'icon_color', 'interval_days', 'interval_label', 'is_default',
    ];

    protected $casts = [
        'is_default'   => 'boolean',
        'interval_days'=> 'integer',
    ];
}
