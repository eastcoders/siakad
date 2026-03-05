<?php

namespace App\Jobs\Feeder;

use App\Models\AlatTransportasi;
use App\Models\JenisTinggal;
use App\Models\Pekerjaan;
use App\Models\Penghasilan;
use App\Models\JenjangPendidikan;
use App\Models\Agama;
use App\Models\Negara;
use App\Services\AkademikServices\AkademikRefService;
use App\Services\ReferensiServices\AdministratifRefService;
use App\Services\ReferensiServices\PribadiRefService;
use App\Services\ReferensiServices\WilayahRefService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PullRefBiodataJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];
    public int $timeout = 120;

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        $attempt = $this->attempts();
        Log::info("SYNC_JOB: [Ref.Biodata] Memulai sinkronisasi referensi biodata (percobaan ke-{$attempt})");

        try {
            DB::beginTransaction();

            $adminService = app(AdministratifRefService::class);
            $pribadiService = app(PribadiRefService::class);
            $akademikService = app(AkademikRefService::class);
            $wilayahService = app(WilayahRefService::class);

            // 1. Jenis Tinggal
            $this->syncSimple('Jenis Tinggal', fn() => $adminService->getJenisTinggal(), JenisTinggal::class, ['id_jenis_tinggal', 'nama_jenis_tinggal']);

            // 2. Alat Transportasi
            $this->syncSimple('Alat Transportasi', fn() => $adminService->getAlatTransportasi(), AlatTransportasi::class, ['id_alat_transportasi', 'nama_alat_transportasi']);

            // 3. Pekerjaan
            $this->syncSimple('Pekerjaan', fn() => $pribadiService->getPekerjaan('', 500, 0), Pekerjaan::class, ['id_pekerjaan', 'nama_pekerjaan']);

            // 4. Penghasilan
            $this->syncSimple('Penghasilan', fn() => $pribadiService->getPenghasilan(), Penghasilan::class, ['id_penghasilan', 'nama_penghasilan']);

            // 5. Jenjang Pendidikan
            $this->syncSimple('Jenjang Pendidikan', fn() => $akademikService->getJenjangPendidikan('', 500, 0), JenjangPendidikan::class, ['id_jenjang_didik', 'nama_jenjang_didik']);

            // 6. Agama
            $this->syncSimple('Agama', fn() => $pribadiService->getAgama(), Agama::class, ['id_agama', 'nama_agama']);

            // 7. Negara
            $this->syncSimple('Negara', fn() => $wilayahService->getNegara(), Negara::class, ['id_negara', 'nama_negara']);

            DB::commit();
            Log::info("SYNC_SUCCESS: [Ref.Biodata] Sinkronisasi referensi biodata selesai.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYNC_ERROR: [Ref.Biodata] Gagal sinkronisasi", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function syncSimple(string $name, callable $fetcher, string $modelClass, array $mapping): void
    {
        Log::info("SYNC_PULL: [Ref.Biodata] Menarik data {$name}...");
        $data = $fetcher();
        $count = count($data);

        if ($count === 0) {
            Log::warning("SYNC_PULL: [Ref.Biodata] Tidak ada data untuk {$name}.");
            return;
        }

        $idKey = $mapping[0];
        $nameKey = $mapping[1];
        $timestamp = now();

        $upsertData = [];
        foreach ($data as $item) {
            if (!isset($item[$idKey]))
                continue;
            $upsertData[] = [
                $idKey => $item[$idKey],
                $nameKey => $item[$nameKey],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        if (!empty($upsertData)) {
            $modelClass::upsert($upsertData, [$idKey], [$nameKey, 'updated_at']);
        }

        Log::info("SYNC_PULL: [Ref.Biodata] {$name} selesai: {$count} records.");
    }

    public function failed(Throwable $exception): void
    {
        Log::error("SYNC_FAILED: [Ref.Biodata] Job GAGAL PERMANEN setelah {$this->tries}x percobaan", [
            'error' => $exception->getMessage(),
        ]);
    }
}
