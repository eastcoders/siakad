<?php

namespace App\Services;

use App\Models\KelasKuliah;

class MonitoringService
{
    /**
     * Ambil statistik global pemantauan perkuliahan.
     */
    public function getGlobalStats($semesterId)
    {
        $allStatsQuery = KelasKuliah::where('id_semester', $semesterId)
            ->withCount('presensiPertemuans');

        return [
            'total_kelas' => (clone $allStatsQuery)->count(),
            'selesai' => (clone $allStatsQuery)->has('presensiPertemuans', '>=', 13)->count(),
            'tertinggal' => (clone $allStatsQuery)->has('presensiPertemuans', '<', 7)->count(),
        ];
    }

    /**
     * Ambil query dasar untuk daftar monitoring data kuliah.
     */
    public function getKelasKuliahQuery($semesterId, $search = null, $prodiIds = [])
    {
        return KelasKuliah::where('id_semester', $semesterId)
            ->withCount('presensiPertemuans')
            ->with(['mataKuliah', 'programStudi', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal'])
            ->when(!empty($prodiIds), function ($query) use ($prodiIds) {
                $query->milikProdi($prodiIds);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('mataKuliah', function ($mq) use ($search) {
                        $mq->where('nama_mk', 'like', "%{$search}%")
                            ->orWhere('kode_mk', 'like', "%{$search}%");
                    })
                        ->orWhereHas('dosenPengajars.dosen', function ($dq) use ($search) {
                            $dq->where('nama', 'like', "%{$search}%");
                        })
                        ->orWhereHas('programStudi', function ($pq) use ($search) {
                            $pq->where('nama_program_studi', 'like', "%{$search}%");
                        })
                        ->orWhere('nama_kelas_kuliah', 'like', "%{$search}%");
                });
            });
    }

    /**
     * Format collection dengan metadata status visual.
     */
    public function formatMonitoringCollection($collection)
    {
        return $collection->transform(function ($kelas) {
            $progres = $kelas->presensi_pertemuans_count;
            if ($progres < 7) {
                $kelas->status_warna = 'danger';
                $kelas->status_label = 'Tertinggal';
            } elseif ($progres <= 12) {
                $kelas->status_warna = 'warning';
                $kelas->status_label = 'Berjalan';
            } else {
                $kelas->status_warna = 'success';
                $kelas->status_label = 'Selesai/Mendekati';
            }
            return $kelas;
        });
    }
}
