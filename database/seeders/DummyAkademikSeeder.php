<?php

namespace Database\Seeders;

use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\RefProdi;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DummyAkademikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Mengambil Prodi valid dari lokal (RefProdi atau ProgramStudi)
        // Berdasarkan audit sebelumnya, kita punya "Perbankan Syariah" dengan ID prodi dari ref_prodis
        $prodi = RefProdi::first();
        $idProdi = $prodi ? $prodi->id : '9d0d123d-7020-43cf-8183-32bae34b5341';
        
        // Mengambil Semester aktif
        $semester = Semester::where('a_periode_aktif', '1')->first();
        $idSemester = $semester ? $semester->id_semester : '20241';

        DB::transaction(function () use ($faker, $idProdi, $idSemester) {
            
            // 1. Generate 10 Mata Kuliah
            $mataKuliahs = [];
            for ($i = 1; $i <= 10; $i++) {
                $mataKuliahs[] = MataKuliah::create([
                    'id_matkul' => (string) Str::uuid(),
                    'id_prodi' => $idProdi,
                    'kode_mk' => 'TEST-MK-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'nama_mk' => 'Mata Kuliah Testing ' . $i . ' (' . $faker->sentence(3) . ')',
                    'sks' => $faker->randomElement([2, 3, 4]),
                    'sks_tatap_muka' => 2,
                    'sks_praktek' => 1,
                    'jenis_mk' => $faker->randomElement(['A', 'B']),
                    'status_aktif' => true,
                    'sumber_data' => 'lokal',
                    'status_sinkronisasi' => 'created_local',
                    'sync_action' => 'insert',
                ]);
            }

            // 2. Generate 10 Kurikulum
            $kurikulums = [];
            for ($i = 1; $i <= 10; $i++) {
                $kurikulums[] = Kurikulum::create([
                    'id_kurikulum' => (string) Str::uuid(),
                    'nama_kurikulum' => 'Kurikulum Testing 2025 v' . $i,
                    'id_prodi' => $idProdi,
                    'id_semester' => $idSemester,
                    'jumlah_sks_lulus' => 144,
                    'jumlah_sks_wajib' => 120,
                    'jumlah_sks_pilihan' => 24,
                    'sumber_data' => 'lokal',
                    'status_sinkronisasi' => 'created_local',
                    'sync_action' => 'insert',
                ]);
            }

            // 3. Generate 10 Matkul Kurikulum (Pivot)
            // Memetakan secara acak/berurutan untuk demonstrasi
            foreach ($mataKuliahs as $index => $mk) {
                $kurikulum = $kurikulums[$index]; // 1-to-1 untuk seeding ini agar tersebar
                
                // Menggunakan relationship attach atau manual DB insert
                // Berdasarkan model Kurikulum, kita bisa gunakan relationship matakuliah()
                $kurikulum->matakuliah()->attach($mk->id_matkul, [
                    'semester' => $faker->numberBetween(1, 8),
                    'sks_mata_kuliah' => $mk->sks,
                    'sks_tatap_muka' => $mk->sks_tatap_muka,
                    'sks_praktek' => $mk->sks_praktek,
                    'apakah_wajib' => ($mk->jenis_mk === 'A' ? 1 : 0),
                    'status_sinkronisasi' => 'created_local',
                    'sumber_data' => 'lokal',
                ]);
            }
        });
    }
}
