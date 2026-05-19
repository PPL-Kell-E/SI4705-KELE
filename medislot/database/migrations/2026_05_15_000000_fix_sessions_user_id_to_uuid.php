<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite uses dynamic typing, no ALTER COLUMN needed
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE uuid USING user_id::text::uuid');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING NULL');
        }
    }
};
