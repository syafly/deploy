<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('jam_reservasis');
    }

    public function down(): void
    {
        // Jika perlu rollback, recreate table (sesuai struktur sebelumnya)
        Schema::create('jam_reservasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_reservasi')->constrained('reservasis')->onDelete('cascade');
            $table->enum('status', ['masuk', 'kembali_istirahat', 'pulang']);
            $table->time('jam');
            $table->timestamps();
            $table->unique(['id_reservasi', 'status']);
        });
    }
};