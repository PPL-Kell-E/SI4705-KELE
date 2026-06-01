<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengingat_waktu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengingat_id')->constrained('pengingat')->cascadeOnDelete();
            $table->integer('offset_menit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengingat_waktu');
    }
};
