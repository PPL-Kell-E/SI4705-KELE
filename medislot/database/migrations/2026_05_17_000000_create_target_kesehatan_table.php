<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->string('deskripsi')->nullable();
            $table->string('icon')->default('fas fa-bullseye');
            $table->string('icon_color')->default('#4a90d9');
            $table->string('icon_bg')->default('#e8f4ff');
            $table->unsignedInteger('target_aktivitas');
            $table->unsignedInteger('aktivitas_dilakukan')->default(0);
            $table->string('satuan')->default('kali');
            $table->date('tanggal_target');
            $table->timestamp('tercapai_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_kesehatan');
    }
};
