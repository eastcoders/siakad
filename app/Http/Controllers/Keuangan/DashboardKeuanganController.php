<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Semester;
use App\Services\KeuanganStatisticService;
use Illuminate\Http\Request;

class DashboardKeuanganController extends Controller
{
    protected KeuanganStatisticService $statService;

    public function __construct(KeuanganStatisticService $statService)
    {
        $this->statService = $statService;
    }

    public function index()
    {
        $semesterAktif = Semester::where('a_periode_aktif', 1)->first();
        $idSemester = $semesterAktif ? $semesterAktif->id_semester : null;

        $antreanCount = $this->statService->getAntreanVerifikasi();

        if ($idSemester) {
            $realisasi = $this->statService->getRealisasiPembayaran($idSemester);
            $totalPiutang = $this->statService->getTotalPiutang($idSemester);
            $totalPendapatan = $this->statService->getTotalPendapatan($idSemester);
            $mahasiswaTerblokir = $this->statService->getMahasiswaTerblokir($idSemester);
        } else {
            $realisasi = ['total_target' => 0, 'total_dibayar' => 0, 'persentase' => 0];
            $totalPiutang = 0;
            $totalPendapatan = 0;
            $mahasiswaTerblokir = collect();
        }

        // Ambil 5 pembayaran terbaru yg sudah disetujui (atau pending, untuk overview)
        $pembayaranTerbaru = Pembayaran::with(['tagihan.mahasiswa'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('keuangan.dashboard', compact(
            'semesterAktif',
            'antreanCount',
            'realisasi',
            'totalPiutang',
            'totalPendapatan',
            'pembayaranTerbaru',
            'mahasiswaTerblokir'
        ));
    }

    public function chartData()
    {
        $semesterAktif = Semester::where('a_periode_aktif', 1)->first();
        if (!$semesterAktif) {
            return response()->json(['labels' => [], 'data' => []]);
        }

        $data = $this->statService->getTransaksiMingguan($semesterAktif->id_semester);
        return response()->json($data);
    }
}
