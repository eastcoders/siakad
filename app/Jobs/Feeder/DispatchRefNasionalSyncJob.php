<?php

namespace App\Jobs\Feeder;

use App\Services\NeoFeederService;
use App\Services\ReferensiServices\WilayahRefService;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class DispatchRefNasionalSyncJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public array $backoff = [10, 30];

    private const FETCH_LIMIT = 500;

    /**
     * Dispatch a Bus Batch containing all Worker Jobs for Ref Nasional sync.
     * Returns the Batch ID via a callback stored in cache for the controller to retrieve.
     */
    public function handle(): void
    {
        Log::info('SYNC_JOB: [Ref.Nasional] Dispatcher memulai perhitungan offset untuk Bus Batch.');

        $workerJobs = [];

        try {
            // 1. Hitung offset untuk Wilayah
            $wilayahJobs = $this->buildWilayahJobs();
            $workerJobs = array_merge($workerJobs, $wilayahJobs);

            // 2. Hitung offset untuk AllProdi + AllPT
            $allProdiJobs = $this->buildAllProdiJobs();
            $workerJobs = array_merge($workerJobs, $allProdiJobs);
        } catch (\Exception $e) {
            Log::error('SYNC_ERROR: [Ref.Nasional] Dispatcher gagal menghitung offset.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        if (empty($workerJobs)) {
            Log::info('SYNC_JOB: [Ref.Nasional] Tidak ada data referensi nasional untuk ditarik.');

            return;
        }

        $totalJobs = count($workerJobs);
        Log::info("SYNC_JOB: [Ref.Nasional] Mendaftarkan {$totalJobs} Worker Jobs ke Bus Batch.");

        Bus::batch($workerJobs)
            ->name('Sync RefNasional')
            ->allowFailures()
            ->then(function (Batch $batch) {
                Log::info('SYNC_SUCCESS: [Ref.Nasional] Seluruh Worker Jobs selesai.', [
                    'batch_id' => $batch->id,
                    'total_jobs' => $batch->totalJobs,
                ]);
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('SYNC_ERROR: [Ref.Nasional] Sebagian Worker Jobs gagal.', [
                    'batch_id' => $batch->id,
                    'failed_jobs' => $batch->failedJobs,
                    'message' => $e->getMessage(),
                ]);
            })
            ->finally(function (Batch $batch) {
                Log::info('SYNC_JOB: [Ref.Nasional] Batch selesai dieksekusi.', [
                    'batch_id' => $batch->id,
                    'progress' => $batch->progress(),
                ]);
            })
            ->dispatch();
    }

    /**
     * Bangun daftar Worker Job untuk Wilayah berdasarkan total data dari API.
     *
     * @return array<ProcessRefWilayahChunkJob>
     */
    private function buildWilayahJobs(): array
    {
        $wilayahService = app(WilayahRefService::class);

        // Tarik halaman pertama untuk mengetahui apakah data tersedia
        $firstPage = $wilayahService->getWilayah('', self::FETCH_LIMIT, 0);
        $firstPageCount = count($firstPage);

        if ($firstPageCount === 0) {
            Log::info('SYNC_JOB: [Ref.Nasional] Wilayah -- tidak ada data dari API.');

            return [];
        }

        $jobs = [];

        // Job untuk halaman pertama (offset 0)
        $jobs[] = new ProcessRefWilayahChunkJob(self::FETCH_LIMIT, 0);

        // Jika halaman pertama penuh, buat Job untuk offset berikutnya
        if ($firstPageCount >= self::FETCH_LIMIT) {
            $offset = self::FETCH_LIMIT;
            // Estimasi: terus buat Job sampai batas wajar, Worker yang akan berhenti jika data kosong
            // Gunakan pendekatan konservatif: max 200 halaman (100.000 data)
            $maxPages = 200;
            $page = 1;

            while ($page < $maxPages) {
                $jobs[] = new ProcessRefWilayahChunkJob(self::FETCH_LIMIT, $offset);
                $offset += self::FETCH_LIMIT;
                $page++;

                // Cek halaman berikutnya apakah masih ada data
                $nextPage = $wilayahService->getWilayah('', 1, $offset);
                if (empty($nextPage)) {
                    break;
                }
            }
        }

        Log::info('SYNC_JOB: [Ref.Nasional] Wilayah -- '.count($jobs).' Worker Jobs dipersiapkan.');

        return $jobs;
    }

    /**
     * Bangun daftar Worker Job untuk AllProdi + AllPT berdasarkan total data dari API.
     *
     * @return array<ProcessRefAllProdiChunkJob>
     */
    private function buildAllProdiJobs(): array
    {
        $feederService = app(NeoFeederService::class);

        // Tarik halaman pertama untuk mengetahui apakah data tersedia
        $firstPage = $feederService->execute('GetAllProdi', [
            'limit' => self::FETCH_LIMIT,
            'offset' => 0,
        ]);
        $firstPageCount = count($firstPage);

        if ($firstPageCount === 0) {
            Log::info('SYNC_JOB: [Ref.Nasional] AllProdi -- tidak ada data dari API.');

            return [];
        }

        $jobs = [];

        // Job untuk halaman pertama (offset 0)
        $jobs[] = new ProcessRefAllProdiChunkJob(self::FETCH_LIMIT, 0);

        // Jika halaman pertama penuh, buat Job untuk offset berikutnya
        if ($firstPageCount >= self::FETCH_LIMIT) {
            $offset = self::FETCH_LIMIT;
            $maxPages = 200;
            $page = 1;

            while ($page < $maxPages) {
                $jobs[] = new ProcessRefAllProdiChunkJob(self::FETCH_LIMIT, $offset);
                $offset += self::FETCH_LIMIT;
                $page++;

                // Cek halaman berikutnya apakah masih ada data
                $nextPage = $feederService->execute('GetAllProdi', [
                    'limit' => 1,
                    'offset' => $offset,
                ]);
                if (empty($nextPage)) {
                    break;
                }
            }
        }

        Log::info('SYNC_JOB: [Ref.Nasional] AllProdi -- '.count($jobs).' Worker Jobs dipersiapkan.');

        return $jobs;
    }

    /**
     * Dipanggil ketika Dispatcher Job gagal permanen.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('SYNC_FAILED: [Ref.Nasional] Dispatcher Job GAGAL PERMANEN.', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
