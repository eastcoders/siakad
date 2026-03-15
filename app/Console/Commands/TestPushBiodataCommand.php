<?php

namespace App\Console\Commands;

use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestPushBiodataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-push-biodata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test push 10 data dummy Biodata Mahasiswa ke sandbox Neo Feeder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pengujian push Biodata Mahasiswa ke Sandbox Feeder...');

        // 1. Authentication Phase (GetToken)
        $url = config('services.feeder.url');
        $username = config('services.feeder.username');
        $password = config('services.feeder.password');

        $this->info("Mendapatkan token otentikasi dari: {$url}");
        Log::info('SYNC_PULL: Mencoba mendapatkan token Feeder', ['url' => $url, 'username' => $username]);

        try {
            $authResponse = Http::timeout(30)->post($url, [
                'act' => 'GetToken',
                'username' => $username,
                'password' => $password,
            ]);

            $authResult = $authResponse->json();

            if (! $authResponse->successful() || ! isset($authResult['error_code']) || $authResult['error_code'] !== 0) {
                $errorMsg = $authResult['error_desc'] ?? 'Gagal menghubungi server atau format response tidak dikenal.';
                $this->error('Gagal mendapatkan token: '.$errorMsg);
                $this->line('HTTP Status: '.$authResponse->status());
                $this->line('Raw Body: '.$authResponse->body());
                Log::error('SYNC_PULL_FAILED: Gagal mendapatkan token', ['response' => $authResult ?? $authResponse->body()]);

                return self::FAILURE;
            }

            $token = $authResult['data']['token'];
            $this->info('Token berhasil didapatkan.');
            Log::info('SYNC_PULL_SUCCESS: Token didapatkan');

        } catch (\Exception $e) {
            $this->error('System Error saat otentikasi: '.$e->getMessage());
            Log::error('SYSTEM_ERROR: Exception saat GetToken', ['message' => $e->getMessage()]);

            return self::FAILURE;
        }

        // 2. Execution Phase
        $faker = Faker::create('id_ID');
        $totalData = 1;
        $successCount = 0;
        $failedCount = 0;

        Log::info('SYNC_PUSH: Mengirim data BiodataMahasiswa ke server sandbox', ['count' => $totalData, 'endpoint' => $url]);

        $this->output->progressStart($totalData);

        for ($i = 0; $i < $totalData; $i++) {
            // Generate data dummy valid
            $record = [
                'nama_mahasiswa' => 'TESTING DUMMY HAPUS '.rand(100, 999),
                'jenis_kelamin' => $faker->randomElement(['L', 'P']),
                'tempat_lahir' => $faker->city(),
                'tanggal_lahir' => $faker->dateTimeBetween('-25 years', '-18 years')->format('Y-m-d'),
                'id_agama' => $faker->randomElement([1, 2, 3, 4, 5, 6]), // 1: Islam, 2: Kristen, dsb
                'nama_ibu_kandung' => strtoupper($faker->name('female')),
                'kewarganegaraan' => 'ID',
                'nik' => $faker->nik(),
                'nisn' => $faker->numerify('##########'),
                'jalan' => $faker->streetAddress(),
                'rt' => '00',
                'rw' => '00',
                'nama_dusun' => 'Dusun '.$faker->word(),
                'kelurahan' => $faker->streetName(),
                'kode_pos' => $faker->postcode(),
                'handphone' => $faker->numerify('08##########'),
                'email' => $faker->email(),
                'id_wilayah' => '000000', // Dummy API wilayah kode
                'penerima_kps' => 0,
            ];

            $payload = [
                'act' => 'InsertBiodataMahasiswa',
                'token' => $token,
                'record' => $record,
            ];

            try {
                $response = Http::timeout(30)->post($url, $payload);
                $result = $response->json();

                if ($response->successful() && isset($result['error_code']) && $result['error_code'] == 0) {
                    $successCount++;
                    $idMahasiswa = $result['data']['id_mahasiswa'] ?? 'N/A';
                    $this->info(" [SUCCESS] Data berhasil terkirim. ID Mahasiswa (UUID): {$idMahasiswa}");
                    Log::info('CRUD_CREATE: Data dummy BiodataMahasiswa berhasil di-push ke live', ['data' => $record, 'response' => $result, 'id_mahasiswa' => $idMahasiswa]);
                } else {
                    $failedCount++;
                    Log::warning('SYNC_PUSH: Gagal insert per record ke sandbox', ['data' => $record, 'response' => $result ?? $response->body()]);
                }
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('SYSTEM_ERROR: Exception saat hit sandbox Feeder', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->newLine();

        Log::info('SYNC_SUCCESS: Sinkronisasi dummy BiodataMahasiswa selesai', ['success' => $successCount, 'failed' => $failedCount]);

        $this->info('Pengujian Selesai.');
        $this->info("Berhasil : {$successCount}");

        if ($failedCount > 0) {
            $this->error("Gagal    : {$failedCount}");
        } else {
            $this->info("Gagal    : {$failedCount}");
        }

        return self::SUCCESS;
    }
}
