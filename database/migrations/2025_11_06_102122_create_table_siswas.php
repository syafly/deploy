<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 30);
            $table->string('id_card', 100);
            $table->string('no_orangtua');
            $table->foreignId('id_kelas')
                  ->constrained('kelas') // Merujuk ke tabel 'kelas'
                  ->onDelete('cascade'); // Jika kelas dihapus, siswa juga dihapus (opsional, bisa diubah)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
