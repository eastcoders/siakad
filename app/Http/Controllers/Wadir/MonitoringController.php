<?php

namespace App\Http\Controllers\Wadir;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\KelasKuliah;
use App\Models\PresensiPertemuan;
use App\Models\PesertaKelasKuliah;
use App\Services\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index(Request $request)
    {
        Log::info("VIEW_MONITORING: Wakil Direktur mengakses dashboard monitoring perkuliahan");

        $semesterId = $request->get('semester_id', getActiveSemesterId());
        $search = $request->get('search');

        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        $stats = $this->monitoringService->getGlobalStats($semesterId);

        $kelasKuliahs = $this->monitoringService->getKelasKuliahQuery($semesterId, $search)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $this->monitoringService->formatMonitoringCollection($kelasKuliahs->getCollection());

        return view('wadir.monitoring.index', compact(
            'kelasKuliahs',
            'semesters',
            'semesterId',
            'stats',
            'search'
        ));
    }

    public function show($id)
    {
        Log::info("VIEW_MONITORING: Wakil Direktur melihat detail kelas", ['id' => $id]);

        $kelas = KelasKuliah::with([
            'mataKuliah',
            'dosenPengajars.dosen',
            'dosenPengajars.dosenAliasLokal',
            'programStudi',
            'semester'
        ])
            ->withCount('presensiPertemuans')
            ->where('id_kelas_kuliah', $id)
            ->firstOrFail();

        $jurnal = PresensiPertemuan::with('dosen')
            ->where('id_kelas_kuliah', $kelas->id_kelas_kuliah)
            ->orderBy('pertemuan_ke', 'asc')
            ->get();

        $peserta = PesertaKelasKuliah::with(['riwayatPendidikan.mahasiswa'])
            ->where('id_kelas_kuliah', $id)
            ->where('status_krs', 'acc')
            ->get();

        $rekapAbsensi = $peserta->map(function ($p) use ($jurnal) {
            $hadirCount = $p->presensiMahasiswas()->where('status', 'H')->count();
            $totalPertemuan = $jurnal->count();
            return [
                'nama' => $p->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? '-',
                'nim' => $p->riwayatPendidikan->mahasiswa->nim ?? '-',
                'hadir' => $hadirCount,
                'total' => $totalPertemuan,
                'percent' => $totalPertemuan > 0 ? round(($hadirCount / $totalPertemuan) * 100, 1) : 0
            ];
        });

        return view('wadir.monitoring.show', compact('kelas', 'jurnal', 'rekapAbsensi'));
    }
}
