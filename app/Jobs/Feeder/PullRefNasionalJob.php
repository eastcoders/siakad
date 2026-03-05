<?php

namespace App\Jobs\Feeder;

use App\Models\ReferenceWilayah;
use App\Services\ReferensiServices\WilayahRefService;
use App\Services\Feeder\Reference\ReferenceDataSyncService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PullRefNasionalJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 600; // 10 menit — data nasional sangat besar

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $attempt = $this->attempts();
        Log::info("SYNC_JOB: [Ref.Nasional] Memulai sinkronisasi referensi nasional (percobaan ke-{$attempt})");

        // 1. Wilayah (paginated)
        $this->syncWilayah();

        // 2. AllProdi + AllPT Nasional (paginated internal via ReferenceDataSyncService)
        $this->syncAllProdiPt();

        Log::info("SYNC_SUCCESS: [Ref.Nasional] Sinkronisasi referensi nasional selesai.");
    }

    private function syncWilayah(): void
    {
        Log::info("SYNC_PULL: [Ref.Nasional] Menarik data Wilayah...");

        try {
            $wilayahService = app(WilayahRefService::class);
            $limit = 500;
            $offset = 0;
            $totalSynced = 0;

            do {
                $data = $wilayahService->getWilayah('', $limit, $offset);
                $count = count($data);

                if ($count === 0)
                    break;

                $timestamp = now();
                $upsertData = [];

                foreach ($data as $item) {
                    $upsertData[] = [
                        'id_wilayah' => $item['id_wilayah'],
                        'nama_wilayah' => $item['nama_wilayah'],
                        'id_level_wilayah' => $item['id_level_wilayah'],
                        'id_induk_wilayah' => !empty($item['id_induk_wilayah']) ? $item['id_induk_wilayah'] : null,
                        'id_negara' => !empty($item['id_negara']) ? $item['id_negara'] : null,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                ReferenceWilayah::upsert($upsertData, ['id_wilayah'], ['nama_wilayah', 'id_level_wilayah', 'id_induk_wilayah', 'id_negara', 'updated_at']);

                $totalSynced += $count;
                $offset += $limit;

                Log::debug("SYNC_PULL: [Ref.Nasional] Wilayah batch offset={$offset}, total={$totalSynced}");

            } while ($count >= $limit);

            Log::info("SYNC_PULL: [Ref.Nasional] Wilayah selesai: {$totalSynced} records.");
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: [Ref.Nasional] Gagal sync Wilayah: " . $e->getMessage());
            throw $e;
        }
    }

    private function syncAllProdiPt(): void
    {
        Log::info("SYNC_PULL: [Ref.Nasional] Menarik data AllProdi + AllPT Nasional...");

        try {
            $syncService = app(ReferenceDataSyncService::class);
            $syncService->sync();
            Log::info("SYNC_PULL: [Ref.Nasional] AllProdi + AllPT selesai.");
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: [Ref.Nasional] Gagal sync AllProdi/AllPT: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("SYNC_FAILED: [Ref.Nasional] Job GAGAL PERMANEN setelah {$this->tries}x percobaan", [
            'error' => $exception->getMessage(),
        ]);
    }
}
