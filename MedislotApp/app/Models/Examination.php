<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Examination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'category',
        'price',
        'duration',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function schedules()
    {
        return $this->hasMany(ExaminationSchedule::class);
    }

    public function recommendations()
    {
        return $this->hasMany(ScheduleRecommendation::class);
    }
}
