<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Examination;
use Illuminate\Support\Facades\DB;

class ExaminationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examinations = config('examinations.examinations');

        foreach ($examinations as $exam) {
            Examination::create($exam);
        }
    }
}
