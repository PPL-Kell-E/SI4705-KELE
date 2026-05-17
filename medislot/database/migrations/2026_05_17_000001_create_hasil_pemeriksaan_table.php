<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis_pemeriksaan');
            $table->date('tanggal_pemeriksaan');
            $table->string('fasilitas_kesehatan')->nullable();
            $table->string('nama_dokter')->nullable();
            $table->text('hasil_pemeriksaan');
            $table->text('catatan_tambahan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_pemeriksaan');
    }
};
