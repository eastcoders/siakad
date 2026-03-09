<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuratPermohonanNotification extends Notification
{
    use Queueable;

    protected $surat;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($surat, $status)
    {
        $this->surat = $surat;
        $this->status = $status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase($notifiable): array
    {
        $tipeLabel = match ($this->surat->tipe_surat) {
            'aktif_kuliah' => 'Aktif Kuliah',
            'cuti_kuliah' => 'Cuti Kuliah',
            'pindah_kelas' => 'Pindah Kelas',
            'pindah_pt' => 'Pindah PT',
            'pengunduran_diri' => 'Pengunduran Diri',
            'izin_pkl' => 'Izin PKL',
            'permintaan_data' => 'Permintaan Data',
            default => $this->surat->tipe_surat,
        };

        $title = "Update Permohonan Surat";
        $message = "";
        $url = route('mahasiswa.surat.show', $this->surat->id);

        switch ($this->status) {
            case 'disetujui':
                $title = "Permohonan Surat Disetujui";
                $message = "Permohonan surat {$tipeLabel} Anda telah disetujui. Silakan tunggu proses finalisasi berkas.";
                break;
            case 'ditolak':
                $title = "Permohonan Surat Ditolak";
                $catatan = $this->surat->catatan_admin ? ": " . $this->surat->catatan_admin : ".";
                $message = "Maaf, permohonan surat {$tipeLabel} Anda ditolak{$catatan}";
                break;
            case 'selesai':
                $title = "Surat Telah Terbit";
                $message = "Surat {$tipeLabel} Anda telah selesai diproses dan dapat diunduh.";
                break;
        }

        return [
            'type' => 'surat_permohonan',
            'status' => $this->status,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'id_surat' => $this->surat->id,
        ];
    }
}
