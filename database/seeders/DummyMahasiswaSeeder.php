<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mahasiswa;
use App\Models\RiwayatPendidikan;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\ReferenceWilayah;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class DummyMahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $prodis = ProgramStudi::all();
        $activeSemester = Semester::where('a_periode_aktif', 1)->orderBy('id_semester', 'desc')->first();

        if (!$activeSemester) {
            $this->command->error("Tidak ada semester aktif ditemukan. Seeder dibatalkan.");
            return;
        }

        // Get a valid wilayah (kecamatan level 3)
        $wilayah = ReferenceWilayah::where('id_level_wilayah', 3)->first();
        $idWilayah = $wilayah ? trim($wilayah->id_wilayah) : '010000  ';

        $this->command->info("Men-generate data Mahasiswa untuk semester aktif: " . $activeSemester->nama_semester);

        foreach ($prodis as $prodi) {
            $this->command->info("Memproses prodi: " . $prodi->nama_program_studi);

            for ($i = 1; $i <= 3; $i++) {
                // Generate Mahasiswa
                $mahasiswa = Mahasiswa::create([
                    'nama_mahasiswa' => $faker->name,
                    'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                    'tempat_lahir' => $faker->city,
                    'tanggal_lahir' => $faker->date('Y-m-d', '-20 years'),
                    'id_agama' => 1, // 1 = Islam
                    'nik' => $faker->numerify('################'),
                    'nisn' => $faker->numerify('##########'),
                    'nama_ibu_kandung' => $faker->name('female'),
                    'kewarganegaraan' => 'ID',
                    'id_wilayah' => $idWilayah,
                    'kelurahan' => $faker->streetName,
                    'penerima_kps' => 0,
                    'handphone' => $faker->numerify('08##########'),
                    'email' => $faker->unique()->safeEmail,

                    // Offline-first sync columns
                    'sumber_data' => 'lokal',
                    'status_sinkronisasi' => 'created_local',
                    'sync_action' => 'insert',
                ]);

                // Generate Riwayat Pendidikan (Enrollment record)
                RiwayatPendidikan::create([
                    'id_mahasiswa' => $mahasiswa->id,
                    'nim' => '24' . $faker->numerify('#######'),
                    'id_jenis_daftar' => '1', // 1 = Peserta didik baru
                    'id_periode_masuk' => $activeSemester->id_semester,
                    'tanggal_daftar' => now()->format('Y-m-d'),
                    'id_prodi' => trim($prodi->id_prodi),

                    // Offline-first sync columns
                    'sumber_data' => 'lokal',
                    'status_sinkronisasi' => 'created_local',
                    'sync_action' => 'insert',
                ]);
            }
        }

        $this->command->info("Seeder dummy Mahasiswa selesai dijalankan!");
    }
}
