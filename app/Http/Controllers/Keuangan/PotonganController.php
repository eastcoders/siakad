<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PotonganController extends Controller
{
    /**
     * Menyimpan/mengubah potongan beasiswa per item tagihan.
     */
    public function store(Request $request, Tagihan $tagihan)
    {
        $request->validate([
            'potongan' => 'required|array',
            'potongan.*' => 'nullable|numeric|min:0',
            'keterangan_potongan' => 'required|array',
            'keterangan_potongan.*' => 'nullable|string|max:255',
        ]);

        // Pastikan tagihan belum lunas penuh
        if ($tagihan->status === Tagihan::STATUS_LUNAS) {
            return redirect()->back()->with('error', 'Tagihan sudah lunas, potongan tidak dapat diubah.');
        }

        try {
            DB::beginTransaction();

            $totalPotongan = 0;

            foreach ($tagihan->items as $item) {
                $idItem = $item->id;

                // Jika input ada utk item ini
                if (isset($request->potongan[$idItem])) {
                    $nominalPotongan = $request->potongan[$idItem] ?: 0;
                    $keterangan = $request->keterangan_potongan[$idItem] ?? null;

                    // Maksimal potongan tidak boleh melebihi nominal asli komponen
                    if ($nominalPotongan > $item->nominal) {
                        return redirect()->back()->with('error', "Potongan pada {$item->komponenBiaya->nama_komponen} tidak boleh melebihi nominal tagihannya.");
                    }

                    $item->update([
                        'potongan' => $nominalPotongan,
                        'keterangan_potongan' => $keterangan,
                    ]);

                    $totalPotongan += $nominalPotongan;
                } else {
                    $totalPotongan += $item->potongan;
                }
            }

            // Update total potongan dan recalculate di tagihan induk
            $tagihan->total_potongan = $totalPotongan;
            $tagihan->catatan_potongan = $totalPotongan > 0 ? 'Terdapat beasiswa/potongan.' : null;
            $tagihan->save();

            // Trigger recalculate untuk validasi status Lunas/Belum jika ada pembayaran parsial
            $tagihan->recalculate();

            DB::commit();

            Log::info("CRUD_UPDATE: [Tagihan] Potongan berhasil diubah", ['id' => $tagihan->id, 'total_potongan' => $totalPotongan]);

            return redirect()->back()->with('success', 'Potongan / Beasiswa berhasil disimpan. Sisa tagihan otomatis disesuaikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal menyimpan potongan tagihan", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Gagal menyimpan potongan. Terjadi kesalahan sistem.');
        }
    }
}
