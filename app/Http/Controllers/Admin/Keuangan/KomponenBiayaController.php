<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\KomponenBiaya;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KomponenBiayaController extends Controller
{
    public function index()
    {
        Log::info("SYNC_PULL: Mengakses daftar Komponen Biaya", ['endpoint' => route('admin.keuangan-modul.komponen-biaya.index')]);

        $komponens = KomponenBiaya::with('programStudi')->latest()->get();
        $prodis = ProgramStudi::orderBy('nama_program_studi')->get();

        return view('admin.keuangan.komponen-biaya.index', compact('komponens', 'prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_komponen' => 'required|string|max:20|unique:komponen_biayas,kode_komponen',
            'nama_komponen' => 'required|string|max:100',
            'kategori' => 'required|in:per_semester,sekali_bayar',
            'nominal_standar' => 'required|numeric|min:0',
            'is_wajib_krs' => 'nullable|boolean',
            'is_wajib_ujian' => 'nullable|boolean',
            'id_prodi' => 'nullable|exists:program_studis,id_prodi',
            'tahun_angkatan' => 'nullable|string|digits:4',
        ]);

        try {
            $data = $request->only(['kode_komponen', 'nama_komponen', 'kategori', 'nominal_standar', 'id_prodi', 'tahun_angkatan']);
            $data['is_wajib_krs'] = $request->boolean('is_wajib_krs');
            $data['is_wajib_ujian'] = $request->boolean('is_wajib_ujian');
            $data['is_active'] = true;

            $model = KomponenBiaya::create($data);

            Log::info("CRUD_CREATE: Komponen Biaya berhasil dibuat", ['id' => $model->id, 'data' => $data]);
            return back()->with('success', 'Komponen biaya berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah Komponen Biaya", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update(Request $request, KomponenBiaya $komponenBiaya)
    {
        $request->validate([
            'kode_komponen' => 'required|string|max:20|unique:komponen_biayas,kode_komponen,' . $komponenBiaya->id,
            'nama_komponen' => 'required|string|max:100',
            'kategori' => 'required|in:per_semester,sekali_bayar',
            'nominal_standar' => 'required|numeric|min:0',
            'is_wajib_krs' => 'nullable|boolean',
            'is_wajib_ujian' => 'nullable|boolean',
            'id_prodi' => 'nullable|exists:program_studis,id_prodi',
            'tahun_angkatan' => 'nullable|string|digits:4',
        ]);

        try {
            $data = $request->only(['kode_komponen', 'nama_komponen', 'kategori', 'nominal_standar', 'id_prodi', 'tahun_angkatan']);
            $data['is_wajib_krs'] = $request->boolean('is_wajib_krs');
            $data['is_wajib_ujian'] = $request->boolean('is_wajib_ujian');

            $komponenBiaya->update($data);

            Log::info("CRUD_UPDATE: Komponen Biaya diubah", ['id' => $komponenBiaya->id, 'changes' => $data]);
            return back()->with('success', 'Komponen biaya berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal mengupdate Komponen Biaya", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy(KomponenBiaya $komponenBiaya)
    {
        try {
            $komponenBiaya->update(['is_active' => false]);
            Log::warning("CRUD_DELETE: Komponen Biaya dinonaktifkan", ['id' => $komponenBiaya->id]);
            return back()->with('success', 'Komponen biaya berhasil dinonaktifkan.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menonaktifkan Komponen Biaya", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
