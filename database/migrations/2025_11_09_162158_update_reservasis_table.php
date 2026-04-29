<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservasis', function (Blueprint $table) {
            // Pertama, drop foreign key constraints yang mungkin bergantung
            $table->dropForeign(['id_siswa']);
            
            // Hapus unique constraint lama
            $table->dropUnique(['id_siswa', 'tanggal']);
            
            // Kembalikan foreign key
            $table->foreign('id_siswa')->references('id')->on('siswas')->onDelete('cascade');
            
            // Hapus kolom tanggal
            $table->dropColumn('tanggal');
            
            // Tambah kolom id_kelas
            $table->foreignId('id_kelas')->nullable()->constrained('kelas')->onDelete('cascade');
            
            // Tambah kolom waktu (datetime) untuk patokan izin
            $table->datetime('waktu')->nullable();
            
            // Ganti unique constraint untuk include id_kelas dan waktu
            $table->unique(['id_siswa', 'id_kelas', 'waktu']);
        });
    }

    public function down(): void
    {
        Schema::table('reservasis', function (Blueprint $table) {
            // Rollback changes
            $table->dropUnique(['id_siswa', 'id_kelas', 'waktu']);
            $table->dropColumn(['id_kelas', 'waktu']);
            
            // Kembalikan kolom tanggal
            $table->date('tanggal')->nullable();
            
            // Drop dan recreate foreign key untuk menghindari error
            $table->dropForeign(['id_siswa']);
            $table->unique(['id_siswa', 'tanggal']);
            $table->foreign('id_siswa')->references('id')->on('siswas')->onDelete('cascade');
        });
    }
};