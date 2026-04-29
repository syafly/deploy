<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pertama, buat user terlebih dahulu
        $user = User::create([
            'nama' => 'Administrator',
        ]);

        // Kemudian buat login credentials
        Login::create([
            'id_user' => $user->id,
            'username' => 'admin',
            'password' => Hash::make('123'),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Username: admin');
        $this->command->info('Password: 123');
    }
}