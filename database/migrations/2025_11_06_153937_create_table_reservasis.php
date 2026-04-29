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
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id(); // id PK
            
            // Kolom FK ke Siswa (asumsi id_grup_or_user merujuk ke siswa)
            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');
            
            // Keterangan umum izin/reservasi
            $table->string('keterangan'); // keterangan STRING
            
            // Tambahkan kolom tanggal agar reservasi spesifik per hari
            $table->date('tanggal')->nullable();

            $table->timestamps();

            // Menjamin hanya ada satu reservasi per siswa per hari
            $table->unique(['id_siswa', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasis');
    }
};