<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum dengan menambahkan nilai "izin"
        DB::statement("ALTER TABLE absensis MODIFY status ENUM('masuk', 'izin', 'alpa', '-')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum sebelumnya
        DB::statement("ALTER TABLE absensis MODIFY status ENUM('masuk', 'alpa', '-')");
    }
};
