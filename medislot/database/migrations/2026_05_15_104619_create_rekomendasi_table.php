<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable()->index();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('icon', 50)->default('fa-stethoscope');
            $table->string('bg_color', 20)->default('#e8f5f0');
            $table->string('icon_color', 20)->default('#2d9e72');
            $table->integer('interval_days')->default(180);
            $table->string('interval_label', 50)->default('6 bulan');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi');
    }
};
