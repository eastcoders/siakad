<?php

namespace App\Http\Controllers\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Kuisioner;
use App\Models\KuisionerSubmission;
use App\Models\KuisionerJawabanDetail;
use App\Models\UserJabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KuisionerRespondenController extends Controller
{
    /**
     * Tampilkan daftar kuesioner AMI yang tersedia untuk responden.
     */
    public function index()
    {
        $user = auth()->user();
        $activeSemesterId = getActiveSemesterId();

        // Cari kuesioner tipe AMI yang published di semester aktif
        $kuisioners = Kuisioner::where('tipe', 'ami')
            ->where('id_semester', $activeSemesterId)
            ->where('status', 'published')
            ->get();

        // Cek status pengisian per kuesioner
        foreach ($kuisioners as $k) {
            $k->is_done = KuisionerSubmission::where('id_kuisioner', $k->id)
                ->where('id_user', $user->id)
                ->exists();
        }

        return view('jabatan.kuisioner.index', compact('kuisioners'));
    }

    /**
     * Tampilkan form pengisian kuesioner AMI.
     */
    public function show(Kuisioner $kuisioner)
    {
        $user = auth()->user();

        // Validasi akses: Harus Pejabat Struktural atau Admin
        $isPejabat = UserJabatan::where('user_id', $user->id)->where('is_active', true)->exists();
        $isAdmin = $user->hasRole('admin');

        if (!$isPejabat && !$isAdmin) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke kuesioner AMI.');
        }

        if ($kuisioner->tipe !== 'ami' || $kuisioner->status !== 'published') {
            return redirect()->route('dashboard')->with('error', 'Kuesioner tidak tersedia.');
        }

        // Cek apakah sudah pernah mengisi
        $isDone = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
            ->where('id_user', $user->id)
            ->exists();

        if ($isDone) {
            return redirect()->route('jabatan.kuisioner.index')->with('info', 'Anda sudah mengisi kuesioner ini.');
        }

        $kuisioner->load([
            'pertanyaans' => function ($q) {
                $q->orderBy('urutan', 'asc');
            }
        ]);

        return view('jabatan.kuisioner.show', compact('kuisioner'));
    }

    /**
     * Simpan jawaban kuesioner AMI.
     */
    public function store(Request $request, Kuisioner $kuisioner)
    {
        $user = auth()->user();

        if ($kuisioner->status !== 'published') {
            return back()->with('error', 'Kuesioner ini sudah ditutup.');
        }

        $request->validate([
            'jawaban' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // Double check submission
            $exists = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->where('id_user', $user->id)
                ->exists();

            if ($exists) {
                return redirect()->route('jabatan.kuisioner.index')->with('info', 'Anda sudah mengisi kuesioner ini.');
            }

            // Buat header submission
            $submission = KuisionerSubmission::create([
                'id_kuisioner' => $kuisioner->id,
                'id_user' => $user->id,
                'status_sinkronisasi' => 'synced'
            ]);

            // Simpan detail jawaban
            $details = [];
            foreach ($request->jawaban as $pertanyaanId => $answer) {
                $details[] = [
                    'id_submission' => $submission->id,
                    'id_pertanyaan' => $pertanyaanId,
                    'jawaban_skala' => $answer['skala'] ?? null,
                    'jawaban_teks' => $answer['teks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            KuisionerJawabanDetail::insert($details);

            DB::commit();

            Log::info("CRUD_CREATE: [KuisionerSubmission] User {$user->id} menyelesaikan kuesioner AMI {$kuisioner->id}");

            return redirect()->route('jabatan.kuisioner.index')->with('success', 'Terima kasih, jawaban kuesioner Anda berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal simpan jawaban kuesioner AMI", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan jawaban Anda.');
        }
    }
}
