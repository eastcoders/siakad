<?php

namespace App\Services\Feeder\Reference;

use App\Models\Semester;
use App\Services\AkademikServices\AkademikRefService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferenceSemesterService
{
    public function __construct(
        protected AkademikRefService $feederService
    ) {
    }

    /**
     * Get all Semester from local DB.
     * Auto-sync from Feeder if table is empty.
     */
    public function get(): Collection
    {
        if (Semester::count() === 0) {
            $this->syncFromFeeder();
        }

        return Semester::orderByDesc('id_semester')->get();
    }

    /**
     * Get only active semesters (a_periode_aktif = 1).
     */
    public function getAktif(): Collection
    {
        if (Semester::count() === 0) {
            $this->syncFromFeeder();
        }

        return Semester::where('a_periode_aktif', 1)
            ->orderByDesc('id_semester')
            ->get();
    }

    /**
     * Sync Semester from Feeder API to local DB.
     */
    public function syncFromFeeder(): void
    {
        try {
            $data = $this->feederService->getSemester();

            DB::transaction(function () use ($data) {
                foreach ($data as $item) {
                    $existing = Semester::find($item['id_semester']);

                    Semester::updateOrCreate(
                        ['id_semester' => $item['id_semester']],
                        [
                            'nama_semester' => $item['nama_semester'] ?? null,
                            'id_tahun_ajaran' => $item['id_tahun_ajaran'] ?? null,
                            'semester' => $item['semester'] ?? null,
                            // Jika data sudah eksis, ikuti status lokalnya. Jika baru, set 0.
                            'a_periode_aktif' => $existing ? $existing->a_periode_aktif : 0,
                            'tanggal_mulai' => $item['tanggal_mulai'] ?? null,
                            'tanggal_selesai' => $item['tanggal_selesai'] ?? null,
                        ]
                    );
                }

                // Cleanup: Pastikan hanya ada maksimal 1 semester aktif berdasarkan ID terbesar
                $activeSemesters = Semester::where('a_periode_aktif', 1)->get();
                if ($activeSemesters->count() > 1) {
                    $maxId = $activeSemesters->max('id_semester');
                    Semester::where('a_periode_aktif', 1)
                        ->where('id_semester', '!=', $maxId)
                        ->update(['a_periode_aktif' => 0]);
                }
            });

            Log::info('Sync Semester berhasil: ' . count($data) . ' records.');
        } catch (\Exception $e) {
            Log::error('Gagal sync Semester: ' . $e->getMessage());
        }
    }
}
