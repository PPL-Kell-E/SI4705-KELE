<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilPemeriksaan extends Model
{
    protected $table = 'hasil_pemeriksaan';

    protected $fillable = [
        'user_id',
        'jenis_pemeriksaan',
        'tanggal_pemeriksaan',
        'fasilitas_kesehatan',
        'nama_dokter',
        'hasil_pemeriksaan',
        'catatan_tambahan',
    ];

    protected $casts = [
        'tanggal_pemeriksaan' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
