<?php

namespace App\Services;

use App\Models\Kuisioner;
use App\Models\KuisionerSubmission;
use App\Models\KuisionerJawabanDetail;
use App\Models\Mahasiswa;
use App\Models\PesertaKelasKuliah;
use App\Models\UserJabatan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KuisionerService
{
    /**
     * Ambil rekap laporan kuesioner.
     */
    public function getRekapLaporan(Kuisioner $kuisioner)
    {
        $kuisioner->load([
            'pertanyaans' => function ($q) {
                $q->where('tipe_input', 'likert')->orderBy('urutan', 'asc');
            },
            'semester'
        ]);

        $activeSemesterId = $kuisioner->id_semester;

        // 1. Hitung Target & Realisasi
        $totalMhsTarget = Mahasiswa::whereHas('riwayatPendidikans.pesertaKelasKuliahs.kelasKuliah', function ($q) use ($activeSemesterId) {
            $q->where('id_semester', $activeSemesterId);
        })->count();

        $totalMhsSudah = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
            ->distinct('id_mahasiswa')
            ->count('id_mahasiswa');

        if ($kuisioner->tipe === 'pelayanan') {
            $targetPartisipan = $totalMhsTarget;
            $totalResponden = $totalMhsSudah;
        } elseif ($kuisioner->tipe === 'ami') {
            $userPunyaJabatan = UserJabatan::where('is_active', true)->pluck('user_id')->toArray();
            $admins = User::role('admin')->pluck('id')->toArray();
            $allTargetUserIds = array_unique(array_merge($userPunyaJabatan, $admins));
            $targetPartisipan = count($allTargetUserIds);
            $totalResponden = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->whereIn('id_user', $allTargetUserIds)
                ->distinct('id_user')
                ->count('id_user');
        } else {
            $targetPartisipan = PesertaKelasKuliah::whereHas('kelasKuliah', function ($q) use ($activeSemesterId) {
                $q->where('id_semester', $activeSemesterId);
            })->count();
            $totalResponden = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->whereNotNull('id_kelas_kuliah')
                ->count();
        }

        $coverage = $targetPartisipan > 0 ? round(($totalResponden / $targetPartisipan) * 100, 1) : 0;
        $totalMhsBelum = max(0, $totalMhsTarget - $totalMhsSudah);

        // 2. Rekap Pertanyaan (Likert)
        $rekapPertanyaan = [];
        $totalAverageSemua = 0;
        foreach ($kuisioner->pertanyaans as $p) {
            $avgScore = KuisionerJawabanDetail::where('id_pertanyaan', $p->id)->avg('jawaban_skala') ?? 0;
            $rekapPertanyaan[] = [
                'teks' => $p->teks_pertanyaan,
                'avg' => round($avgScore, 2),
                'label' => $this->getKesimpulanSkor($avgScore)
            ];
            $totalAverageSemua += $avgScore;
        }

        $countPertanyaan = $kuisioner->pertanyaans->count();
        $grandAverage = $countPertanyaan > 0 ? round($totalAverageSemua / $countPertanyaan, 2) : 0;
        $grandKesimpulan = $this->getKesimpulanSkor($grandAverage);

        // 3. Rekap Dosen
        $rekapDosen = [];
        if ($kuisioner->tipe === 'dosen') {
            $rekapDosen = KuisionerSubmission::where('kuisioner_submissions.id_kuisioner', $kuisioner->id)
                ->join('kuisioner_jawaban_details', 'kuisioner_submissions.id', '=', 'kuisioner_jawaban_details.id_submission')
                ->join('dosens', 'kuisioner_submissions.id_dosen', '=', 'dosens.id')
                ->join('kuisioner_pertanyaans', 'kuisioner_jawaban_details.id_pertanyaan', '=', 'kuisioner_pertanyaans.id')
                ->where('kuisioner_pertanyaans.tipe_input', 'likert')
                ->select('dosens.nama', 'dosens.nidn', DB::raw('AVG(kuisioner_jawaban_details.jawaban_skala) as avg_score'))
                ->groupBy('dosens.id', 'dosens.nama', 'dosens.nidn')
                ->orderBy('avg_score', 'desc')
                ->get()
                ->map(function ($item) {
                    $item->kesimpulan = $this->getKesimpulanSkor($item->avg_score);
                    return $item;
                });
        }

        return [
            'totalResponden' => $totalResponden,
            'targetPartisipan' => $targetPartisipan,
            'coverage' => $coverage,
            'totalMhsSudah' => $totalMhsSudah,
            'totalMhsBelum' => $totalMhsBelum,
            'totalMhsTarget' => $totalMhsTarget,
            'rekapPertanyaan' => $rekapPertanyaan,
            'grandAverage' => $grandAverage,
            'grandKesimpulan' => $grandKesimpulan,
            'rekapDosen' => $rekapDosen
        ];
    }

    /**
     * Konversi Rata-rata 1-5 menjadi Kategori Kualitatif
     */
    public function getKesimpulanSkor($avg)
    {
        if ($avg >= 4.21)
            return ['teks' => 'Sangat Memuaskan', 'color' => 'success'];
        if ($avg >= 3.41)
            return ['teks' => 'Memuaskan', 'color' => 'primary'];
        if ($avg >= 2.61)
            return ['teks' => 'Cukup', 'color' => 'info'];
        if ($avg >= 1.81)
            return ['teks' => 'Kurang', 'color' => 'warning'];
        if ($avg > 0)
            return ['teks' => 'Sangat Kurang', 'color' => 'danger'];
        return ['teks' => 'Belum Ada Data', 'color' => 'secondary'];
    }
}
