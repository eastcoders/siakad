<?php

namespace App\Http\Controllers;

use App\Models\PesertaKelasKuliah;
use App\Http\Requests\StorePesertaKelasKuliahRequest;
use App\Http\Requests\UpdatePesertaKelasKuliahRequest;

class PesertaKelasKuliahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePesertaKelasKuliahRequest $request)
    {
        try {
            $validated = $request->validated();

            // Cari data Riwayat Pendidikan dari inputan Dosen/KRS lokal
            $riwayat = \App\Models\RiwayatPendidikan::findOrFail($validated['riwayat_pendidikan_id']);

            // Validasi apakah mahasiswa ini sudah ada di kelas ini
            $exists = PesertaKelasKuliah::where('id_kelas_kuliah', $validated['id_kelas_kuliah'])
                ->where('riwayat_pendidikan_id', $riwayat->id)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Mahasiswa tersebut sudah terdaftar di kelas kuliah ini.');
            }

            PesertaKelasKuliah::create([
                'id_kelas_kuliah' => $validated['id_kelas_kuliah'],
                'riwayat_pendidikan_id' => $riwayat->id,

                // Cek apakah mahasiswa ini sudah ada di Feeder. Jika ya, ambil ID Feeder-nya.
                // Jika belum, biarkan null dulu (Deferred Sync akan menghandle nanti).
                'id_registrasi_mahasiswa' => $riwayat->id_riwayat_pendidikan ?? null,

                // Status standard offline-first
                'sumber_data' => 'lokal',
                'status_sinkronisasi' => PesertaKelasKuliah::STATUS_CREATED_LOCAL,
                'sync_action' => 'insert',
                'is_local_change' => true,
            ]);

            return back()->with('success', 'Peserta Kelas Kuliah berhasil ditambahkan ke dalam database lokal.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PesertaKelasKuliah Store Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menambahkan peserta kelas: ' . $e->getMessage());
        }
    }

    /**
     * Store kolektif/massal peserta kelas kuliah ke database lokal.
     */
    public function storeKolektif(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'id_kelas_kuliah' => 'required|exists:kelas_kuliah,id_kelas_kuliah',
            'riwayat_pendidikan_ids' => 'required|array',
            'riwayat_pendidikan_ids.*' => 'exists:riwayat_pendidikans,id',
        ]);

        try {
            $idKelas = $request->id_kelas_kuliah;
            $riwayatIds = $request->riwayat_pendidikan_ids;
            $successCount = 0;

            foreach ($riwayatIds as $riwayatId) {
                $riwayat = \App\Models\RiwayatPendidikan::find($riwayatId);
                if (!$riwayat)
                    continue;

                // Cegah duplicate entry lokal
                $exists = PesertaKelasKuliah::where('id_kelas_kuliah', $idKelas)
                    ->where('riwayat_pendidikan_id', $riwayat->id)
                    ->exists();

                if (!$exists) {
                    PesertaKelasKuliah::create([
                        'id_kelas_kuliah' => $idKelas,
                        'riwayat_pendidikan_id' => $riwayat->id,

                        // UUID Feeder Nullable jika belum tersinkron
                        'id_registrasi_mahasiswa' => $riwayat->id_riwayat_pendidikan ?? null,

                        // Status standard offline-first
                        'sumber_data' => 'lokal',
                        'status_sinkronisasi' => PesertaKelasKuliah::STATUS_CREATED_LOCAL,
                        'sync_action' => 'insert',
                        'is_local_change' => true,
                    ]);
                    $successCount++;
                }
            }

            return back()->with('success', "Berhasil menambahkan $successCount peserta kelas secara kolektif ke database lokal.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PesertaKelasKuliah Kolektif Store Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat mencoba menginput kolektif: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PesertaKelasKuliah $pesertaKelasKuliah)
    {
        try {
            if ($pesertaKelasKuliah->sumber_data === 'server') {
                $pesertaKelasKuliah->update([
                    'is_deleted_local' => true,
                    'status_sinkronisasi' => PesertaKelasKuliah::STATUS_DELETED_LOCAL,
                    'sync_action' => 'delete',
                    'is_local_change' => true,
                    'sync_error_message' => null,
                ]);
            } else {
                // Determine if it ever reached the server
                $hasEverSynced = $pesertaKelasKuliah->last_push_at !== null
                    || in_array($pesertaKelasKuliah->status_sinkronisasi, [
                        PesertaKelasKuliah::STATUS_SYNCED,
                        PesertaKelasKuliah::STATUS_UPDATED_LOCAL,
                        PesertaKelasKuliah::STATUS_DELETED_LOCAL,
                        PesertaKelasKuliah::STATUS_PUSH_SUCCESS,
                    ], true);

                if (!$hasEverSynced) {
                    $pesertaKelasKuliah->delete(); // Hard delete
                } else {
                    $pesertaKelasKuliah->update([
                        'is_deleted_local' => true,
                        'status_sinkronisasi' => PesertaKelasKuliah::STATUS_DELETED_LOCAL,
                        'sync_action' => 'delete',
                        'is_local_change' => true,
                        'sync_error_message' => null,
                    ]);
                }
            }

            return back()->with('success', 'Peserta Kelas Kuliah berhasil dikeluarkan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PesertaKelasKuliah Delete Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengeluarkan peserta: ' . $e->getMessage());
        }
    }
}
