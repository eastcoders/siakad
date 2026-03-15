<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\RiwayatPendidikan;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DummyMahasiswaBaruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $prodis = ProgramStudi::all();

        if ($prodis->isEmpty()) {
            $this->command->error('Data Program Studi tidak ditemukan. Silakan jalankan seeder prodi terlebih dahulu.');

            return;
        }

        DB::transaction(function () use ($faker, $prodis) {
            for ($i = 1; $i <= 10; $i++) {
                // 1. Pilih Prodi Acak
                $prodi = $prodis->random();

                // 2. Generate NIM (25 + kode_prodi + nomor_urut)
                $nim = '25'.$prodi->kode_program_studi.str_pad($i, 3, '0', STR_PAD_LEFT);

                // 3. Simpan Biodata Mahasiswa
                $mahasiswa = Mahasiswa::create([
                    'id_feeder' => (string) str()->uuid(),
                    'nama_mahasiswa' => strtoupper($faker->name()),
                    'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                    'tempat_lahir' => substr($faker->city(), 0, 32),
                    'tanggal_lahir' => $faker->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
                    'id_agama' => $faker->randomElement([1, 2, 3, 4, 5, 6]),
                    'nik' => $faker->nik(),
                    'nisn' => $faker->numerify('##########'),
                    'nama_ibu_kandung' => strtoupper($faker->name('female')),
                    'id_wilayah' => '000000',
                    'kelurahan' => $faker->streetName(),
                    'jalan' => $faker->streetAddress(),
                    'rt' => $faker->numerify('#'),
                    'rw' => $faker->numerify('#'),
                    'kode_pos' => $faker->postcode(),
                    'handphone' => $faker->numerify('08##########'),
                    'email' => $faker->unique()->safeEmail(),
                    'sumber_data' => 'lokal',
                    'is_synced' => false,
                    'status_sinkronisasi' => 'created_local',
                ]);

                // 4. Simpan Riwayat Pendidikan
                RiwayatPendidikan::create([
                    'id_mahasiswa' => $mahasiswa->id, // Menggunakan bigint ID lokal
                    'nim' => $nim,
                    'id_jenis_daftar' => '1',
                    'id_jalur_daftar' => '12',
                    'id_pembiayaan' => '1',
                    'id_periode_masuk' => '20251',
                    'id_prodi' => $prodi->id_prodi,
                    'id_perguruan_tinggi' => '77609f0b-0f05-4796-827f-ed8b134eb5ac',
                    'tanggal_daftar' => '2025-09-01',
                    'biaya_masuk' => 0,
                    'sumber_data' => 'lokal',
                    'is_synced' => false,
                    'status_sinkronisasi' => 'created_local',
                ]);

                Log::info('CRUD_CREATE: DummyMahasiswaBaruSeeder berhasil membuat mahasiswa', [
                    'nim' => $nim,
                    'nama' => $mahasiswa->nama_mahasiswa,
                ]);
            }
        });

        $this->command->info('Berhasil men-generate 10 data Mahasiswa Baru angkatan 2025.');
    }
}
