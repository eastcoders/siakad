<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratPermohonan;
use App\Notifications\SuratPermohonanNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratApprovalController extends Controller
{
    /**
     * Display a listing of all letter requests.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'validasi'); // Default show validated ones
        $id_semester = $request->get('id_semester');

        $surats = SuratPermohonan::with(['mahasiswa.user', 'mahasiswa.riwayatAktif.prodi', 'semester'])
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($id_semester, function ($q) use ($id_semester) {
                return $q->where('id_semester', $id_semester);
            })
            ->latest('tgl_pengajuan')
            ->get();

        return view('admin.surat.index', compact('surats', 'status', 'id_semester'));
    }

    /**
     * Display details of a specific request.
     */
    public function show($id)
    {
        $surat = SuratPermohonan::with(['mahasiswa.user', 'semester', 'details', 'anggotas.mahasiswa'])->findOrFail($id);

        return view('admin.surat.show', compact('surat'));
    }

    /**
     * Approve or reject a validated surat request.
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $surat = SuratPermohonan::with(['mahasiswa.user'])->findOrFail($id);

            if ($surat->status !== 'validasi') {
                return back()->with('error', 'Hanya surat dengan status validasi yang dapat disetujui/ditolak.');
            }

            $oldStatus = $surat->status;

            $surat->update([
                'status' => $request->status,
                'catatan_admin' => $request->catatan ?? $surat->catatan_admin,
            ]);

            // Notify Mahasiswa
            if ($surat->mahasiswa && $surat->mahasiswa->user) {
                $surat->mahasiswa->user->notify(new SuratPermohonanNotification($surat, $request->status));
            }

            DB::commit();

            Log::info('CRUD_UPDATE: [SuratPermohonan] '.$request->status.' oleh Admin', [
                'id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_id' => auth()->id(),
            ]);

            $message = $request->status === 'disetujui'
                ? 'Permohonan surat berhasil disetujui.'
                : 'Permohonan surat telah ditolak.';

            return redirect()->route('admin.surat-approval.show', $id)->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SYSTEM_ERROR: Gagal menyetujui/menolak surat', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Print DOCX from template and finalize the request.
     */
    /**
     * Print DOCX Only (Doesn't change status to finished).
     */
    public function printOnly(Request $request, $id)
    {
        try {
            $surat = SuratPermohonan::with([
                'mahasiswa.user',
                'mahasiswa.riwayatAktif.programStudi',
                'mahasiswa.wilayah',
                'semester',
                'details',
                'anggotas.mahasiswa',
            ])->findOrFail($id);

            $templateProcessor = $this->prepareTemplateProcessor($surat);

            // Save the processed DOCX to public storage (Overwrites if exists)
            $outputFileName = 'Surat_'.$surat->nomor_tiket.'_'.time().'.docx';
            $outputPathRelative = 'surat-final/'.$outputFileName;
            $outputPathAbsolute = storage_path('app/public/'.$outputPathRelative);

            // Ensure output directory exists
            if (! file_exists(dirname($outputPathAbsolute))) {
                mkdir(dirname($outputPathAbsolute), 0755, true);
            }

            $templateProcessor->saveAs($outputPathAbsolute);

            // Update file_final path even if state is not finished yet
            $surat->update(['file_final' => $outputPathRelative]);

            return response()->download($outputPathAbsolute);

        } catch (\Exception $e) {
            Log::error('SYSTEM_ERROR: Gagal mencetak dokumen surat', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem saat membuat dokumen.');
        }
    }

    /**
     * Finalize and Notify Mahasiswa.
     */
    public function notifyMahasiswa(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $surat = SuratPermohonan::with(['mahasiswa.user', 'anggotas.mahasiswa.user'])->findOrFail($id);

            if ($surat->status !== 'disetujui' && $surat->status !== 'selesai') {
                return back()->with('error', 'Hanya surat yang sudah disetujui yang dapat dikonfirmasi/diberitahu.');
            }

            // Update Database
            $surat->update([
                'status' => 'selesai',
                'tgl_selesai' => $surat->tgl_selesai ?? now(),
            ]);

            // Notify Mahasiswa
            if ($surat->mahasiswa && $surat->mahasiswa->user) {
                $surat->mahasiswa->user->notify(new SuratPermohonanNotification($surat, 'selesai'));
            }

            // Notify Partners if any (PKL Group)
            if ($surat->tipe_surat === 'izin_pkl') {
                foreach ($surat->anggotas as $anggota) {
                    if ($anggota->mahasiswa && $anggota->mahasiswa->user) {
                        $anggota->mahasiswa->user->notify(new SuratPermohonanNotification($surat, 'selesai'));
                    }
                }
            }

            DB::commit();

            Log::info('CRUD_UPDATE: [SuratPermohonan] Mahasiswa diberitahu (Selesai)', [
                'id' => $id,
                'admin_id' => auth()->id(),
            ]);

            return back()->with('success', 'Mahasiswa telah berhasil diberitahu.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SYSTEM_ERROR: Gagal memberitahu mahasiswa', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Legacy: Print DOCX from template and finalize the request.
     */
    public function printAndFinalize(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $surat = SuratPermohonan::with([
                'mahasiswa.user',
                'mahasiswa.riwayatAktif.programStudi',
                'mahasiswa.wilayah',
                'semester',
                'details',
                'anggotas.mahasiswa',
            ])->findOrFail($id);

            if ($surat->status !== 'disetujui') {
                return back()->with('error', 'Surat harus disetujui terlebih dahulu.');
            }

            $templateProcessor = $this->prepareTemplateProcessor($surat);

            // Save the processed DOCX to public storage
            $outputFileName = 'Surat_'.$surat->nomor_tiket.'_'.time().'.docx';
            $outputPathRelative = 'surat-final/'.$outputFileName;
            $outputPathAbsolute = storage_path('app/public/'.$outputPathRelative);

            // Ensure output directory exists
            if (! file_exists(dirname($outputPathAbsolute))) {
                mkdir(dirname($outputPathAbsolute), 0755, true);
            }

            $templateProcessor->saveAs($outputPathAbsolute);

            // Update Database
            $surat->update([
                'status' => 'selesai',
                'file_final' => $outputPathRelative,
                'tgl_selesai' => now(),
            ]);

            // Notify Mahasiswa
            if ($surat->mahasiswa && $surat->mahasiswa->user) {
                $surat->mahasiswa->user->notify(new SuratPermohonanNotification($surat, 'selesai'));
            }

            // Notify Partners if any
            if ($surat->tipe_surat === 'izin_pkl') {
                foreach ($surat->anggotas as $anggota) {
                    if ($anggota->mahasiswa && $anggota->mahasiswa->user) {
                        $anggota->mahasiswa->user->notify(new SuratPermohonanNotification($surat, 'selesai'));
                    }
                }
            }

            DB::commit();

            Log::info('CRUD_UPDATE: [SuratPermohonan] dicetak (DOCX) & finalisasi', [
                'id' => $id,
                'admin_id' => auth()->id(),
                'file' => $outputPathRelative,
            ]);

            return response()->download($outputPathAbsolute);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SYSTEM_ERROR: Gagal mencetak dokumen surat', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem saat membuat dokumen.');
        }
    }

    /**
     * Private Helper: Prepare the Word Template Processor with all variables.
     */
    private function prepareTemplateProcessor(SuratPermohonan $surat): \PhpOffice\PhpWord\TemplateProcessor
    {
        try {
            $templateDir = storage_path('app/templates');

            // Determine Template Path
            $templateName = match ($surat->tipe_surat) {
                'cuti_kuliah' => 'cuti_kuliah.docx',
                'aktif_kuliah' => 'aktif_kuliah.docx',
                'pindah_pt' => 'pindah_pt.docx',
                'pindah_kelas' => 'pindah_kelas.docx',
                'izin_pkl' => 'izin_pkl.docx',
                'pengunduran_diri' => 'pengunduran_diri.docx',
                'permintaan_data' => $surat->getMeta('peruntukan') === 'Tugas Akhir'
                    ? 'izin_permintaan_data_ta.docx'
                    : 'izin_permintaan_data_pkl.docx',
                default => 'surat_template.docx',
            };

            $templatePath = $templateDir.'/'.$templateName;

            if (! file_exists($templatePath)) {
                throw new \Exception("File template {$templateName} tidak ditemukan di storage/app/templates/");
            }

            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // 1. Common Variables
            $templateProcessor->setValue('nomor_surat', $surat->nomor_surat ?? '-');
            $templateProcessor->setValue('nama_mahasiswa', $surat->mahasiswa->nama_mahasiswa ?? '-');
            $templateProcessor->setValue('nim', $surat->mahasiswa->nim ?? '-');
            $templateProcessor->setValue('nomor_tiket', $surat->nomor_tiket);

            // 2. Specific Template Variables
            if ($surat->tipe_surat === 'cuti_kuliah') {
                $prodi = $surat->mahasiswa->riwayatAktif->programStudi->nama_program_studi ?? '-';
                $namaSemester = $surat->semester->nama_semester ?? '';
                $parts = explode(' ', $namaSemester);

                $templateProcessor->setValues([
                    'program_studi' => $prodi,
                    'tanggal_pengajuan' => $surat->created_at ? $surat->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
                    'semester_cuti' => $parts[1] ?? '-',
                    'tahun_akademik' => $parts[0] ?? '-',
                ]);
            } elseif ($surat->tipe_surat === 'aktif_kuliah') {
                $mhs = $surat->mahasiswa;
                $prodi = $mhs->riwayatAktif?->programStudi->nama_program_studi ?? '-';
                $namaSemester = $surat->semester->nama_semester ?? '';
                $parts = explode(' ', $namaSemester);

                $direktur = \App\Models\Direktur::where('user_jabatans.is_active', true)->with('user.dosen')->first();
                $namaDir = $direktur?->user->dosen->nama ?? '-';

                $templateProcessor->setValues([
                    'nama_lengkap_dir' => $namaDir,
                    'jabatan' => 'Direktur',
                    'tahun_masuk' => $mhs->riwayatAktif?->id_periode_masuk ? substr($mhs->riwayatAktif->id_periode_masuk, 0, 4) : '-',
                    'tempat_lahir' => $mhs->tempat_lahir ?? '-',
                    'tanggal_lahir' => $mhs->tanggal_lahir ? $mhs->tanggal_lahir->translatedFormat('d F Y') : '-',
                    'alamat' => collect([$mhs->jalan, $mhs->kelurahan, $mhs->wilayah->nama_wilayah ?? ''])->filter()->implode(', '),
                    'nama_ortu' => $surat->getMeta('nama_ortu', $mhs->nama_ayah ?: '-'),
                    'program_studi' => $prodi,
                    'semester' => $parts[1] ?? '-',
                    'tahun_akademik' => $parts[0] ?? '-',
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                ]);
            } elseif ($surat->tipe_surat === 'pindah_pt') {
                $mhs = $surat->mahasiswa;
                $prodi = $mhs->riwayatAktif?->programStudi;
                $direktur = \App\Models\Direktur::where('user_jabatans.is_active', true)->with('user.dosen')->first();

                $templateProcessor->setValues([
                    'dosen_sbg_direktur' => $direktur?->user->dosen->nama ?? '-',
                    'nidp' => ($direktur?->user->dosen->nip ?? $direktur?->user->dosen->nidn) ?? '-',
                    'jabatan' => 'Direktur',
                    'prodi' => $prodi->nama_program_studi ?? '-',
                    'jenjang_pendidikan_prodi' => $prodi->nama_jenjang_pendidikan ?? '-',
                    'pt_tujuan' => $surat->getMeta('pt_tujuan_nama', '-'),
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                ]);
            } elseif ($surat->tipe_surat === 'pindah_kelas') {
                $mhs = $surat->mahasiswa;
                $prodi = $mhs->riwayatAktif?->programStudi;
                $direktur = \App\Models\Direktur::where('user_jabatans.is_active', true)->with('user.dosen')->first();

                $tipeSaatIni = collect(['A' => 'Reguler Pagi', 'B' => 'Karyawan', 'C' => 'Shift'])->get($mhs->tipe_kelas, $mhs->tipe_kelas);
                $tipeTujuanHuman = $surat->getMeta('kelas_tujuan', '-');

                $templateProcessor->setValues([
                    'nama_prodi' => $prodi->nama_program_studi ?? '-',
                    'kode_prodi_alfa' => $prodi->kode_prodi_alfa ?? 'XX',
                    'tingkat_semester' => $mhs->tingkat_semester,
                    'tipe_kelas_saat_ini' => $tipeSaatIni,
                    'tipe_kelas_tujuan' => $tipeTujuanHuman,
                    'nama_dosen_sbg_direktur' => $direktur?->user->dosen->nama ?? '-',
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                ]);
            } elseif ($surat->tipe_surat === 'izin_pkl') {
                $mhs = $surat->mahasiswa;
                $direktur = \App\Models\Direktur::where('user_jabatans.is_active', true)->with('user.dosen')->first();

                // 1. Basic Info
                $templateProcessor->setValues([
                    'nama_instansi' => $surat->instansi_tujuan ?? '-',
                    'alamat_instansi' => $surat->alamat_instansi ?? '-',
                    'tanggal_mulai' => $surat->tgl_mulai ? $surat->tgl_mulai->translatedFormat('d F Y') : '-',
                    'tanggal_selesai' => $surat->tgl_selesai ? $surat->tgl_selesai->translatedFormat('d F Y') : '-',
                    'nama_dosen_sbg_direktur' => $direktur?->user->dosen->nama ?? '-',
                    'jabatan' => 'Direktur',
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                ]);

                // 2. Dynamic Student Table
                $students = collect([$mhs])->concat($surat->anggotas->map(fn ($a) => $a->mahasiswa)->filter());

                $templateProcessor->cloneRow('mhs_nama', $students->count());
                foreach ($students as $index => $student) {
                    $rowNum = $index + 1;
                    $templateProcessor->setValue("mhs_no#$rowNum", $rowNum);
                    $templateProcessor->setValue("mhs_nama#$rowNum", $student->nama_mahasiswa ?? '-');
                    $templateProcessor->setValue("mhs_nim#$rowNum", $student->nim ?? '-');
                    $templateProcessor->setValue("mhs_prodi#$rowNum", $student->riwayatAktif?->programStudi->nama_program_studi ?? '-');
                }
            } elseif ($surat->tipe_surat === 'permintaan_data') {
                $mhs = $surat->mahasiswa;
                $direktur = \App\Models\Direktur::where('user_jabatans.is_active', true)->with('user.dosen')->first();

                // 1. Basic Info
                $templateProcessor->setValues([
                    'nama_instansi' => $surat->getMeta('pimpinan_instansi', '-'), // Per ralat: pimpinan mapped to nama_instansi
                    'alamat_instansi' => $surat->alamat_instansi ?? '-',
                    'tanggal_mulai' => $surat->tgl_mulai ? $surat->tgl_mulai->translatedFormat('d F Y') : '-',
                    'tanggal_selesai' => $surat->tgl_selesai ? $surat->tgl_selesai->translatedFormat('d F Y') : '-',
                    'nama_dosen_sbg_direktur' => $direktur?->user->dosen->nama ?? '-',
                    'jabatan' => 'Direktur',
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                ]);

                // 2. Dynamic Student Table
                $students = collect([$mhs])->concat($surat->anggotas->map(fn ($a) => $a->mahasiswa)->filter());

                $templateProcessor->cloneRow('mhs_nama', $students->count());
                foreach ($students as $index => $student) {
                    $rowNum = $index + 1;
                    $templateProcessor->setValue("mhs_no#$rowNum", $rowNum);
                    $templateProcessor->setValue("mhs_nama#$rowNum", $student->nama_mahasiswa ?? '-');
                    $templateProcessor->setValue("mhs_nim#$rowNum", $student->nim ?? '-');
                    $templateProcessor->setValue("mhs_prodi#$rowNum", $student->riwayatAktif?->programStudi->nama_program_studi ?? '-');
                }
            } elseif ($surat->tipe_surat === 'pengunduran_diri') {
                $mhs = $surat->mahasiswa;
                $activeRiwayat = $mhs->riwayatAktif;
                $direktur = \App\Models\Direktur::where('user_jabatans.is_active', true)->with('user.dosen')->first();

                $templateProcessor->setValues([
                    'nama_mahasiswa' => $mhs->nama_mahasiswa ?? '-',
                    'tempat_lahir' => $mhs->tempat_lahir ?? '-',
                    'tanggal_lahir' => $mhs->tanggal_lahir ? $mhs->tanggal_lahir->translatedFormat('d F Y') : '-',
                    'prodi' => $activeRiwayat?->programStudi->nama_program_studi ?? '-',
                    'jenjang_prodi' => $activeRiwayat?->programStudi->nama_jenjang_pendidikan ?? '-',
                    'nim' => $mhs->nim ?? '-',
                    'tahun_masuk' => substr($mhs->id_periode_masuk, 0, 4),
                    'alamat_mhs' => $surat->getMeta('alamat_undur_diri', $mhs->alamat ?? '-'),
                    'tahun_ajaran' => getActiveSemester()->nama_semester ?? '-',
                    'tanggal_pengajuan' => $surat->created_at->translatedFormat('d F Y'),
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                    'dosen_sbg_direktur' => $direktur?->user->dosen->nama ?? '-',
                    'jabatan' => 'Direktur',
                ]);
            }

            return $templateProcessor;
        } catch (\Exception $e) {
            Log::error('DOC_GEN_ERROR: Gagal memproses template', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}
