<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProfilPerguruanTinggi;
use App\Models\RefPerguruanTinggi;
use App\Models\SuratPermohonan;
use App\Models\SuratPermohonanAnggota;
use App\Models\SuratPermohonanDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratMahasiswaController extends Controller
{
    /**
     * Display a listing of personal letter requests.
     */
    public function index(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $id_semester = $request->get('id_semester');

        $surats = SuratPermohonan::with('semester')
            ->where('id_mahasiswa', $mahasiswa->id)
            ->when($id_semester, function ($q) use ($id_semester) {
                return $q->where('id_semester', $id_semester);
            })
            ->latest('tgl_pengajuan')
            ->get();

        return view('mahasiswa.surat.index', compact('surats', 'id_semester'));
    }

    /**
     * Show the form for creating a new request.
     */
    public function create()
    {
        $mahasiswa = auth()->user()->mahasiswa;

        // Periksa apakah sudah tercover dalam permohonan PKL lain (sebagai anggota/partner)
        $existingPartner = SuratPermohonanAnggota::where('id_mahasiswa', $mahasiswa->id)
            ->whereHas('permohonan', function ($q) {
                $q->where('tipe_surat', 'izin_pkl')
                    ->whereNotIn('status', ['ditolak']);
            })->exists();

        $existingInitiator = SuratPermohonan::where('id_mahasiswa', $mahasiswa->id)
            ->where('tipe_surat', 'izin_pkl')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        $isCoveredPKL = $existingPartner || $existingInitiator;

        return view('mahasiswa.surat.create', compact('isCoveredPKL'));
    }

    /**
     * Store a newly created request in storage.
     */
    public function store(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $tipe = $request->tipe_surat;

        // Validation based on type
        $rules = [
            'tipe_surat' => 'required|in:aktif_kuliah,cuti_kuliah,pindah_kelas,pindah_pt,pengunduran_diri,izin_pkl,permintaan_data',
            'id_semester' => 'required',
        ];

        if ($tipe === 'aktif_kuliah') {
            $rules = array_merge($rules, [
                'nama_ortu' => 'required|string|max:100',
                'alamat_ortu' => 'required|string',
                'pekerjaan_ortu' => 'required|string|max:100',
                'keperluan' => 'required|string',
            ]);
        } elseif ($tipe === 'cuti_kuliah') {
            $rules['alasan'] = 'required|string';
        } elseif ($tipe === 'pindah_kelas') {
            $rules['kelas_tujuan'] = 'required|in:Pagi,Sore';
        } elseif ($tipe === 'pindah_pt') {
            $rules = array_merge($rules, [
                'id_pt_tujuan' => 'required|uuid',
                'akreditasi_pt_tujuan' => 'required|string',
            ]);
        } elseif ($tipe === 'izin_pkl') {
            $rules = array_merge($rules, [
                'instansi_tujuan' => 'required|string|max:255',
                'pkl_pimpinan' => 'required|string|max:255',
                'alamat_instansi' => 'required|string',
                'tgl_mulai' => 'required|date',
                'partners' => 'nullable|array',
                'partners.*' => 'exists:mahasiswas,id',
            ]);
        } elseif ($tipe === 'pengunduran_diri') {
            $rules['alamat_undur_diri'] = 'required|string';
            $rules['alasan_undur_diri'] = 'nullable|string';
        } elseif ($tipe === 'permintaan_data') {
            $rules = array_merge($rules, [
                'peruntukan' => 'required|in:PKL,Tugas Akhir',
                'pimpinan_instansi' => 'required|string|max:255',
                'instansi_tujuan_data' => 'required|string|max:255',
                'alamat_instansi_data' => 'required|string',
                'judul_laporan' => 'required|string|max:255',
                'data_dibutuhkan' => 'required|string',
                'partners_data' => 'nullable|array',
                'partners_data.*' => 'exists:mahasiswas,id',
            ]);
        }

        $validated = $request->validate($rules);

        // Additional Logic for izin_pkl: Check for existing active requests
        if ($tipe === 'izin_pkl') {
            $isInitiatorCovered = SuratPermohonan::where('id_mahasiswa', $mahasiswa->id)
                ->where('tipe_surat', 'izin_pkl')
                ->whereNotIn('status', ['ditolak'])
                ->exists() ||
                SuratPermohonanAnggota::where('id_mahasiswa', $mahasiswa->id)
                    ->whereHas('permohonan', function ($q) {
                        $q->where('tipe_surat', 'izin_pkl')->whereNotIn('status', ['ditolak']);
                    })->exists();

            if ($isInitiatorCovered) {
                return back()->withInput()->with('error', 'Anda sudah memiliki permohonan PKL yang aktif.');
            }

            if ($request->has('partners')) {
                $alreadyCoveredPartners = Mahasiswa::whereIn('id', $request->partners)
                    ->where(function ($q) {
                        $q->whereHas('permohonans', function ($sq) {
                            $sq->where('tipe_surat', 'izin_pkl')->whereNotIn('status', ['ditolak']);
                        })->orWhereHas('suratAnggota', function ($sq) {
                            $sq->whereHas('permohonan', function ($ssq) {
                                $ssq->where('tipe_surat', 'izin_pkl')->whereNotIn('status', ['ditolak']);
                            });
                        });
                    })->pluck('nama_mahasiswa')->toArray();

                if (!empty($alreadyCoveredPartners)) {
                    return back()->withInput()->with('error', 'Mahasiswa berikut sudah terdaftar di permohonan PKL lain: ' . implode(', ', $alreadyCoveredPartners));
                }
            }
        }

        try {
            DB::beginTransaction();

            $permohonan = SuratPermohonan::create([
                'id_mahasiswa' => $mahasiswa->id,
                'id_semester' => $validated['id_semester'],
                'tipe_surat' => $tipe,
                'nomor_tiket' => 'SR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
                'status' => 'pending',
                'alasan' => $validated['alasan'] ?? null,
                'keperluan' => $validated['keperluan'] ?? null,
                'instansi_tujuan' => $validated['instansi_tujuan'] ?? $validated['instansi_tujuan_data'] ?? null,
                'alamat_instansi' => $validated['alamat_instansi'] ?? $validated['alamat_instansi_data'] ?? null,
                'tgl_mulai' => $validated['tgl_mulai'] ?? null,
                'tgl_selesai' => $validated['tgl_selesai'] ?? null,
            ]);

            // Save metadata for Aktif Kuliah
            if ($tipe === 'aktif_kuliah') {
                $metaFields = [
                    'nama_ortu' => $validated['nama_ortu'],
                    'alamat_ortu' => $validated['alamat_ortu'],
                    'pekerjaan_ortu' => $validated['pekerjaan_ortu'],
                    'nip_ortu' => $request->nip_ortu,
                    'jabatan_ortu' => $request->jabatan_ortu,
                    'instansi_ortu' => $request->instansi_ortu,
                    'alamat_instansi_ortu' => $request->alamat_instansi_ortu,
                    'keperluan' => $validated['keperluan'],
                ];

                foreach ($metaFields as $key => $value) {
                    if ($value) {
                        SuratPermohonanDetail::create([
                            'id_surat_permohonan' => $permohonan->id,
                            'meta_key' => $key,
                            'meta_value' => $value,
                        ]);
                    }
                }
            }

            // Save metadata for Pindah Kelas
            if ($tipe === 'pindah_kelas') {
                $metaFields = [
                    'kelas_asal' => $mahasiswa->tipe_kelas,
                    'kelas_tujuan' => $validated['kelas_tujuan'],
                ];

                foreach ($metaFields as $key => $value) {
                    SuratPermohonanDetail::create([
                        'id_surat_permohonan' => $permohonan->id,
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            }

            // Save metadata for Pindah PT
            if ($tipe === 'pindah_pt') {
                $ptAsal = ProfilPerguruanTinggi::whereNotNull('nama_perguruan_tinggi')->first();
                $idPtTujuan = $validated['id_pt_tujuan'];
                $ptTujuan = RefPerguruanTinggi::find($idPtTujuan);

                $metaFields = [
                    'pt_asal' => $ptAsal->nama_perguruan_tinggi ?? 'Politeknik Sawunggalih Aji',
                    'id_pt_tujuan' => $idPtTujuan,
                    'pt_tujuan_nama' => $ptTujuan->nama_perguruan_tinggi ?? '-',
                    'akreditasi_pt_tujuan' => $validated['akreditasi_pt_tujuan'],
                ];

                foreach ($metaFields as $key => $value) {
                    SuratPermohonanDetail::create([
                        'id_surat_permohonan' => $permohonan->id,
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            }

            // Save metadata for Izin PKL
            if ($tipe === 'izin_pkl') {
                SuratPermohonanDetail::create([
                    'id_surat_permohonan' => $permohonan->id,
                    'meta_key' => 'pkl_pimpinan',
                    'meta_value' => $validated['pkl_pimpinan'],
                ]);

                // Store partners
                if ($request->has('partners')) {
                    foreach ($request->partners as $partnerId) {
                        SuratPermohonanAnggota::create([
                            'id_surat_permohonan' => $permohonan->id,
                            'id_mahasiswa' => $partnerId,
                        ]);
                    }
                }
            }

            // Save metadata for Permintaan Data
            if ($tipe === 'pengunduran_diri') {
                SuratPermohonanDetail::create([
                    'id_surat_permohonan' => $permohonan->id,
                    'meta_key' => 'alamat_undur_diri',
                    'meta_value' => $request->alamat_undur_diri,
                ]);

                if ($request->filled('alasan_undur_diri')) {
                    SuratPermohonanDetail::create([
                        'id_surat_permohonan' => $permohonan->id,
                        'meta_key' => 'alasan_undur_diri',
                        'meta_value' => $request->alasan_undur_diri,
                    ]);
                }
            }

            if ($tipe === 'permintaan_data') {
                $metaFields = [
                    'peruntukan' => $validated['peruntukan'],
                    'pimpinan_instansi' => $validated['pimpinan_instansi'],
                    'judul_laporan' => $validated['judul_laporan'],
                    'data_dibutuhkan' => $validated['data_dibutuhkan'],
                ];

                foreach ($metaFields as $key => $value) {
                    if ($value) {
                        SuratPermohonanDetail::create([
                            'id_surat_permohonan' => $permohonan->id,
                            'meta_key' => $key,
                            'meta_value' => $value,
                        ]);
                    }
                }

                // Store partners
                if ($request->has('partners_data')) {
                    foreach ($request->partners_data as $partnerId) {
                        SuratPermohonanAnggota::create([
                            'id_surat_permohonan' => $permohonan->id,
                            'id_mahasiswa' => $partnerId,
                        ]);
                    }
                }
            }

            DB::commit();

            // Notify Kaprodi
            $activeRiwayat = $mahasiswa->riwayatAktif;
            $prodiId = $activeRiwayat->id_prodi ?? 'NONE';
            $kaprodis = \App\Models\Kaprodi::with('dosen.user')->where('id_prodi', $prodiId)->get();

            foreach ($kaprodis as $kaprodi) {
                if ($kaprodi->dosen && $kaprodi->dosen->user) {
                    $kaprodi->dosen->user->notify(new \App\Notifications\SuratPermohonanNotification($permohonan, 'pending'));
                }
            }

            Log::info("CRUD_CREATE: Permohonan Surat {$tipe} berhasil dibuat", [
                'id' => $permohonan->id,
                'nim' => $mahasiswa->nim,
            ]);

            return redirect()->route('mahasiswa.surat.index')
                ->with('success', 'Permohonan surat berhasil dikirim.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SYSTEM_ERROR: Gagal membuat permohonan surat', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
        }
    }

    /**
     * Show details of a request.
     */
    public function show($id)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $surat = SuratPermohonan::with(['details', 'semester', 'anggotas.mahasiswa'])->where('id_mahasiswa', $mahasiswa->id)->findOrFail($id);

        return view('mahasiswa.surat.show', compact('surat'));
    }

    /**
     * AJAX Search for Perguruan Tinggi (RefPerguruanTinggi)
     */
    public function searchPT(Request $request)
    {
        $search = $request->get('q');
        $localPt = ProfilPerguruanTinggi::first();
        $localPtId = $localPt ? $localPt->id_perguruan_tinggi : '';

        $ptList = RefPerguruanTinggi::where('nama_perguruan_tinggi', 'iLIKE', "%$search%")
            ->when($localPtId, function ($q) use ($localPtId) {
                return $q->where('id', '!=', $localPtId);
            })
            ->limit(20)
            ->get(['id', 'nama_perguruan_tinggi']);

        return response()->json($ptList->map(function ($pt) {
            return [
                'id' => $pt->id,
                'text' => $pt->nama_perguruan_tinggi,
            ];
        }));
    }

    public function searchMahasiswa(Request $request)
    {
        $search = $request->get('q');
        $currentMahasiswa = auth()->user()->mahasiswa;

        $mahasiswaList = Mahasiswa::where('id', '!=', $currentMahasiswa->id)
            ->where(function ($query) use ($search) {
                $query->where('nama_mahasiswa', 'iLIKE', "%$search%")
                    ->orWhereHas('riwayatAktif', function ($q) use ($search) {
                        $q->where('nim', 'LIKE', "%$search%");
                    });
            })
            ->limit(20)
            ->get();

        return response()->json($mahasiswaList->map(function ($m) {
            return [
                'id' => $m->id,
                'text' => $m->nama_mahasiswa . ' (' . ($m->nim ?? 'NIM Tdk Ada') . ')',
            ];
        }));
    }
}
