<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengingatWaktu extends Model
{
    protected $table = 'pengingat_waktu';

    protected $fillable = ['pengingat_id', 'offset_menit'];

    public function pengingat()
    {
        return $this->belongsTo(Pengingat::class);
    }

    public function offsetLabel(): string
    {
        $m = $this->offset_menit;
        if ($m >= 1440 && $m % 1440 === 0) {
            $d = $m / 1440;
            return "H-{$d}";
        }
        if ($m >= 60 && $m % 60 === 0) {
            return ($m / 60) . ' jam sebelum';
        }
        return "{$m} menit sebelum";
    }

    public function chipLabel(): string
    {
        $m = $this->offset_menit;
        if ($m >= 1440 && $m % 1440 === 0) {
            return '+' . ($m / 1440) . 'd';
        }
        if ($m >= 60) {
            return '+' . round($m / 60) . 'h';
        }
        return "+{$m}m";
    }
}
