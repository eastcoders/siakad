<?php

namespace App\Jobs\Feeder;

use App\Services\NeoFeederService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessRefAllProdiChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public array $backoff = [10, 30, 60];

    /**
     * Jumlah record per upsert batch ke database.
     */
    private const UPSERT_CHUNK_SIZE = 250;

    public function __construct(
        public int $limit,
        public int $offset,
    ) {}

    /**
     * Middleware untuk melewati Job jika Batch sudah dibatalkan.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled];
    }

    public function handle(): void
    {
        // Proteksi memori: matikan query log
        DB::disableQueryLog();

        $attempt = $this->attempts();
        Log::info("SYNC_PULL: [Ref.Nasional] AllProdi Worker mulai -- offset={$this->offset}, limit={$this->limit} (percobaan ke-{$attempt})");

        try {
            $feederService = app(NeoFeederService::class);
            $data = $feederService->execute('GetAllProdi', [
                'limit' => $this->limit,
                'offset' => $this->offset,
            ]);

            $recordCount = count($data);

            if ($recordCount === 0) {
                Log::info("SYNC_PULL: [Ref.Nasional] AllProdi Worker offset={$this->offset} -- tidak ada data, skip.");

                return;
            }

            $timestamp = now();

            // 1. Kumpulkan data PT unik (upsert PT terlebih dahulu karena foreign key constraint)
            $ptData = [];
            $seenPtIds = [];

            foreach ($data as $item) {
                if (! empty($item['id_perguruan_tinggi']) && ! isset($seenPtIds[$item['id_perguruan_tinggi']])) {
                    $ptData[] = [
                        'id' => $item['id_perguruan_tinggi'],
                        'kode_perguruan_tinggi' => $item['kode_perguruan_tinggi'] ?? null,
                        'nama_perguruan_tinggi' => $item['nama_perguruan_tinggi'] ?? 'Unknown PT',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                    $seenPtIds[$item['id_perguruan_tinggi']] = true;
                }
            }

            // Upsert PT dalam chunk
            if (! empty($ptData)) {
                $ptChunks = array_chunk($ptData, self::UPSERT_CHUNK_SIZE);
                foreach ($ptChunks as $chunk) {
                    DB::table('ref_perguruan_tinggis')->upsert(
                        $chunk,
                        ['id'],
                        ['kode_perguruan_tinggi', 'nama_perguruan_tinggi', 'updated_at']
                    );
                }
                unset($ptData, $ptChunks, $seenPtIds);
            }

            // 2. Kumpulkan data Prodi
            $prodiData = [];

            foreach ($data as $item) {
                if (! empty($item['id_prodi']) && ! empty($item['id_perguruan_tinggi'])) {
                    $prodiData[] = [
                        'id' => $item['id_prodi'],
                        'kode_program_studi' => $item['kode_program_studi'] ?? null,
                        'nama_program_studi' => $item['nama_program_studi'] ?? 'Unknown Prodi',
                        'status' => $item['status'] ?? null,
                        'id_jenjang_pendidikan' => $item['id_jenjang_pendidikan'] ?? null,
                        'nama_jenjang_pendidikan' => $item['nama_jenjang_pendidikan'] ?? null,
                        'id_perguruan_tinggi' => $item['id_perguruan_tinggi'],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }
            }

            // Upsert Prodi dalam chunk
            if (! empty($prodiData)) {
                $prodiChunks = array_chunk($prodiData, self::UPSERT_CHUNK_SIZE);
                foreach ($prodiChunks as $chunk) {
                    DB::table('ref_prodis')->upsert(
                        $chunk,
                        ['id'],
                        ['kode_program_studi', 'nama_program_studi', 'status', 'id_jenjang_pendidikan', 'nama_jenjang_pendidikan', 'updated_at']
                    );
                }
                unset($prodiData, $prodiChunks);
            }

            // Bersihkan memori
            unset($data);
            gc_collect_cycles();

            Log::info("SYNC_PULL: [Ref.Nasional] AllProdi Worker offset={$this->offset} selesai -- {$recordCount} records diproses.");
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: [Ref.Nasional] AllProdi Worker GAGAL pada offset={$this->offset}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Dipanggil ketika Worker Job gagal permanen.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("SYNC_FAILED: [Ref.Nasional] AllProdi Worker GAGAL PERMANEN pada offset={$this->offset}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
