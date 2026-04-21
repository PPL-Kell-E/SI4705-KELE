<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'examination_id',
        'age_min',
        'age_max',
        'frequency',
        'frequency_unit',
        'description',
        'is_active'
    ];

    protected $casts = [
        'age_min' => 'integer',
        'age_max' => 'integer',
        'is_active' => 'boolean',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }
}
