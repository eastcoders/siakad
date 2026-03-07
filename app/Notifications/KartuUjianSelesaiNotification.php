<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\PesertaUjian;

class KartuUjianSelesaiNotification extends Notification
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
        $tipe = $this->pesertaUjian->jadwalUjian->tipe_ujian ?? 'Ujian';
        $nim = $this->pesertaUjian->pesertaKelasKuliah->riwayatPendidikan->nim ?? '-';

        return [
            'type' => 'kartu_ujian_selesai',
            'title' => "Kartu {$tipe} Selesai Dicetak",
            'message' => "Kartu ujian {$tipe} Anda sudah dapat diambil di Ruang Akademik dengan membawa informasi NIM ({$nim}).",
            'url' => route('mahasiswa.ujian.index'),
        ];
    }
}
