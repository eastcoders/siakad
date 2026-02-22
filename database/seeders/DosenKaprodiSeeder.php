<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DosenKaprodiSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan Role Kaprodi ada di sistem
        $roleKaprodi = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Kaprodi']);

        // Dosen 1: Murni Dosen
        $dosenBiasa = clone \App\Models\Dosen::firstOrCreate(
            ['nidn' => '111222333'],
            [
                'nama' => 'Budi Santoso (Dosen Biasa)',
                'email' => 'budi@polsa.ac.id',
                'is_pengajar' => true,
                'is_active' => true,
            ]
        );

        // Dosen 2: Dosen + Kaprodi
        $dosenKaprodi = \App\Models\Dosen::firstOrCreate(
            ['nidn' => '444555666'],
            [
                'nama' => 'Dr. Siti Aminah (Kaprodi)',
                'email' => 'siti@polsa.ac.id',
                'is_pengajar' => true,
                'is_active' => true,
                'is_struktural' => true,
            ]
        );

        // Berikan Role Kaprodi ke akun Siti melalui relasi Dosen -> Akun (User)
        // Note: Akun (User) secara otomatis dibuat oleh DosenObserver.
        if ($dosenKaprodi->akun) {
            $dosenKaprodi->akun->assignRole($roleKaprodi);
        }
    }
}
