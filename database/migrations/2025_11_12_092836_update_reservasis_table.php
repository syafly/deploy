<?php
// database/migrations/xxxx_reset_reservasis_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReservasisTable extends Migration
{
    public function up()
    {
        // 1. HAPUS TABEL LAMA (jika data kosong)
        Schema::dropIfExists('reservasis');

        // 2. BUAT TABEL BARU DENGAN STRUKTUR YANG DIINGINKAN
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->string('keterangan');
            $table->datetime('waktu_mulai');
            $table->datetime('waktu_akhir');
            $table->timestamps();
            
            // Unique constraint baru (optional)
            // $table->unique(['id_siswa', 'id_kelas', 'waktu_mulai']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservasis');
        
        // Buat kembali tabel dengan struktur lama
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->string('keterangan');
            $table->datetime('waktu');
            $table->timestamps();
            
            $table->unique(['id_siswa', 'id_kelas', 'waktu']);
        });
    }
}