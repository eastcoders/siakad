<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PesertaUjian;
use Illuminate\Support\Facades\Log;

class KartuUjianController extends Controller
{
    /**
     * Tampilkan halaman kartu ujian mahasiswa.
     * Filter jadwal berdasarkan tipe_kelas mahasiswa (Pagi/Sore/Universal).
     */
    public function index()
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')->with('error', 'Profil mahasiswa tidak ditemukan.');
        }

        $tipeKelas = $mahasiswa->tipe_kelas;
        $semesterId = getActiveSemesterId();
        $riwayatIds = $mahasiswa->riwayatPendidikans()->pluck('id');

        // Ambil peserta ujian mahasiswa ini di semester aktif, filter tipe_waktu sesuai tipe_kelas
        // Load juga pengaturan waktu ujian via semester
        $pesertaUjians = PesertaUjian::whereHas('pesertaKelasKuliah', function ($q) use ($riwayatIds) {
            $q->whereIn('riwayat_pendidikan_id', $riwayatIds);
        })
            ->whereHas('jadwalUjian', function ($q) use ($semesterId, $tipeKelas) {
                $q->where('id_semester', $semesterId);
                if ($tipeKelas) {
                    $q->whereIn('tipe_waktu', [$tipeKelas, 'Universal']);
                }
            })
            ->with([
                'jadwalUjian.kelasKuliah.mataKuliah',
                'jadwalUjian.semester.pengaturanUjians',
            ])
            ->get();

        // Evaluasi penyelesaian kuesioner untuk UTS & UAS
        $kuesionerStatus = [
            'UTS' => ['is_lengkap' => true, 'pesan' => ''],
            'UAS' => ['is_lengkap' => true, 'pesan' => '']
        ];

        foreach (['UTS', 'UAS'] as $tipe) {
            // 1. Cek Pelayanan
            $pendingPelayanan = \App\Models\Kuisioner::where('id_semester', $semesterId)
                ->where('target_ujian', $tipe)
                ->where('tipe', 'pelayanan')
                ->where('status', 'published')
                ->whereDoesntHave('submissions', function ($q) use ($mahasiswa) {
                    $q->where('id_mahasiswa', $mahasiswa->id);
                })->first();

            if ($pendingPelayanan) {
                $kuesionerStatus[$tipe] = [
                    'is_lengkap' => false,
                    'pesan' => "Wajib mengisi kuesioner pelayanan: '{$pendingPelayanan->judul}'"
                ];
                continue;
            }

            // 2. Cek Dosen
            $kd = \App\Models\Kuisioner::where('id_semester', $semesterId)
                ->where('target_ujian', $tipe)
                ->where('tipe', 'dosen')
                ->where('status', 'published')
                ->first();

            if ($kd) {
                $idKelasDiambil = \App\Models\PesertaKelasKuliah::whereIn('riwayat_pendidikan_id', $riwayatIds)
                    ->whereHas('kelasKuliah', function ($q) use ($semesterId) {
                        $q->where('id_semester', $semesterId);
                    })->pluck('id_kelas_kuliah');

                // Hitung dosen unik (mempertimbangkan alias lokal)
                $countDosenTarget = \App\Models\DosenPengajarKelasKuliah::whereIn('id_kelas_kuliah', $idKelasDiambil)
                    ->selectRaw('COUNT(DISTINCT COALESCE(id_dosen_alias_lokal, id_dosen)) as total')
                    ->first()->total;

                if ($countDosenTarget > 0) {
                    $countSubmitDosen = \App\Models\KuisionerSubmission::where('id_kuisioner', $kd->id)
                        ->where('id_mahasiswa', $mahasiswa->id)
                        ->whereNotNull('id_kelas_kuliah')
                        ->whereNotNull('id_dosen')
                        ->distinct('id_dosen')
                        ->count();

                    if ($countSubmitDosen < $countDosenTarget) {
                        $kuesionerStatus[$tipe] = [
                            'is_lengkap' => false,
                            'pesan' => "Belum mengevaluasi seluruh pengajar ({$countSubmitDosen}/{$countDosenTarget}) pada formulir '{$kd->judul}'"
                        ];
                    }
                }
            }
        }

        // Cek apakah mahasiswa punya KRS (PesertaKelasKuliah) di semester aktif
        $hasKrs = \App\Models\PesertaKelasKuliah::whereIn('riwayat_pendidikan_id', $riwayatIds)
            ->whereHas('kelasKuliah', function ($q) use ($semesterId) {
                $q->where('id_semester', $semesterId);
            })->exists();

        Log::info("MAHASISWA_KARTU_UJIAN: Diakses", [
            'mahasiswa' => $mahasiswa->nama_mahasiswa,
            'tipe_kelas' => $tipeKelas,
            'count' => $pesertaUjians->count(),
            'has_krs' => $hasKrs,
        ]);

        return view('mahasiswa.ujian.index', compact('pesertaUjians', 'mahasiswa', 'tipeKelas', 'kuesionerStatus', 'hasKrs'));
    }

    /**
     * Mahasiswa mengajukan cetak kartu ujian.
     * Update status_cetak dari 'belum' menjadi 'diminta'.
     */
    public function ajukanCetak(string $pesertaUjianId)
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;
        $riwayatIds = $mahasiswa->riwayatPendidikans()->pluck('id');

        try {
            // Validasi kepemilikan: pastikan peserta ujian ini milik mahasiswa yang login
            $peserta = PesertaUjian::with('jadwalUjian.semester.pengaturanUjians')
                ->whereHas('pesertaKelasKuliah', function ($q) use ($riwayatIds) {
                    $q->whereIn('riwayat_pendidikan_id', $riwayatIds);
                })->findOrFail($pesertaUjianId);

            if (!$peserta->can_print) {
                return back()->with('error', 'Anda tidak layak mengikuti ujian ini dan belum mendapat dispensasi akademik. Silakan hubungi Akademik.');
            }

            // Validasi Waktu Cetak
            $tipeUjian = $peserta->jadwalUjian->tipe_ujian; // 'UTS' atau 'UAS'
            $pengaturans = $peserta->jadwalUjian->semester->pengaturanUjians;
            $pengaturan = $pengaturans->where('tipe_ujian', $tipeUjian)->first();

            if ($pengaturan) {
                $now = now();
                if ($pengaturan->tgl_mulai_cetak && $now->lt($pengaturan->tgl_mulai_cetak)) {
                    return back()->with('error', "Periode cetak kartu $tipeUjian belum dibuka. Akan dibuka pada: " . $pengaturan->tgl_mulai_cetak->format('d M Y H:i'));
                }
                if ($pengaturan->tgl_akhir_cetak && $now->gt($pengaturan->tgl_akhir_cetak)) {
                    return back()->with('error', "Periode cetak kartu $tipeUjian sudah berakhir pada: " . $pengaturan->tgl_akhir_cetak->format('d M Y H:i'));
                }
            }

            if ($peserta->status_cetak !== PesertaUjian::CETAK_BELUM) {
                return back()->with('info', 'Permintaan cetak sudah diajukan sebelumnya.');
            }

            // ==========================================
            // GATEKEEPING KUESIONER BPMI
            // ==========================================
            $semesterId = $peserta->jadwalUjian->id_semester;

            // 1. Cek Kuesioner Pelayanan Akademik
            $kuesionerPelayanan = \App\Models\Kuisioner::where('id_semester', $semesterId)
                ->where('target_ujian', $tipeUjian)
                ->where('tipe', 'pelayanan')
                ->where('status', 'published')
                ->get();

            foreach ($kuesionerPelayanan as $kp) {
                $sudahIsi = \App\Models\KuisionerSubmission::where('id_kuisioner', $kp->id)
                    ->where('id_mahasiswa', $mahasiswa->id)
                    ->exists();

                if (!$sudahIsi) {
                    return back()->with('error', "Akses Dilarang: Anda diwajibkan mengisi Kuesioner Pelayanan '{$kp->judul}' terlebih dahulu sebelum dapat mencetak kartu {$tipeUjian}.");
                }
            }

            // 2. Cek Kuesioner Kinerja Dosen
            $kuesionerDosen = \App\Models\Kuisioner::where('id_semester', $semesterId)
                ->where('target_ujian', $tipeUjian)
                ->where('tipe', 'dosen')
                ->where('status', 'published')
                ->get();

            if ($kuesionerDosen->isNotEmpty()) {
                // Kumpulkan ID Kelas yang diambil mahasiswa di semester aktif
                $idKelasDiambil = \App\Models\PesertaKelasKuliah::whereIn('riwayat_pendidikan_id', $riwayatIds)
                    ->whereHas('kelasKuliah', function ($q) use ($semesterId) {
                        $q->where('id_semester', $semesterId);
                    })->pluck('id_kelas_kuliah');

                // Hitung total individu dosen unik (Utama + Alias) yang terlibat di kelas-kelas tersebut
                $countDosenTarget = \App\Models\DosenPengajarKelasKuliah::whereIn('id_kelas_kuliah', $idKelasDiambil)
                    ->selectRaw('COUNT(DISTINCT COALESCE(id_dosen_alias_lokal, id_dosen)) as total')
                    ->first()->total;

                if ($countDosenTarget > 0) {
                    foreach ($kuesionerDosen as $kd) {
                        $countSubmitDosen = \App\Models\KuisionerSubmission::where('id_kuisioner', $kd->id)
                            ->where('id_mahasiswa', $mahasiswa->id)
                            ->whereNotNull('id_kelas_kuliah')
                            ->whereNotNull('id_dosen')
                            ->distinct('id_dosen')
                            ->count();

                        if ($countSubmitDosen < $countDosenTarget) {
                            return back()->with('error', "Akses Dilarang: Anda belum mengevaluasi seluruh pengajar (Tuntas: {$countSubmitDosen} dari {$countDosenTarget}) pada formulir '{$kd->judul}'. Segera lengkapi untuk membuka akses cetak {$tipeUjian}.");
                        }
                    }
                }
            }
            // ==========================================
            // END GATEKEEPING
            // ==========================================

            $peserta->update([
                'status_cetak' => PesertaUjian::CETAK_DIMINTA,
                'diminta_pada' => now(),
            ]);

            Log::info("UJIAN_AJUKAN_CETAK: Mahasiswa mengajukan cetak kartu", [
                'peserta_ujian_id' => $peserta->id,
                'mahasiswa' => $mahasiswa->nama_mahasiswa,
            ]);

            $adminUsers = \App\Models\User::role(['admin'])->get();
            \Illuminate\Support\Facades\Notification::send($adminUsers, new \App\Notifications\PermintaanCetakAdminNotification($peserta));

            return back()->with('success', 'Permintaan cetak kartu ujian berhasil diajukan. Silakan tunggu proses dari admin.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal ajukan cetak kartu ujian", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
