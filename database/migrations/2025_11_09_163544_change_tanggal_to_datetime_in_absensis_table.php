<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Ubah kolom tanggal dari date menjadi datetime
            $table->datetime('tanggal')->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            // Kembalikan ke date jika rollback
            $table->date('tanggal')->change();
        });
    }
};