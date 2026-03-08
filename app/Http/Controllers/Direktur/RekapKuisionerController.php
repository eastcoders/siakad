<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Kuisioner;
use App\Models\Semester;
use App\Services\KuisionerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RekapKuisionerController extends Controller
{
    protected $kuisionerService;

    public function __construct(KuisionerService $kuisionerService)
    {
        $this->kuisionerService = $kuisionerService;
    }

    public function index(Request $request)
    {
        Log::info("VIEW_KUISIONER: Direktur mengakses daftar rekap kuisioner");

        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        $idSemester = $request->query('id_semester', getActiveSemesterId());

        $kuisioners = Kuisioner::with('semester')
            ->where('id_semester', $idSemester)
            ->where('status', '!=', 'draft') // Direktur hanya melihat yang sudah publish/closed
            ->latest()
            ->get();

        return view('direktur.rekap-kuisioner.index', compact('kuisioners', 'semesters', 'idSemester'));
    }

    public function show(Kuisioner $kuisioner)
    {
        Log::info("VIEW_KUISIONER: Direktur melihat detail rekap kuisioner", ['id' => $kuisioner->id]);

        $data = $this->kuisionerService->getRekapLaporan($kuisioner);

        // Sample Esai (5 terbaru)
        $esaiTerbaru = \App\Models\KuisionerJawabanDetail::whereHas('pertanyaan', function ($q) use ($kuisioner) {
            $q->where('id_kuisioner', $kuisioner->id)->where('tipe_input', 'esai');
        })
            ->whereNotNull('jawaban_teks')
            ->latest()
            ->limit(5)
            ->get();

        return view('direktur.rekap-kuisioner.show', array_merge($data, [
            'kuisioner' => $kuisioner,
            'esaiTerbaru' => $esaiTerbaru
        ]));
    }
}
