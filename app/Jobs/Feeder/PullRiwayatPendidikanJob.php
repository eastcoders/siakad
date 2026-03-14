<?php

namespace App\Jobs\Feeder;

use App\Models\Mahasiswa;
use App\Models\RiwayatPendidikan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PullRiwayatPendidikanJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Riwayat Pendidikan';
    }

    protected function syncRow(array $row): void
    {
        // Resolve local Mahasiswa ID (bigint) from Feeder UUID
        $mahasiswaId = Mahasiswa::where('id_feeder', $row['id_mahasiswa'])->value('id');

        if (! $mahasiswaId) {
            Log::warning("SYNC_WARNING: [Riwayat Pendidikan] Mahasiswa dengan ID Feeder {$row['id_mahasiswa']} tidak ditemukan di lokal. Baris dilewati.");

            return;
        }

        RiwayatPendidikan::updateOrCreate(
            ['id_feeder' => $row['id_registrasi_mahasiswa']],
            [
                'id_mahasiswa' => $mahasiswaId,
                'id_jenis_daftar' => $row['id_jenis_daftar'],
                'id_jalur_daftar' => $row['id_jalur_daftar'],
                'id_periode_masuk' => $row['id_periode_masuk'],
                'id_prodi' => $row['id_prodi'],
                'id_perguruan_tinggi' => $row['id_perguruan_tinggi'] ?? null,
                'id_perguruan_tinggi_asal' => $row['id_perguruan_tinggi_asal'] ?? null,
                'id_prodi_asal' => $row['id_prodi_asal'] ?? null,
                'id_pembiayaan' => $row['id_pembiayaan'] ?? null,
                'id_bidang_minat' => $row['id_bidang_minat'] ?? null,
                'sks_diakui' => $row['sks_diakui'] ?? null,
                'nim' => $row['nim'],
                'tanggal_daftar' => $row['tanggal_daftar'],
                'id_jenis_keluar' => $row['id_jenis_keluar'] ?? null,
                'tanggal_keluar' => $row['tanggal_keluar'] ?? null,
                'keterangan_keluar' => $row['keterangan_keluar'] ?? null,
                'biaya_masuk' => $row['biaya_masuk'] ?? null,
                'status_sinkronisasi' => 'synced',
                'is_synced' => true,
                'last_synced_at' => Carbon::now(),
                'sumber_data' => 'server',
            ]
        );
    }
}
