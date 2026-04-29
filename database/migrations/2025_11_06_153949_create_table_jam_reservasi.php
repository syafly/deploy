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
        Schema::create('jam_reservasis', function (Blueprint $table) {
            $table->id(); // id PK
            
            // FK ke tabel reservasis
            $table->foreignId('id_reservasi')->constrained('reservasis')->onDelete('cascade'); // id_reservasi FK(RESERVASI)
            
            // Status fase waktu yang diizinkan/dipesan
            $table->enum('status', ['masuk', 'kembali_istirahat', 'pulang']); // status ENUM
            
            // Waktu spesifik untuk reservasi tersebut (misalnya, izin pulang jam 14:30:00)
            $table->time('jam'); // jam TIME

            $table->timestamps();

            // Menjamin hanya ada satu jam reservasi per status per reservasi
            $table->unique(['id_reservasi', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_reservasis');
    }
};