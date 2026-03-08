<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class MonitoringPerkuliahanController extends Controller
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Tampilkan monitoring progres perkuliahan.
     */
    public function index(Request $request)
    {
        $semesterId = $request->get('semester_id', getActiveSemesterId());
        $search = $request->get('search');

        // Master Semester untuk filter
        $semesters = Semester::orderBy('id_semester', 'desc')->get();

        // Statistik Widget (Selalu dihitung dari seluruh data semester ini)
        $stats = $this->monitoringService->getGlobalStats($semesterId);

        // Query Utama dengan Pencarian dan Paginasi
        $kelasKuliahs = $this->monitoringService->getKelasKuliahQuery($semesterId, $search)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Tambahkan atribut visual ke setiap item yang ter-paginasi
        $this->monitoringService->formatMonitoringCollection($kelasKuliahs->getCollection());

        return view('admin.monitoring.perkuliahan.index', compact('kelasKuliahs', 'semesters', 'semesterId', 'stats', 'search'));
    }
}
