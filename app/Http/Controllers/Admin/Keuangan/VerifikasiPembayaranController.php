<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerifikasiPembayaranController extends Controller
{
    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        $this->tagihanService = $tagihanService;
    }

    public function index(Request $request)
    {
        Log::info("SYNC_PULL: Mengakses daftar Verifikasi Pembayaran");

        $query = Pembayaran::with(['tagihan.mahasiswa', 'tagihan.semester', 'verifier']);

        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        } else {
            // Default: tampilkan pending dulu
            $query->orderByRaw("CASE WHEN status_verifikasi = 'pending' THEN 0 ELSE 1 END");
        }

        $pembayarans = $query->latest()->get();

        return view('admin.keuangan.verifikasi.index', compact('pembayarans'));
    }

    public function show(Pembayaran $pembayaran)
    {
        $pembayaran->load(['tagihan.mahasiswa', 'tagihan.semester', 'tagihan.items.komponenBiaya', 'verifier']);

        return view('admin.keuangan.verifikasi.show', compact('pembayaran'));
    }

    public function approve(Pembayaran $pembayaran)
    {
        if ($pembayaran->status_verifikasi !== Pembayaran::STATUS_PENDING) {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        try {
            $this->tagihanService->verifikasiPembayaran($pembayaran, true, null, auth()->user());
            return back()->with('success', "Pembayaran disetujui. Kuitansi: {$pembayaran->fresh()->nomor_kuitansi}");
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal approve pembayaran", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function reject(Request $request, Pembayaran $pembayaran)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($pembayaran->status_verifikasi !== Pembayaran::STATUS_PENDING) {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        try {
            $this->tagihanService->verifikasiPembayaran($pembayaran, false, $request->catatan_admin, auth()->user());
            return back()->with('success', 'Pembayaran ditolak.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal reject pembayaran", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function downloadBukti(Pembayaran $pembayaran)
    {
        if (!Storage::disk('local')->exists($pembayaran->bukti_bayar)) {
            abort(404, 'File bukti bayar tidak ditemukan.');
        }

        return response()->download(Storage::disk('local')->path($pembayaran->bukti_bayar));
    }
}
