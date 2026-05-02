<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Profile Model — PKE-1: Pengelolaan Profil
 * Maps to: public.profiles (Supabase)
 */
class Profile extends Model
{
    protected $table      = 'profiles';
    protected $primaryKey = 'id';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = [
        'id',
        'name',
        'age',
        'gender',
        'phone',
        'address',
        'birth_date',
        'avatar_url',
        'role',
    ];

    protected $casts = [
        'age'        => 'integer',
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** Relasi ke health data terbaru */
    public function healthData()
    {
        return $this->hasMany(HealthData::class, 'user_id', 'id');
    }

    public function latestHealthData()
    {
        return $this->hasOne(HealthData::class, 'user_id', 'id')->latestOfMany('recorded_at');
    }
}
