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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id(); // Kolom ID utama (primary key)
            
            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');
            
            $table->date('tanggal');

            $table->string('keterangan')->default('-'); 
            
            $table->timestamps(); // Kolom created_at dan updated_at
            
            // Tambahkan unique constraint agar satu siswa hanya punya satu absensi per tanggal
            $table->unique(['id_siswa', 'tanggal']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
