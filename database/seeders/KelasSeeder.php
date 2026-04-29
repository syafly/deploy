<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kelas yang ingin diisi
        $kelas = [
            // PENTING: ID 1 harus ada untuk default migrasi Siswa sebelumnya
            ['nama_kelas' => 'Umum', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            
            ['nama_kelas' => 'X-A', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kelas' => 'X-B', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kelas' => 'XI-IPA', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kelas' => 'XI-IPS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nama_kelas' => 'XII-IPA', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        // Masukkan data ke tabel 'kelas'
        DB::table('kelas')->insert($kelas);
    }
}