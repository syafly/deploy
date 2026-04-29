<?php 

// database/migrations/..._create_attendance_rules_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            // Kolom untuk 4 aktivitas (1=True, 0=False)
            $table->tinyInteger('masuk')->default(0);
            $table->tinyInteger('istirahat')->default(0);
            $table->tinyInteger('kembali_istirahat')->default(0);
            $table->tinyInteger('pulang')->default(0);
            
            // Kolom untuk status hasil (Masuk, Izin, Alpa)
            $table->enum('status_output', ['masuk', 'alpa']);
            
            // Kolom unik untuk memastikan 16 kombinasi tidak duplikat
            $table->unique(['masuk', 'istirahat', 'kembali_istirahat', 'pulang'], 'unique_rule_combination');

            // Kita tidak memerlukan timestamps (created_at, updated_at) untuk tabel aturan statis ini
            // $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};