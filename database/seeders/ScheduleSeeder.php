<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        Schedule::create([
            'checkup_type' => 'Pemeriksaan Gigi Rutin',
            'facility' => 'Klinik Gigi',
            'date' => '2026-04-12',
            'time' => '10:00',
            'notes' => 'Scaling dan cek gigi berlubang',
            'status' => 'upcoming',
        ]);

        Schedule::create([
            'checkup_type' => 'Cek Darah lengkap',
            'facility' => 'Klinik Gigi',
            'date' => '2026-04-20',
            'time' => '10:00',
            'notes' => 'Puasa 10 jam sebelum periksa',
            'status' => 'upcoming',
        ]);

        Schedule::create([
            'checkup_type' => 'Konsultasi Umum',
            'facility' => 'Klinik Medina',
            'date' => '2026-04-01',
            'time' => '10:00',
            'notes' => null,
            'status' => 'completed',
        ]);
    }
}
