<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EmployeeSeeder::class,
        ]);

        // Akun 1: Admin (Akses penuh: Dashboard + Laporan Analisis AI)
        User::updateOrCreate(
            ['email' => 'admin@lobbyflow.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Akun 2: Resepsionis (Akses operasional: Form Check-In & Tabel Live)
        User::updateOrCreate(
            ['email' => 'resepsionis@lobbyflow.com'],
            [
                'name' => 'Resepsionis Lobi',
                'password' => Hash::make('password'),
                'role' => 'receptionist',
            ]
        );
    }
}
