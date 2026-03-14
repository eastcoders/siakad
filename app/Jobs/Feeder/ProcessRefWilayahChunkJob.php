<?php

namespace App\Jobs\Feeder;

use App\Services\ReferensiServices\WilayahRefService;
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

class ProcessRefWilayahChunkJob implements ShouldQueue
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
        Log::info("SYNC_PULL: [Ref.Nasional] Wilayah Worker mulai -- offset={$this->offset}, limit={$this->limit} (percobaan ke-{$attempt})");

        try {
            $wilayahService = app(WilayahRefService::class);
            $data = $wilayahService->getWilayah('', $this->limit, $this->offset);
            $recordCount = count($data);

            if ($recordCount === 0) {
                Log::info("SYNC_PULL: [Ref.Nasional] Wilayah Worker offset={$this->offset} -- tidak ada data, skip.");

                return;
            }

            $timestamp = now();
            $upsertData = [];

            foreach ($data as $item) {
                $upsertData[] = [
                    'id_wilayah' => $item['id_wilayah'],
                    'nama_wilayah' => $item['nama_wilayah'],
                    'id_level_wilayah' => $item['id_level_wilayah'],
                    'id_induk_wilayah' => ! empty($item['id_induk_wilayah']) ? $item['id_induk_wilayah'] : null,
                    'id_negara' => ! empty($item['id_negara']) ? $item['id_negara'] : null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            // Pecah ke chunk kecil untuk upsert agar tidak melampaui max_allowed_packet
            $chunks = array_chunk($upsertData, self::UPSERT_CHUNK_SIZE);

            foreach ($chunks as $chunk) {
                DB::table('ref_wilayah')->upsert(
                    $chunk,
                    ['id_wilayah'],
                    ['nama_wilayah', 'id_level_wilayah', 'id_induk_wilayah', 'id_negara', 'updated_at']
                );
            }

            // Bersihkan memori
            unset($data, $upsertData, $chunks);
            gc_collect_cycles();

            Log::info("SYNC_PULL: [Ref.Nasional] Wilayah Worker offset={$this->offset} selesai -- {$recordCount} records diproses.");
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: [Ref.Nasional] Wilayah Worker GAGAL pada offset={$this->offset}", [
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
        Log::error("SYNC_FAILED: [Ref.Nasional] Wilayah Worker GAGAL PERMANEN pada offset={$this->offset}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
