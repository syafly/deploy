<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreignId('id_user')
                  ->constrained('users') // Merujuk ke tabel 'siswas'
                  ->onDelete('cascade'); // Menghapus transaksi jika siswa dihapus
        });
    }

    public function down()
    {
        Schema::dropIfExists('login');
    }
}
