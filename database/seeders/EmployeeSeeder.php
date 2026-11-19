<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Employee::insert([
            ['name' => 'Budi Santoso', 'department' => 'HRD', 'email' => 'budi@company.com'],
            ['name' => 'Siti Aminah', 'department' => 'Finance', 'email' => 'siti@company.com'],
            ['name' => 'Andi Pratama', 'department' => 'IT Support', 'email' => 'andi@company.com'],
        ]);
    }
}
