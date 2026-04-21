<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthData extends Model
{
    protected $table = 'health_data';
    protected $fillable = [
        'blood_type',
        'allergies',
        'height',
        'weight',
        'medical_history',
        'last_checkup',
    ];
}
