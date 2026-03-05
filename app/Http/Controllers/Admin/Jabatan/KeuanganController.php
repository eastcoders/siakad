<?php

namespace App\Http\Controllers\Admin\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Keuangan;
use App\Models\Dosen;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KeuanganController extends Controller
{
    public function index()
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen Keuangan", ['endpoint' => route('admin.keuangan.index')]);

        $keuangans = Keuangan::with(['dosen', 'pegawai'])->latest()->get();
        $assignedDosenIds = Keuangan::whereNotNull('id_dosen')->pluck('id_dosen');
        $assignedPegawaiIds = Keuangan::whereNotNull('id_pegawai')->pluck('id_pegawai');

        $dosens = Dosen::whereNotIn('id', $assignedDosenIds)->orderBy('nama')->get();
        $pegawais = Pegawai::whereNotIn('id', $assignedPegawaiIds)->orderBy('nama_lengkap')->get();

        return view('admin.jabatan.keuangan.index', compact('keuangans', 'dosens', 'pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe_user' => 'required|in:dosen,pegawai',
            'id_dosen' => 'required_if:tipe_user,dosen|nullable|exists:dosens,id|unique:keuangans,id_dosen',
            'id_pegawai' => 'required_if:tipe_user,pegawai|nullable|exists:pegawais,id|unique:keuangans,id_pegawai',
        ], [
            'id_dosen.unique' => 'Dosen ini sudah menjabat sebagai pejabat keuangan.',
            'id_pegawai.unique' => 'Pegawai ini sudah menjabat sebagai pejabat keuangan.'
        ]);

        try {
            $data = [
                'is_active' => true,
                'id_dosen' => $request->tipe_user === 'dosen' ? $request->id_dosen : null,
                'id_pegawai' => $request->tipe_user === 'pegawai' ? $request->id_pegawai : null,
            ];
            $model = Keuangan::create($data);

            // Pastikan Role eksis sebelum di-assign
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Keuangan', 'guard_name' => 'web']);

            // Berikan role Keuangan pada usernya langsung
            if ($model->id_dosen && $model->dosen && $model->dosen->user) {
                $model->dosen->user->assignRole('Keuangan');
            } elseif ($model->id_pegawai && $model->pegawai && $model->pegawai->user) {
                $model->pegawai->user->assignRole('Keuangan');
            }

            Log::info("CRUD_CREATE: User dicalonkan menjadi Jabatan Keuangan", ['id' => $model->id, 'data' => $data]);
            return back()->with('success', 'Pejabat keuangan berhasil ditunjuk.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah Jabatan Keuangan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update(Request $request, Keuangan $keuangan)
    {
        $request->validate(['is_active' => 'required|boolean']);
        try {
            $keuangan->update(['is_active' => $request->is_active]);

            // Tambah/Cabut role bergantung status is_active-nya
            $user = null;
            if ($keuangan->id_dosen && $keuangan->dosen) {
                $user = $keuangan->dosen->user;
            } elseif ($keuangan->id_pegawai && $keuangan->pegawai) {
                $user = $keuangan->pegawai->user;
            }

            if ($user) {
                \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Keuangan', 'guard_name' => 'web']);
                if ($request->is_active) {
                    $user->assignRole('Keuangan');
                } else {
                    $user->removeRole('Keuangan');
                }
            }

            Log::info("CRUD_UPDATE: Status Jabatan Keuangan ID {$keuangan->id} diubah", ['id' => $keuangan->id, 'changes' => $request->all()]);
            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal mengupdate status Keuangan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy(Keuangan $keuangan)
    {
        try {
            // Cabut role terlebih dahulu
            $user = null;
            if ($keuangan->id_dosen && $keuangan->dosen) {
                $user = $keuangan->dosen->user;
            } elseif ($keuangan->id_pegawai && $keuangan->pegawai) {
                $user = $keuangan->pegawai->user;
            }

            if ($user && $user->hasRole('Keuangan')) {
                $user->removeRole('Keuangan');
            }

            $keuangan->delete();
            Log::warning("CRUD_DELETE: Jabatan Keuangan dihapus", ['id' => $keuangan->id]);
            return back()->with('success', 'Jabatan keuangan berhasil dicabut.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus Jabatan Keuangan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
