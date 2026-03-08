<?php

namespace App\Http\Controllers\Admin\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\UserJabatan;
use App\Models\Dosen;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManajemenJabatanController extends Controller
{
    /**
     * Tampilkan daftar penugasan jabatan aktif.
     */
    public function index()
    {
        $penugasans = UserJabatan::with(['user.dosen', 'user.pegawai', 'jabatan'])
            ->where('is_active', true)
            ->get();

        $jabatans = Jabatan::where('is_active', true)->get();

        return view('admin.jabatan.manajemen.index', compact('penugasans', 'jabatans'));
    }

    /**
     * Simpan penugasan jabatan baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jabatan_id' => 'required|exists:jabatans,id',
            'nomor_sk' => 'nullable|string|max:100',
            'tanggal_mulai' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // Cek apakah sudah ada jabatan yang sama aktif untuk user ini
            $exists = UserJabatan::where('user_id', $request->user_id)
                ->where('jabatan_id', $request->jabatan_id)
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return back()->with('error', 'User tersebut sudah memegang jabatan ini secara aktif.');
            }

            $userJabatan = UserJabatan::create([
                'user_id' => $request->user_id,
                'jabatan_id' => $request->jabatan_id,
                'nomor_sk' => $request->nomor_sk,
                'tanggal_mulai' => $request->tanggal_mulai,
                'is_active' => true,
            ]);

            DB::commit();

            Log::info("CRUD_CREATE: [UserJabatan] berhasil dibuat", [
                'id' => $userJabatan->id,
                'user_id' => $request->user_id,
                'jabatan_id' => $request->jabatan_id
            ]);

            return back()->with('success', 'Jabatan berhasil ditugaskan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal simpan penugasan jabatan", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Cabut jabatan (Soft handling via is_active = false).
     */
    public function destroy($id)
    {
        try {
            $userJabatan = UserJabatan::findOrFail($id);
            $userJabatan->update(['is_active' => false]);

            Log::warning("CRUD_DELETE: [UserJabatan] dinonaktifkan", ['id' => $id]);

            return back()->with('success', 'Jabatan berhasil dicabut.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal cabut jabatan", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Gagal mencabut jabatan.');
        }
    }

    /**
     * AJAX Search User (Dosen & Pegawai) untuk Select2 Optgroup.
     */
    public function searchUser(Request $request)
    {
        $search = $request->q;

        if (!$search) {
            return response()->json([]);
        }

        // Cari Dosen
        $dosens = Dosen::where('nama', 'ilike', "%{$search}%")
            ->orWhere('nidn', 'ilike', "%{$search}%")
            ->orWhere('nip', 'ilike', "%{$search}%")
            ->with('user')
            ->limit(10)
            ->get();

        // Cari Pegawai
        $pegawais = Pegawai::where('nama_lengkap', 'ilike', "%{$search}%")
            ->orWhere('nip', 'ilike', "%{$search}%")
            ->with('user')
            ->limit(10)
            ->get();

        $results = [];

        if ($dosens->count() > 0) {
            $dosenGroup = ['text' => 'DOSEN', 'children' => []];
            foreach ($dosens as $d) {
                if ($d->user) {
                    $dosenGroup['children'][] = [
                        'id' => $d->user->id,
                        'text' => $d->nama_admin_display . " (" . ($d->nidn ?? $d->nip ?? '-') . ")"
                    ];
                }
            }
            $results[] = $dosenGroup;
        }

        if ($pegawais->count() > 0) {
            $pegawaiGroup = ['text' => 'PEGAWAI', 'children' => []];
            foreach ($pegawais as $p) {
                if ($p->user) {
                    $pegawaiGroup['children'][] = [
                        'id' => $p->user->id,
                        'text' => "{$p->nama_lengkap} (NIP: " . ($p->nip ?? '-') . ")"
                    ];
                }
            }
            $results[] = $pegawaiGroup;
        }

        return response()->json($results);
    }
}
