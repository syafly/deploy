<?php 

// database/seeders/AttendanceRulesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penilaian;

class AttendanceRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // M, I, K, P | Status
            [0, 0, 0, 0, 'Alpa'], 
            [0, 0, 0, 1, 'Alpa'],
            [0, 0, 1, 0, 'Alpa'], 
            [0, 0, 1, 1, 'Alpa'],
            [0, 1, 0, 0, 'Alpa'], 
            [0, 1, 0, 1, 'Alpa'],
            [0, 1, 1, 0, 'Alpa'], 
            [0, 1, 1, 1, 'Alpa'],
            [1, 0, 0, 0, 'Alpa'], 
            [1, 0, 0, 1, 'Masuk'],
            [1, 0, 1, 0, 'Masuk'],
            [1, 0, 1, 1, 'Masuk'],
            [1, 1, 0, 0, 'Masuk'], 
            [1, 1, 0, 1, 'Masuk'],
            [1, 1, 1, 0, 'Masuk'], 
            [1, 1, 1, 1, 'Masuk'], // Contoh: Kasus #15
            
            // Pastikan Anda memasukkan semua 16 kombinasi unik di sini
        ];

        foreach ($rules as $rule) {
            Penilaian::create([
                'masuk' => $rule[0],
                'istirahat' => $rule[1],
                'kembali_istirahat' => $rule[2],
                'pulang' => $rule[3],
                'status_output' => $rule[4],
            ]);
        }
    }
}