<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratApprovalController extends Controller
{
    /**
     * Display a listing of all letter requests.
     */
    public function index()
    {
        $surats = SuratPermohonan::with(['mahasiswa.user', 'semester'])
            ->latest('tgl_pengajuan')
            ->get();

        return view('admin.surat.index', compact('surats'));
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
     * Update the status of a request (Validate, Approve, Reject).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:validasi,disetujui,ditolak',
            'catatan_admin' => 'required_if:status,ditolak|nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $surat = SuratPermohonan::findOrFail($id);
            $oldStatus = $surat->status;

            $surat->update([
                'status' => $request->status,
                'catatan_admin' => $request->catatan_admin ?? $surat->catatan_admin,
            ]);

            DB::commit();

            Log::info("CRUD_UPDATE: [SuratPermohonan] status diubah", [
                'id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_id' => auth()->id()
            ]);

            return back()->with('success', 'Status permohonan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal memperbarui status surat", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Finalize the request by uploading the official PDF.
     */
    public function finalize(Request $request, $id)
    {
        $request->validate([
            'file_final' => 'required|mimes:pdf|max:2048',
            'nomor_surat' => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();
            $surat = SuratPermohonan::findOrFail($id);

            if ($request->hasFile('file_final')) {
                // Delete old file if exists
                if ($surat->file_final) {
                    Storage::disk('public')->delete($surat->file_final);
                }

                $path = $request->file('file_final')->store('surat-final', 'public');

                $surat->update([
                    'status' => 'selesai',
                    'file_final' => $path,
                    'nomor_surat' => $request->nomor_surat,
                    'tgl_selesai' => now(),
                ]);
            }

            DB::commit();

            Log::info("CRUD_UPDATE: [SuratPermohonan] permohonan difinalisasi", [
                'id' => $id,
                'nomor_surat' => $request->nomor_surat
            ]);

            return back()->with('success', 'Permohonan berhasil difinalisasi dan file telah diunggah.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal finalisasi surat", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat mengunggah file.');
        }
    }

    /**
     * Download the official signed PDF.
     */
    public function download($id)
    {
        $surat = SuratPermohonan::findOrFail($id);

        if (!$surat->file_final || !Storage::disk('public')->exists($surat->file_final)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $surat->file_final,
            'Surat_' . str_replace('/', '_', $surat->nomor_surat) . '.pdf'
        );
    }
}
