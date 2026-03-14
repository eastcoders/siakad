<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Feeder\DispatchRefNasionalSyncJob;
use App\Jobs\Feeder\PullRefBiodataJob;
use App\Jobs\Feeder\PullRefPendidikanJob;
use App\Services\Feeder\FeederSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncManagerController extends Controller
{
    protected FeederSyncService $syncService;

    public function __construct(FeederSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Dashboard Sync Manager.
     */
    public function index()
    {
        $entities = [
            ['name' => 'Mahasiswa', 'icon' => 'users'],
            ['name' => 'RiwayatPendidikan', 'icon' => 'graduation-cap'],
            ['name' => 'MataKuliah', 'icon' => 'book'],
            ['name' => 'Kurikulum', 'icon' => 'list-alt'],
            ['name' => 'MatkulKurikulum', 'icon' => 'link'],
            ['name' => 'KelasKuliah', 'icon' => 'chalkboard-teacher'],
            ['name' => 'Dosen', 'icon' => 'user-tie'],
            ['name' => 'DosenPengajar', 'icon' => 'user-friends'],
            ['name' => 'PesertaKelas', 'icon' => 'user-graduate'],
            ['name' => 'Nilai', 'icon' => 'edit-box'],
        ];

        $refEntities = [
            ['name' => 'RefBiodata', 'icon' => 'profile', 'label' => 'Ref. Biodata', 'desc' => 'Alat Transportasi, Jenis Tinggal, Pekerjaan, Penghasilan, Jenjang Pendidikan, Agama, Negara'],
            ['name' => 'RefPendidikan', 'icon' => 'school', 'label' => 'Ref. Pendidikan', 'desc' => 'Semester, Jenis Daftar, Jalur Pendaftaran, Prodi, Profil PT'],
            ['name' => 'RefNasional', 'icon' => 'global', 'label' => 'Ref. Nasional', 'desc' => 'Wilayah, All Prodi & All PT Nasional'],
        ];

        return view('admin.sync.index', compact('entities', 'refEntities'));
    }

    /**
     * Dispatch sync for a specific entity.
     */
    public function dispatchSync(Request $request)
    {
        $request->validate([
            'entity' => 'required|string',
            'filters' => 'nullable|array',
        ]);

        $entity = $request->entity;

        try {
            // Handle referensi entities secara terpisah
            $refJobMap = [
                'RefBiodata' => PullRefBiodataJob::class,
                'RefPendidikan' => PullRefPendidikanJob::class,
                'RefNasional' => DispatchRefNasionalSyncJob::class,
            ];

            if (isset($refJobMap[$entity])) {
                return $this->dispatchRefSync($entity, $refJobMap[$entity]);
            }

            // Default: Dispatch melalui FeederSyncService (entitas transaksional)
            $batchId = $this->syncService->dispatchSync($entity, $request->filters ?? []);

            if ($batchId === 'empty') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ada data baru untuk disinkronkan.',
                    'batchId' => null,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'batchId' => $batchId,
            ]);
        } catch (\Exception $e) {
            Log::error('SYSTEM_ERROR: Gagal dispatch sync', ['entity' => $entity, 'message' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dispatch referensi sync sebagai single-job batch.
     */
    private function dispatchRefSync(string $entity, string $jobClass)
    {
        Log::info("SYNC_PULL: Mulai tarik data referensi [{$entity}]");

        $batch = Bus::batch([new $jobClass])
            ->name("Sync {$entity}")
            ->then(function ($batch) use ($entity) {
                Log::info("SYNC_SUCCESS: Sinkronisasi [{$entity}] selesai.");
            })
            ->catch(function ($batch, Throwable $e) use ($entity) {
                Log::error("SYNC_ERROR: Batch [{$entity}] gagal", ['message' => $e->getMessage()]);
            })
            ->dispatch();

        return response()->json([
            'status' => 'success',
            'batchId' => $batch->id,
        ]);
    }

    /**
     * Check batch progress.
     */
    public function checkBatch($batchId)
    {
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'progress' => $batch->progress(),
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ]);
    }
}
