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
        Schema::create('transaksi_absens', function (Blueprint $table) {
            $table->id(); // transaksi_id (BIGINT PRIMARY KEY AUTO_INCREMENT)
            
            $table->foreignId('id_siswa')
                  ->constrained('siswas') // Merujuk ke tabel 'siswas'
                  ->onDelete('cascade'); // Menghapus transaksi jika siswa dihapus

            // Jenis aksi
            $table->enum('status', [
                'masuk', 
                'istirahat', 
                'kembali', 
                'pulang'
            ]);
            
            $table->dateTime('waktu_tap')->useCurrent(); 
            
            $table->index(['id_siswa', 'waktu_tap']);
            
            $table->timestamp('created_at')->useCurrent(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_absens');
    }
};