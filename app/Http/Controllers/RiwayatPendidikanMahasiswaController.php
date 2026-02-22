<?php

namespace App\Http\Controllers;

use App\Models\RiwayatPendidikan;
use App\Models\Mahasiswa;
use App\Http\Requests\StoreRiwayatPendidikanMahasiswaRequest;
use App\Http\Requests\UpdateRiwayatPendidikanMahasiswaRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\Feeder\Reference\ReferenceDataSyncService;

class RiwayatPendidikanMahasiswaController extends Controller
{
    public function __construct(
        protected ReferenceDataSyncService $refSyncService
    ) {
    }

    /**
     * Get prodi by PT (AJAX).
     */
    public function getProdiByPt(string $id_perguruan_tinggi)
    {
        $prodis = $this->refSyncService->getProdiByPt($id_perguruan_tinggi);

        return response()->json([
            'success' => true,
            'data' => $prodis,
        ]);
    }
    /**
     * Store a newly created riwayat pendidikan.
     */
    public function store(StoreRiwayatPendidikanMahasiswaRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                RiwayatPendidikan::create($request->validated());
            });

            return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan riwayat pendidikan: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan riwayat pendidikan: ' . $e->getMessage());
        }
    }

    /**
     * Return riwayat pendidikan data as JSON for modal edit.
     */
    public function edit(RiwayatPendidikan $riwayat_pendidikan)
    {
        return response()->json([
            'success' => true,
            'data' => $riwayat_pendidikan,
        ]);
    }

    /**
     * Update the specified riwayat pendidikan.
     */
    public function update(UpdateRiwayatPendidikanMahasiswaRequest $request, RiwayatPendidikan $riwayat_pendidikan)
    {
        try {
            DB::transaction(function () use ($request, $riwayat_pendidikan) {
                $riwayat_pendidikan->update($request->validated());
            });

            return back()->with('success', 'Riwayat pendidikan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui riwayat pendidikan: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui riwayat pendidikan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified riwayat pendidikan.
     */
    public function destroy(RiwayatPendidikan $riwayat_pendidikan)
    {
        try {
            if ($riwayat_pendidikan->sumber_data === 'server') {
                $riwayat_pendidikan->update([
                    'is_deleted_local' => true,
                    'status_sinkronisasi' => 'deleted_local',
                    'sync_action' => 'delete',
                    'is_local_change' => true,
                    'sync_error_message' => null,
                ]);
            } else {
                $hasEverSynced = $riwayat_pendidikan->last_push_at !== null
                    || in_array($riwayat_pendidikan->status_sinkronisasi, [
                        'synced',
                        'updated_local',
                        'deleted_local',
                        'push_success',
                    ], true);

                if (!$hasEverSynced) {
                    $riwayat_pendidikan->delete();
                } else {
                    $riwayat_pendidikan->update([
                        'is_deleted_local' => true,
                        'status_sinkronisasi' => 'deleted_local',
                        'sync_action' => 'delete',
                        'is_local_change' => true,
                        'sync_error_message' => null,
                    ]);
                }
            }

            return back()->with('success', 'Riwayat pendidikan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus riwayat pendidikan: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus riwayat pendidikan: ' . $e->getMessage());
        }
    }
}
