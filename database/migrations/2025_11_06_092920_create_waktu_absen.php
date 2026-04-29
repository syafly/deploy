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
        // Tabel WAKTU_ABSEN
        Schema::create('waktu_absen', function (Blueprint $table) {
            $table->id(); // id PK
            $table->enum('status', ['masuk', 'istirahat', 'kembali_istirahat', 'pulang']); // status ENUM
            $table->time('from'); // from TIME
            $table->time('to'); // to TIME
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waktu_absen');
    }
};