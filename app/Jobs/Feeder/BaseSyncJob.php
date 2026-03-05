<?php

namespace App\Jobs\Feeder;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

abstract class BaseSyncJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang sebelum Job dianggap gagal permanen.
     */
    public int $tries = 3;

    /**
     * Waktu tunggu antar percobaan (dalam detik), exponential backoff.
     * Percobaan 1: tunggu 5 detik, Percobaan 2: 15 detik, Percobaan 3: 30 detik.
     */
    public array $backoff = [5, 15, 30];

    /**
     * Batas waktu eksekusi per Job (dalam detik).
     * Dosen sync butuh waktu lebih lama karena banyak sub-request API.
     */
    public int $timeout = 300;

    protected array $data;
    protected string $entityName;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->entityName = $this->getEntityName();
    }

    /**
     * Get the descriptive name of the entity for logging.
     */
    abstract protected function getEntityName(): string;

    /**
     * Logic to sync a single row.
     */
    abstract protected function syncRow(array $row): void;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $attempt = $this->attempts();
        Log::info("SYNC_JOB: [{$this->entityName}] Memulai eksekusi (percobaan ke-{$attempt})", [
            'chunk_size' => count($this->data),
        ]);

        foreach ($this->data as $item) {
            try {
                $this->syncRow($item);
            } catch (Exception $e) {
                // Jika error koneksi (cURL), lemparkan ulang agar mekanisme retry bekerja
                if ($this->isConnectionError($e)) {
                    Log::warning("SYNC_RETRY: [{$this->entityName}] Koneksi gagal, akan dicoba ulang (percobaan ke-{$attempt}/{$this->tries})", [
                        'error' => $e->getMessage(),
                    ]);
                    throw $e; // Re-throw agar Laravel retry mekanisme berjalan
                }

                // Error non-koneksi (misalnya data tidak valid), catat dan lanjutkan
                Log::error("SYNC_ERROR: [{$this->entityName}] Gagal sinkronisasi data", [
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Cek apakah exception merupakan error koneksi (cURL / timeout).
     */
    protected function isConnectionError(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        $connectionKeywords = [
            'curl error',
            'connection reset',
            'connection refused',
            'connection timed out',
            'recv failure',
            'could not resolve host',
            'ssl connection',
            'operation timed out',
            'network is unreachable',
        ];

        foreach ($connectionKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Dipanggil ketika Job telah melewati semua percobaan dan tetap gagal.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("SYNC_FAILED: [{$this->entityName}] Job GAGAL PERMANEN setelah {$this->tries}x percobaan", [
            'error' => $exception->getMessage(),
            'chunk_size' => count($this->data),
        ]);
    }
}
