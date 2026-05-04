<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * HealthData Model — PKE-2: Data Kesehatan Dasar
 * Maps to: public.health_data (Supabase)
 */
class HealthData extends Model
{
    protected $table = 'health_data';

    protected $fillable = [
        'user_id',
        'blood_type',
        'weight_kg',
        'height_cm',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'blood_sugar_mg_dl',
        'cholesterol_mg_dl',
        'allergies',
        'chronic_conditions',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'allergies'          => 'array',
        'chronic_conditions' => 'array',
        'weight_kg'          => 'decimal:2',
        'height_cm'          => 'decimal:2',
        'blood_sugar_mg_dl'  => 'decimal:2',
        'cholesterol_mg_dl'  => 'decimal:2',
        'recorded_at'        => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    /** BMI calculated attribute */
    public function getBmiAttribute(): ?float
    {
        if ($this->weight_kg && $this->height_cm) {
            $h = $this->height_cm / 100;
            return round($this->weight_kg / ($h * $h), 1);
        }
        return null;
    }

    /** BMI category */
    public function getBmiCategoryAttribute(): string
    {
        $bmi = $this->bmi;
        if (!$bmi) return '-';
        if ($bmi < 18.5) return 'Kekurangan Berat Badan';
        if ($bmi < 25.0) return 'Normal';
        if ($bmi < 30.0) return 'Kelebihan Berat Badan';
        return 'Obesitas';
    }
}
