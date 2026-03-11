<?php

namespace App\Http\Controllers\Dosen\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Kaprodi;
use App\Models\SuratPermohonan;
use App\Notifications\SuratPermohonanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratKaprodiController extends Controller
{
    /**
     * Display a listing of personal letter requests for the Kaprodi's Prodi.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $dosen = $user->dosen;

        if (! $dosen) {
            abort(403, 'Anda tidak terdaftar sebagai Dosen.');
        }

        // Get the Prodi(s) where this dosen is Kaprodi
        $prodiIds = Kaprodi::where('dosen_id', $dosen->id)->pluck('id_prodi');

        if ($prodiIds->isEmpty()) {
            abort(403, 'Anda tidak terdaftar sebagai Kaprodi di Program Studi manapun.');
        }

        $id_semester = $request->get('id_semester');

        $surats = SuratPermohonan::with(['mahasiswa.riwayatAktif', 'semester'])
            ->whereHas('mahasiswa.riwayatAktif', function ($q) use ($prodiIds) {
                $q->whereIn('id_prodi', $prodiIds);
            })
            ->whereIn('status', ['pending', 'validasi', 'ditolak', 'disetujui', 'selesai'])
            ->when($id_semester, function ($q) use ($id_semester) {
                return $q->where('id_semester', $id_semester);
            })
            ->latest('tgl_pengajuan')
            ->get();

        return view('dosen.kaprodi.surat.index', compact('surats', 'id_semester'));
    }

    /**
     * Display details of a specific request.
     */
    public function show($id)
    {
        $user = auth()->user();
        $dosen = $user->dosen;

        $prodiIds = Kaprodi::where('dosen_id', $dosen->id)->pluck('id_prodi');

        $surat = SuratPermohonan::with(['mahasiswa.user', 'mahasiswa.riwayatAktif.prodi', 'semester', 'details', 'anggotas.mahasiswa'])
            ->whereHas('mahasiswa.riwayatAktif', function ($q) use ($prodiIds) {
                $q->whereIn('id_prodi', $prodiIds);
            })
            ->findOrFail($id);

        return view('dosen.kaprodi.surat.show', compact('surat'));
    }

    /**
     * Update the status of a request (Validate/Reject by Kaprodi).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:validasi,ditolak',
            'catatan' => 'required_if:status,ditolak|nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $dosen = $user->dosen;

            if (! $dosen) {
                throw new \Exception('Akun Anda tidak memiliki data Dosen.');
            }

            $prodiIds = Kaprodi::where('dosen_id', $dosen->id)->pluck('id_prodi');

            $surat = SuratPermohonan::whereHas('mahasiswa.riwayatAktif', function ($q) use ($prodiIds) {
                $q->whereIn('id_prodi', $prodiIds);
            })
                ->findOrFail($id);

            $oldStatus = $surat->status;

            $surat->update([
                'status' => $request->status,
                'catatan_admin' => $request->catatan ?? $surat->catatan_admin, // Using existing column for now as per Option 1
            ]);

            // Notify Mahasiswa
            if ($surat->mahasiswa && $surat->mahasiswa->user) {
                $surat->mahasiswa->user->notify(new SuratPermohonanNotification($surat, $request->status));
            }

            // If validated, notify Admin
            if ($request->status === 'validasi') {
                $admins = \App\Models\User::role('admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new SuratPermohonanNotification($surat, 'validasi'));
                }
            }

            DB::commit();

            Log::info('CRUD_UPDATE: [SuratPermohonan] divalidasi Kaprodi', [
                'id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'kaprodi_id' => $user->id,
            ]);

            return redirect()->route('kaprodi.surat.index')->with('success', 'Status permohonan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SYSTEM_ERROR: Gagal memvalidasi surat oleh Kaprodi', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
