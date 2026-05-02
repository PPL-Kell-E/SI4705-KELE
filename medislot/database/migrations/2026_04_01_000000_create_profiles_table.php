<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');
            $table->integer('age')->default(1);
            $table->string('gender')->default('Lainnya');
            $table->string('phone', 20)->nullable();
            $table->string('address', 500)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('avatar_url')->nullable();
            $table->string('role', 50)->default('user');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
