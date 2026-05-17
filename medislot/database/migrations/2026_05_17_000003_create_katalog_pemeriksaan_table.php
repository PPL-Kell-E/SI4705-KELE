<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('katalog_pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nama');
            $table->string('kategori');
            $table->string('icon')->default('fa-stethoscope');
            $table->string('bg_color')->default('#e8f5f0');
            $table->string('icon_color')->default('#2d9e72');
            $table->text('singkat')->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('persiapan')->nullable();
            $table->unsignedInteger('biaya_min')->default(0);
            $table->unsignedInteger('biaya_max')->default(0);
            $table->string('durasi')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('katalog_pemeriksaan');
    }
};
