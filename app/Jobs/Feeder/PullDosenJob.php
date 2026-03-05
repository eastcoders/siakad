<?php

namespace App\Jobs\Feeder;

use App\Models\Dosen;
use App\Models\DosenPenugasan;
use App\Models\DosenRiwayatFungsional;
use App\Models\DosenRiwayatPangkat;
use App\Models\DosenRiwayatPendidikan;
use App\Models\DosenRiwayatPenelitian;
use App\Models\DosenRiwayatSertifikasi;
use App\Services\Feeder\DosenFeederService;
use Illuminate\Support\Facades\Log;

class PullDosenJob extends BaseSyncJob
{
    protected function getEntityName(): string
    {
        return 'Dosen';
    }

    protected function syncRow(array $row): void
    {
        $feederService = app(DosenFeederService::class);
        $dosenData = $row;
        $namaDosen = $dosenData['nama_dosen'] ?? 'Unknown';

        try {
            // 1. Fetch Detail Biodata
            $detail = $feederService->getDetailBiodataDosen($dosenData['id_dosen']);
            $detailData = isset($detail[0]) ? $detail[0] : (!empty($detail) ? $detail : []);

            if (!empty($detailData) && ($detailData['id_dosen'] ?? '') === $dosenData['id_dosen']) {
                $dosenData['tempat_lahir'] = $detailData['tempat_lahir'] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning("Detail biodata gagal untuk {$namaDosen}: " . $e->getMessage(), [
                'id_dosen' => $dosenData['id_dosen'] ?? 'N/A'
            ]);
        }

        // 2. Sync Core Dosen Data
        $dosen = $this->createOrUpdateDosen($dosenData);

        // 3. Sync Related Data
        try {
            $this->syncPenugasan($dosen, $feederService);
        } catch (\Exception $e) {
            Log::warning("Penugasan sync gagal untuk {$dosen->nama}: " . $e->getMessage());
        }

        try {
            $this->syncRiwayat($dosen, $feederService);
        } catch (\Exception $e) {
            Log::warning("Riwayat sync gagal untuk {$dosen->nama}: " . $e->getMessage());
        }
    }

    protected function createOrUpdateDosen(array $data): Dosen
    {
        $tglLahir = $data['tanggal_lahir'] ?? null;
        if ($tglLahir) {
            try {
                $tglLahir = \Carbon\Carbon::createFromFormat('d-m-Y', $tglLahir)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $tglLahir = \Carbon\Carbon::parse($tglLahir)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $tglLahir = null;
                }
            }
        }

        return Dosen::updateOrCreate(
            ['external_id' => $data['id_dosen']],
            [
                'nidn' => $data['nidn'] ?? null,
                'nip' => $data['nip'] ?? null,
                'nama' => $data['nama_dosen'],
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $tglLahir,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'id_agama' => $data['id_agama'] ?? null,
                'id_status_aktif' => $data['id_status_aktif'] ?? null,
                'status_sinkronisasi' => 'pusat',
                'is_active' => true,
            ]
        );
    }

    protected function syncPenugasan(Dosen $dosen, DosenFeederService $feederService): void
    {
        $penugasanList = $feederService->getListPenugasanDosen($dosen->external_id);

        foreach ($penugasanList as $penugasan) {
            DosenPenugasan::updateOrCreate(
                [
                    'external_id' => $penugasan['id_registrasi_dosen'],
                ],
                [
                    'id_dosen' => $dosen->id,
                    'id_tahun_ajaran' => $penugasan['id_tahun_ajaran'],
                    'id_prodi' => $penugasan['id_prodi'],
                    'jenis_penugasan' => null,
                    'unit_penugasan' => null,
                    'tanggal_mulai' => $penugasan['tanggal_surat_tugas'],
                    'tanggal_selesai' => null,
                    'sumber_data' => 'pusat',
                ]
            );
        }
    }

    protected function syncRiwayat(Dosen $dosen, DosenFeederService $feederService): void
    {
        // 1. Fungsional
        $fungsionalList = $feederService->getRiwayatFungsionalDosen($dosen->external_id);
        foreach ($fungsionalList as $item) {
            DosenRiwayatFungsional::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'sk_nomor' => $item['sk_jabatan_fungsional'],
                ],
                [
                    'external_id' => $item['id_jabatan_fungsional'] ?? null,
                    'jabatan_fungsional' => $item['nama_jabatan_fungsional'],
                    'sk_tanggal' => null,
                    'tmt_jabatan' => $item['mulai_sk_jabatan'] ?? null,
                ]
            );
        }

        // 2. Pangkat
        $pangkatList = $feederService->getRiwayatPangkatDosen($dosen->external_id);
        foreach ($pangkatList as $item) {
            $tglSk = $item['tanggal_sk_pangkat'] ?? null;
            $tmt = $item['mulai_sk_pangkat'] ?? null;

            try {
                if ($tglSk)
                    $tglSk = \Carbon\Carbon::createFromFormat('d-m-Y', $tglSk)->format('Y-m-d');
                if ($tmt)
                    $tmt = \Carbon\Carbon::createFromFormat('d-m-Y', $tmt)->format('Y-m-d');
            } catch (\Exception $e) {
            }

            DosenRiwayatPangkat::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'sk_nomor' => $item['sk_pangkat'],
                ],
                [
                    'external_id' => $item['id_pangkat_golongan'] ?? null,
                    'pangkat_golongan' => $item['nama_pangkat_golongan'],
                    'sk_tanggal' => $tglSk,
                    'tmt_pangkat' => $tmt,
                ]
            );
        }

        // 3. Pendidikan
        $pendidikanList = $feederService->getRiwayatPendidikanDosen($dosen->external_id);
        foreach ($pendidikanList as $item) {
            DosenRiwayatPendidikan::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'jenjang_pendidikan' => $item['nama_jenjang_pendidikan'],
                    'perguruan_tinggi' => $item['nama_perguruan_tinggi'],
                    'tahun_lulus' => $item['tahun_lulus'],
                ],
                [
                    'external_id' => null,
                    'gelar_akademik' => $item['nama_gelar_akademik'] ?? null,
                    'program_studi' => $item['nama_bidang_studi'] ?? $item['nama_program_studi'] ?? null,
                    'sk_penyetaraan' => null,
                    'tanggal_ijazah' => null,
                    'nomor_ijazah' => null,
                ]
            );
        }

        // 4. Sertifikasi
        $sertifikasiList = $feederService->getRiwayatSertifikasiDosen($dosen->external_id);
        foreach ($sertifikasiList as $item) {
            DosenRiwayatSertifikasi::updateOrCreate(
                [
                    'id_dosen' => $dosen->id,
                    'nomor_sertifikasi' => $item['nomor_peserta'] ?? $item['sk_sertifikasi'],
                ],
                [
                    'external_id' => null,
                    'jenis_sertifikasi' => $item['nama_jenis_sertifikasi'],
                    'tahun_sertifikasi' => $item['tahun_sertifikasi'],
                    'bidang_studi' => $item['nama_bidang_studi'],
                ]
            );
        }

        // 5. Penelitian
        $penelitianList = $feederService->getRiwayatPenelitianDosen($dosen->external_id);
        foreach ($penelitianList as $item) {
            $tahun = $item['tahun_kegiatan'];
            if (strpos($tahun, '/') !== false) {
                $tahun = explode('/', $tahun)[0];
            }
            $tahun = (int) $tahun;

            DosenRiwayatPenelitian::updateOrCreate(
                ['external_id' => $item['id_penelitian']],
                [
                    'id_dosen' => $dosen->id,
                    'judul_penelitian' => $item['judul_penelitian'],
                    'kategori_kegiatan' => null,
                    'kelompok_bidang' => $item['nama_kelompok_bidang'] ?? null,
                    'lembaga_iptek' => $item['nama_lembaga_iptek'] ?? null,
                    'tahun_kegiatan' => $tahun,
                ]
            );
        }
    }
}
