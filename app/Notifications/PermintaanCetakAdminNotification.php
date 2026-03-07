<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\PesertaUjian;

class PermintaanCetakAdminNotification extends Notification
{
    use Queueable;

    protected $pesertaUjian;

    public function __construct(PesertaUjian $pesertaUjian)
    {
        $this->pesertaUjian = $pesertaUjian;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $mahasiswa = $this->pesertaUjian->pesertaKelasKuliah->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? 'Mahasiswa';
        $mk = $this->pesertaUjian->jadwalUjian->kelasKuliah->mataKuliah->nama_mk ?? 'Mata Kuliah';
        $tipe = $this->pesertaUjian->jadwalUjian->tipe_ujian ?? 'Ujian';

        return [
            'type' => 'permintaan_cetak_kartu',
            'title' => "Permintaan Cetak Kartu $tipe",
            'message' => "{$mahasiswa} meminta cetak kartu {$tipe} ({$mk}).",
            'url' => route('admin.ujian.permintaan-cetak', ['id_semester' => $this->pesertaUjian->jadwalUjian->id_semester]),
        ];
    }
}
