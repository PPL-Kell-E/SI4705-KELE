<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetKesehatan extends Model
{
    protected $table = 'target_kesehatan';

    protected $fillable = [
        'user_id', 'nama', 'deskripsi', 'icon', 'icon_color', 'icon_bg',
        'target_aktivitas', 'aktivitas_dilakukan', 'satuan',
        'tanggal_target', 'tercapai_at',
    ];

    protected $casts = [
        'tanggal_target' => 'date',
        'tercapai_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressAttribute(): int
    {
        if ($this->target_aktivitas === 0) return 0;
        return min(100, (int) round(($this->aktivitas_dilakukan / $this->target_aktivitas) * 100));
    }

    public function getStatusAttribute(): string
    {
        $p = $this->progress;
        if ($p >= 100) return 'tercapai';
        if ($p >= 50)  return 'on-track';
        return 'perlu-perhatian';
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'tercapai'        => 'Tercapai',
            'on-track'        => 'On Track',
            default           => 'Perlu Perhatian',
        };
    }
}
