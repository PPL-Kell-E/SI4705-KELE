<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengingat extends Model
{
    protected $table = 'pengingat';

    protected $fillable = ['jadwal_id', 'user_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function waktu()
    {
        return $this->hasMany(PengingatWaktu::class)->orderBy('offset_menit');
    }
}
