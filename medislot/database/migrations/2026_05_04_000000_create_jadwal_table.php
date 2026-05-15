<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis_pemeriksaan');
            $table->string('fasilitas_klinik');
            $table->date('tanggal');
            $table->time('waktu');
            $table->text('catatan')->nullable();
            $table->enum('status', ['mendatang', 'selesai', 'batal'])->default('mendatang');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
