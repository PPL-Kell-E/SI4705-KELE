<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedule_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained()->onDelete('cascade');
            $table->integer('age_min');
            $table->integer('age_max');
            $table->integer('frequency'); // 1, 2, 3, etc.
            $table->enum('frequency_unit', ['hari', 'minggu', 'bulan', 'tahun']);
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('examination_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_recommendations');
    }
};
