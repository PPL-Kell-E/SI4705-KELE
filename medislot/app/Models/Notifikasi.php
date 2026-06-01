<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    protected $fillable = [
        'user_id', 'jadwal_id', 'pengingat_waktu_id',
        'judul', 'pesan', 'is_read', 'notified_at',
    ];

    protected $casts = [
        'is_read'     => 'boolean',
        'notified_at' => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
