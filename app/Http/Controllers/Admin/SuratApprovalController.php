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

            return redirect()->route('admin.surat-approval.index')->with('success', $message);
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

            // Ensure templates directory exists
            $templateDir = storage_path('app/templates');
            if (! file_exists($templateDir)) {
                mkdir($templateDir, 0755, true);
            }

            // Determine Template Path based on surat type
            if ($surat->tipe_surat === 'cuti_kuliah') {
                $templateName = 'cuti_kuliah.docx';
            } elseif ($surat->tipe_surat === 'aktif_kuliah') {
                $templateName = 'aktif_kuliah.docx';
            } elseif ($surat->tipe_surat === 'pindah_kelas') {
                $templateName = 'pindah_kelas.docx';
            } else {
                $templateName = 'surat_template.docx';
            }
            $templatePath = $templateDir.'/'.$templateName;

            // Create a dummy template if it doesn't exist yet (Temporary fallback)
            if (! file_exists($templatePath)) {
                $phpWord = new \PhpOffice\PhpWord\PhpWord;
                $section = $phpWord->addSection();
                $section->addText('SURAT PERMOHONAN', ['bold' => true, 'size' => 16]);
                $section->addText('Nomor Tiket: ${nomor_tiket}');
                $section->addText('Nama: ${nama_mahasiswa}');
                $section->addText('NIM: ${nim}');
                $section->addText('Tipe Surat: ${tipe_surat}');
                $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $objWriter->save($templatePath);
            }

            // Process Template
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // Common Variables
            $templateProcessor->setValue('nomor_surat', $surat->nomor_surat ?? '-');
            $templateProcessor->setValue('nama_mahasiswa', $surat->mahasiswa->nama_mahasiswa ?? '-');
            $templateProcessor->setValue('nim', $surat->mahasiswa->nim ?? '-');

            // Specific Template Variables
            if ($surat->tipe_surat === 'cuti_kuliah') {
                $prodi = $surat->mahasiswa->riwayatAktif->programStudi->nama_program_studi ?? '-';

                $namaSemester = $surat->semester->nama_semester ?? '';
                $parts = explode(' ', $namaSemester);
                $tahunAkademik = $parts[0] ?? '-';
                $sms = $parts[1] ?? '-';

                $tanggalPengajuan = $surat->created_at ? $surat->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y');

                $templateProcessor->setValue('program_studi', $prodi);
                $templateProcessor->setValue('tanggal_pengajuan', $tanggalPengajuan);
                $templateProcessor->setValue('semester_cuti', $sms);
                $templateProcessor->setValue('tahun_akademik', $tahunAkademik);
            } elseif ($surat->tipe_surat === 'aktif_kuliah') {
                $mhs = $surat->mahasiswa;
                $prodi = $mhs->riwayatAktif->programStudi->nama_program_studi ?? '-';

                // Pecah Semester
                $namaSemester = $surat->semester->nama_semester ?? '';
                $parts = explode(' ', $namaSemester);
                $tahunAkademik = $parts[0] ?? '-';
                $sms = $parts[1] ?? '-';

                // Biodata
                $alamat = collect([$mhs->jalan, $mhs->kelurahan, $mhs->wilayah->nama_wilayah ?? ''])->filter()->implode(', ');
                $tahunMasuk = $mhs->riwayatAktif?->id_periode_masuk ? substr($mhs->riwayatAktif->id_periode_masuk, 0, 4) : '-';

                $templateProcessor->setValues([
                    'nama_lengkap_dir' => '[Nama Direktur]', // Hardcoded fallback
                    'jabatan_direktur' => 'Direktur',
                    'jabatan' => 'Direktur',

                    'tahun_masuk' => $tahunMasuk,
                    'tempat_lahir' => $mhs->tempat_lahir ?? '-',
                    'tanggal_lahir' => $mhs->tanggal_lahir ? $mhs->tanggal_lahir->translatedFormat('d F Y') : '-',
                    'alamat' => $alamat ?: '-',

                    // Data Orang Tua (ditarik dari meta properties pengajuan)
                    'nama_ortu' => $surat->getMeta('nama_ortu', $mhs->nama_ayah ?: '-'),
                    'nama_instansi_ortu' => $surat->getMeta('instansi_ortu', '-'),
                    'nip_ortu' => $surat->getMeta('nip_ortu', '-'),
                    'pangkat_ortu' => $surat->getMeta('jabatan_ortu', '-'),
                    'Alamat_instansi' => $surat->getMeta('alamat_instansi_ortu', '-'),

                    'program_studi' => $prodi,
                    'semester' => $sms,
                    'tahun_akademik' => $tahunAkademik,
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                ]);
            } elseif ($surat->tipe_surat === 'pindah_kelas') {
                $mhs = $surat->mahasiswa;
                $prodi = $mhs->riwayatAktif->programStudi->nama_program_studi ?? '-';
                $kodeProdiAlfa = $mhs->riwayatAktif->programStudi->kode_prodi_alfa ?? 'XX';
                $tingkatSemester = $mhs->tingkat_semester;

                // Pecah Semester
                $namaSemester = $surat->semester->nama_semester ?? '';
                $parts = explode(' ', $namaSemester);
                $tahunAkademik = $parts[0] ?? '-';
                $sms = $parts[1] ?? '-';

                // Data Dinamis dari Pengajuan (diketahui user menginput lewat form)
                // Key 'kelas_tujuan' adalah standar dari SuratMahasiswaController@store
                $tipeSaatIni = collect([
                    'A' => 'Reguler Pagi',
                    'B' => 'Karyawan / Reguler Malam',
                    'C' => 'Eksekutif / Shift',
                ])->get($mhs->tipe_kelas, $mhs->tipe_kelas ?? '-');

                $tipeTujuanHuman = $surat->getMeta('kelas_tujuan', '-'); // Misal: "Sore" atau "Pagi"
                $tipeTujuan = $tipeTujuanHuman === 'Pagi' ? 'Reguler Pagi' : ($tipeTujuanHuman === 'Sore' ? 'Karyawan / Reguler Malam' : $tipeTujuanHuman);
                $tipeTujuanAlfa = $tipeTujuanHuman === 'Pagi' ? 'A' : ($tipeTujuanHuman === 'Sore' ? 'B' : 'X');

                $templateProcessor->setValues([
                    // Data Mahasiswa
                    'nama_mahasiswa' => $mhs->nama_mahasiswa ?? '-',
                    'nim' => $mhs->nim ?? '-',
                    'nama_prodi' => $prodi,
                    'kode_prodi_alfa' => $kodeProdiAlfa,
                    'tingkat_semester' => $tingkatSemester,

                    // Data Semester Aktif
                    'semester_genap_atau_ganjil' => $sms,
                    'tahun_ajaran' => $tahunAkademik,

                    // Data Perpindahan
                    'tipe_kelas_saat_ini' => $tipeSaatIni,
                    'tipe_kelas_tujuan' => $tipeTujuan,
                    'tipe_kelas_aplfabet' => $tipeTujuanAlfa,

                    // Meta Konstan/Umum
                    'tanggal_pengajuan' => $surat->tgl_pengajuan ? \Carbon\Carbon::parse($surat->tgl_pengajuan)->translatedFormat('d F Y') : now()->translatedFormat('d F Y'),
                    'tanggal_cetak' => now()->translatedFormat('d F Y'),
                    'nama_dosen_sbg_direktur' => '[Nama Direktur]', // Hardcoded fallback
                ]);
            } else {
                $templateProcessor->setValue('nomor_tiket', $surat->nomor_tiket);
                $templateProcessor->setValue('tipe_surat', strtoupper(str_replace('_', ' ', $surat->tipe_surat)));
            }

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
                'file_final' => $outputPathRelative, // Save relative path
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

            return back()->with('success', 'Dokumen DOCX berhasil dicetak dan permohonan telah selesai.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SYSTEM_ERROR: Gagal mencetak dokumen surat', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem saat membuat dokumen.');
        }
    }
}
