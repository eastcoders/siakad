<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\RiwayatPendidikan;
use App\Services\NeoFeederService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SinkronisasiController extends Controller
{
    protected $feeder;

    public function __construct(NeoFeederService $feeder)
    {
        $this->feeder = $feeder;
    }

    public function index()
    {
        $countBiodata = Mahasiswa::where('is_synced', false)->count();
        $countRiwayat = RiwayatPendidikan::where('is_synced', false)->count();

        $modules = [
            [
                'id' => 'biodata',
                'name' => 'Biodata Mahasiswa',
                'count' => $countBiodata,
                'push_route' => route('admin.sinkronisasi.push-biodata'),
            ],
            [
                'id' => 'riwayat',
                'name' => 'Riwayat Pendidikan',
                'count' => $countRiwayat,
                'push_route' => route('admin.sinkronisasi.push-riwayat'),
                'dependency' => 'biodata', // Label dependency logic
            ],
        ];

        return view('admin.sinkronisasi.index', compact('modules', 'countBiodata', 'countRiwayat'));
    }

    public function pushBiodata(Request $request)
    {
        $id = $request->id;
        $mahasiswa = Mahasiswa::find($id);

        if (! $mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($mahasiswa->is_synced) {
            return response()->json(['success' => true, 'message' => 'Data sudah sinkron.']);
        }

        try {
            $record = [
                'nama_mahasiswa' => $mahasiswa->nama_mahasiswa,
                'jenis_kelamin' => $mahasiswa->jenis_kelamin,
                'tempat_lahir' => $mahasiswa->tempat_lahir,
                'tanggal_lahir' => $mahasiswa->tanggal_lahir->format('Y-m-d'),
                'id_agama' => $mahasiswa->id_agama,
                'nama_ibu_kandung' => $mahasiswa->nama_ibu_kandung,
                'id_wilayah' => trim($mahasiswa->id_wilayah) ?? '000000',
                'kelurahan' => $mahasiswa->kelurahan ?? '-',
                'jalan' => $mahasiswa->jalan ?? '-',
                'rt' => $mahasiswa->rt ?? '0',
                'rw' => $mahasiswa->rw ?? '0',
                'kode_pos' => $mahasiswa->kode_pos ?? '00000',
                'kewarganegaraan' => 'ID',
                'nik' => $mahasiswa->nik,
                'nisn' => $mahasiswa->nisn,
                'handphone' => $mahasiswa->handphone,
                'email' => $mahasiswa->email,
            ];

            $result = $this->feeder->execute('InsertBiodataMahasiswa', ['record' => $record]);

            if (isset($result['id_mahasiswa'])) {
                $mahasiswa->update([
                    'id_feeder' => $result['id_mahasiswa'],
                    'is_synced' => true,
                    'status_sinkronisasi' => 'synced',
                    'last_synced_at' => now(),
                ]);

                Log::info("SYNC_SUCCESS: Biodata Mahasiswa [{$mahasiswa->nama_mahasiswa}] berhasil di-push.");

                return response()->json(['success' => true, 'message' => 'Berhasil sinkronisasi Biodata.']);
            }

            return response()->json(['success' => false, 'message' => 'Gagal mendapatkan UUID dari Feeder.']);
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: Gagal push Biodata [{$mahasiswa->nama_mahasiswa}]", ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function pushRiwayat(Request $request)
    {
        $id = $request->id;
        $riwayat = RiwayatPendidikan::with('mahasiswa')->find($id);

        if (! $riwayat) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($riwayat->is_synced) {
            return response()->json(['success' => true, 'message' => 'Data sudah sinkron.']);
        }

        // Cek apakah Biodata sudah sinkron (wajib punya ID Feeder)
        if (! $riwayat->mahasiswa->id_feeder) {
            return response()->json(['success' => false, 'message' => 'Biodata mahasiswa belum di-sinkronisasi.'], 422);
        }

        try {
            $record = [
                'id_mahasiswa' => $riwayat->mahasiswa->id_feeder,
                'nim' => $riwayat->nim,
                'id_jenis_daftar' => (string) $riwayat->id_jenis_daftar,
                'id_periode_masuk' => (string) $riwayat->id_periode_masuk,
                'tanggal_daftar' => $riwayat->tanggal_daftar->format('Y-m-d'),
                'id_perguruan_tinggi' => config('services.feeder.id_pt'), // ID PT dari config
                'id_prodi' => $riwayat->id_prodi,
                'id_jalur_daftar' => (string) ($riwayat->id_jalur_daftar ?: '12'),
                'id_pembiayaan' => (string) ($riwayat->id_pembiayaan ?? '1'),
                'biaya_masuk' => (string) (float) ($riwayat->biaya_masuk ?? 0),
            ];

            Log::debug("SYNC_PAYLOAD: Riwayat [{$riwayat->nim}]", ['record' => $record]);

            $result = $this->feeder->execute('InsertRiwayatPendidikanMahasiswa', ['record' => $record]);

            if (isset($result['id_registrasi_mahasiswa'])) {
                $riwayat->update([
                    'id_feeder' => $result['id_registrasi_mahasiswa'],
                    'is_synced' => true,
                    'status_sinkronisasi' => 'synced',
                    'last_synced_at' => now(),
                ]);

                Log::info("SYNC_SUCCESS: Riwayat Pendidikan [{$riwayat->nim}] berhasil di-push.");

                return response()->json(['success' => true, 'message' => 'Berhasil sinkronisasi Riwayat.']);
            }

            return response()->json(['success' => false, 'message' => 'Gagal mendapatkan UUID Registrasi dari Feeder.']);
        } catch (\Exception $e) {
            Log::error("SYNC_ERROR: Gagal push Riwayat [{$riwayat->nim}]", ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUnsyncedIds(Request $request)
    {
        $type = $request->input('type');

        Log::info("SYNC_GET_IDS: Requesting unsynced IDs", ['type' => $type]);

        if ($type === 'biodata') {
            $ids = Mahasiswa::where('is_synced', false)->pluck('id');
        } elseif ($type === 'riwayat') {
            $ids = RiwayatPendidikan::where('is_synced', false)->pluck('id');
        } else {
            $ids = collect([]);
        }

        Log::info("SYNC_GET_IDS: Found IDs", ['type' => $type, 'count' => count($ids)]);

        return response()->json($ids);
    }
}
