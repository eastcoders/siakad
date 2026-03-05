<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\Tagihan;
use App\Services\TagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TagihanController extends Controller
{
    protected TagihanService $tagihanService;

    public function __construct(TagihanService $tagihanService)
    {
        $this->tagihanService = $tagihanService;
    }

    public function index(Request $request)
    {
        Log::info("SYNC_PULL: Mengakses daftar Tagihan Mahasiswa");

        $query = Tagihan::with(['mahasiswa', 'semester']);

        if ($request->filled('id_semester')) {
            $query->where('id_semester', $request->id_semester);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tagihans = $query->latest()->get();

        $semesters = Semester::orderByDesc('id_semester')->get();
        $prodis = ProgramStudi::orderBy('nama_program_studi')->get();

        return view('admin.keuangan.tagihan.index', compact('tagihans', 'semesters', 'prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:individual,bulk',
            'id_semester' => 'required|exists:semesters,id_semester',
            'id_mahasiswa' => 'required_if:mode,individual|nullable|exists:mahasiswas,id',
            'id_prodi' => 'nullable|exists:program_studis,id_prodi',
        ]);

        try {
            if ($request->mode === 'individual') {
                $mahasiswa = Mahasiswa::findOrFail($request->id_mahasiswa);

                // Cek duplikat
                $exists = Tagihan::where('id_mahasiswa', $mahasiswa->id)
                    ->where('id_semester', $request->id_semester)
                    ->exists();

                if ($exists) {
                    return back()->with('error', 'Tagihan untuk mahasiswa ini pada semester tersebut sudah ada.');
                }

                $tagihan = $this->tagihanService->terbitkanTagihan($mahasiswa, $request->id_semester, $request->id_prodi);
                return back()->with('success', "Tagihan {$tagihan->nomor_tagihan} berhasil diterbitkan.");
            } else {
                $count = $this->tagihanService->terbitkanTagihanBulk($request->id_semester, $request->id_prodi);
                return back()->with('success', "{$count} tagihan berhasil diterbitkan secara bulk.");
            }
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menerbitkan tagihan", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal menerbitkan tagihan: ' . $e->getMessage());
        }
    }

    public function show(Tagihan $tagihan)
    {
        $tagihan->load(['mahasiswa', 'semester', 'items.komponenBiaya', 'pembayarans.verifier']);

        return view('admin.keuangan.tagihan.show', compact('tagihan'));
    }

    public function destroy(Tagihan $tagihan)
    {
        if ($tagihan->status !== Tagihan::STATUS_BELUM_BAYAR) {
            return back()->with('error', 'Tagihan yang sudah dibayar tidak dapat dihapus.');
        }

        try {
            Log::warning("CRUD_DELETE: Tagihan dihapus", ['id' => $tagihan->id, 'nomor' => $tagihan->nomor_tagihan]);
            $tagihan->delete();
            return redirect()->route('admin.keuangan-modul.tagihan.index')->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus tagihan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * AJAX search mahasiswa untuk Select2 (pencarian via NIM atau Nama).
     * Mendukung filter opsional berdasarkan id_prodi.
     */
    public function searchMahasiswa(Request $request)
    {
        $search = $request->get('q', '');
        $idProdi = $request->get('id_prodi');

        $query = Mahasiswa::query()
            ->where('is_deleted_server', false)
            ->where('is_deleted_local', false)
            ->whereHas('riwayatPendidikans', function ($q) use ($idProdi, $search) {
                if ($idProdi) {
                    $q->where('id_prodi', $idProdi);
                }
                if ($search) {
                    $q->where('nim', 'ilike', "%{$search}%");
                }
            });

        // Juga cari berdasarkan nama
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('riwayatPendidikans', fn($rq) => $rq->where('nim', 'ilike', "%{$search}%"))
                    ->orWhere('nama_mahasiswa', 'ilike', "%{$search}%");
            });

            // Re-apply prodi filter jika ada
            if ($idProdi) {
                $query->whereHas('riwayatPendidikans', fn($rq) => $rq->where('id_prodi', $idProdi));
            }
        }

        $results = $query->with('riwayatPendidikans')
            ->limit(20)
            ->get()
            ->map(function ($mhs) {
                $nim = $mhs->riwayatPendidikans->first()?->nim ?? '-';
                return [
                    'id' => $mhs->id,
                    'text' => "{$nim} - {$mhs->nama_mahasiswa}",
                ];
            });

        return response()->json(['results' => $results]);
    }
}
