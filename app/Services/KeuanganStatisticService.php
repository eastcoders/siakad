<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KeuanganStatisticService
{
    /**
     * Get jumlah antrean verifikasi bukti pembayaran
     */
    public function getAntreanVerifikasi(): int
    {
        return Pembayaran::pending()->count();
    }

    /**
     * Get total target tagihan, dibayar, dan persentasenya
     */
    public function getRealisasiPembayaran(string $idSemester): array
    {
        // Total tagihan yang harus dibayar (tagihan - potongan)
        $tagihans = Tagihan::where('id_semester', $idSemester)->get();
        $totalTagihan = 0;
        $totalDibayar = 0;

        foreach ($tagihans as $tagihan) {
            $totalTagihan += max(0, $tagihan->total_tagihan - $tagihan->total_potongan);
            $totalDibayar += $tagihan->total_dibayar;
        }

        $persentase = $totalTagihan > 0 ? round(($totalDibayar / $totalTagihan) * 100, 2) : 0;

        return [
            'total_target' => $totalTagihan,
            'total_dibayar' => $totalDibayar,
            'persentase' => $persentase,
        ];
    }

    /**
     * Get total nominal piutang yg belum dibayar
     */
    public function getTotalPiutang(string $idSemester): float
    {
        $tagihans = Tagihan::belumLunas()->where('id_semester', $idSemester)->get();
        $piutang = 0;

        foreach ($tagihans as $tagihan) {
            $piutang += $tagihan->sisa_tagihan;
        }

        return $piutang;
    }

    /**
     * Get total pendapatan rill yg sudah disetujui
     */
    public function getTotalPendapatan(string $idSemester): float
    {
        return Pembayaran::disetujui()
            ->whereHas('tagihan', function ($q) use ($idSemester) {
                $q->where('id_semester', $idSemester);
            })
            ->sum('jumlah_bayar');
    }

    /**
     * Get trend transaksi pembayaran yang masuk (approved/pending) per minggu selama 4 minggu ke belakang
     */
    public function getTransaksiMingguan(string $idSemester): array
    {
        $labels = [];
        $data = [];

        // 4 minggu ke belakang
        for ($i = 3; $i >= 0; $i--) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = Carbon::now()->subWeeks($i)->endOfWeek();

            $sum = Pembayaran::disetujui()
                ->whereHas('tagihan', function ($q) use ($idSemester) {
                    $q->where('id_semester', $idSemester);
                })
                ->whereBetween('tanggal_bayar', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->sum('jumlah_bayar');

            $labels[] = "Mgg " . count($labels) + 1 . " (" . $start->format('d/m') . ")";
            $data[] = $sum;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get mahasiswa yang terblokir akses KRS / Ujian
     */
    public function getMahasiswaTerblokir(string $idSemester, int $limit = 5)
    {
        // Cari tagihan yg belum lunas saja (yg punya potensi tagihan wajib KRS/Ujian)
        $tagihans = Tagihan::with(['mahasiswa', 'items.komponenBiaya'])
            ->belumLunas()
            ->where('id_semester', $idSemester)
            ->get();

        $terblokir = collect();

        foreach ($tagihans as $tagihan) {
            $wajibKrs = 0;
            $wajibUjian = 0;

            foreach ($tagihan->items as $item) {
                if ($item->komponenBiaya->is_wajib_krs) {
                    $wajibKrs += ($item->nominal - $item->potongan);
                }
                if ($item->komponenBiaya->is_wajib_ujian) {
                    $wajibUjian += ($item->nominal - $item->potongan);
                }
            }

            $isBlokirKrs = $wajibKrs > 0 && $tagihan->total_dibayar < $wajibKrs;
            $isBlokirUjian = $wajibUjian > 0 && $tagihan->total_dibayar < $wajibUjian;

            if ($isBlokirKrs || $isBlokirUjian) {
                $statusType = [];
                if ($isBlokirKrs)
                    $statusType[] = 'KRS';
                if ($isBlokirUjian)
                    $statusType[] = 'Ujian';

                $terblokir->push([
                    'mahasiswa' => $tagihan->mahasiswa,
                    'tagihan' => $tagihan,
                    'status_blokir' => implode(' & ', $statusType)
                ]);
            }
        }

        return $terblokir->take($limit);
    }
}
