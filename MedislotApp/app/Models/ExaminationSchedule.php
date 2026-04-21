<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExaminationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'examination_id',
        'schedule_date',
        'start_time',
        'end_time',
        'max_capacity',
        'current_capacity',
        'status'
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'max_capacity' => 'integer',
        'current_capacity' => 'integer',
    ];

    public function examination()
    {
        return $this->belongsTo(Examination::class);
    }

    public function bookings()
    {
        return $this->hasMany(ExaminationBooking::class);
    }

    public function isAvailable()
    {
        return $this->current_capacity < $this->max_capacity && $this->status === 'available';
    }
}
