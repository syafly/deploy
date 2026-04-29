<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaktuAbsen; // Import Model yang sudah dibuat

class WaktuAbsenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WaktuAbsen::create([
            'status' => 'masuk',
            'from' => '07:00:00', // Jam masuk ideal
            'to' => '07:15:00',   // Batas toleransi terlambat
        ]);

        WaktuAbsen::create([
            'status' => 'istirahat',
            'from' => '12:00:00', // Jam istirahat keluar ideal
            'to' => '12:15:00',   // Batas toleransi
        ]);
        
        WaktuAbsen::create([
            'status' => 'kembali_istirahat',
            'from' => '13:00:00', // Jam kembali masuk ideal
            'to' => '13:15:00',   // Batas toleransi terlambat
        ]);
        
        WaktuAbsen::create([
            'status' => 'pulang',
            'from' => '16:00:00', // Jam pulang ideal
            'to' => '16:15:00',   // Batas toleransi untuk tap terakhir
        ]);
    }
}