<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\KomponenBiaya;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Exports\LaporanKeuanganExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanKeuanganController extends Controller
{
    public function index()
    {
        $prodis = ProgramStudi::all();
        $komponens = KomponenBiaya::all();

        return view('keuangan.laporan.index', compact('prodis', 'komponens'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'id_prodi' => 'nullable|uuid',
            'komponen_biaya_id' => 'nullable|exists:komponen_biayas,id',
        ]);

        $fileName = 'Laporan-Keuangan-' . date('Ymd-His') . '.xlsx';

        return Excel::download(
            new LaporanKeuanganExport(
                $request->start_date,
                $request->end_date,
                $request->id_prodi,
                $request->komponen_biaya_id
            ),
            $fileName
        );
    }
}
