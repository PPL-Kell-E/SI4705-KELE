<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExaminationBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'booking_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'booking_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(ExaminationSchedule::class);
    }
}
