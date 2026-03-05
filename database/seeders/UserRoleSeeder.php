<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'mahasiswa']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'dosen']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Keuangan']);
    }
}
