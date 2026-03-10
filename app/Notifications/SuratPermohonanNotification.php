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

        // Tentukan URL default berdasarkan peran penerima (notifiable)
        // Admin -> admin.surat-approval.show
        // Kaprodi -> kaprodi.surat.show
        // Mahasiswa/Lainnya -> mahasiswa.surat.show
        $url = route('mahasiswa.surat.show', $this->surat->id);
        if ($notifiable->hasRole('admin')) {
            $url = route('admin.surat-approval.show', $this->surat->id);
        } elseif ($notifiable->hasRole('Kaprodi')) {
            $url = route('kaprodi.surat.show', $this->surat->id);
        }

        switch ($this->status) {
            case 'pending':
                $title = "Permohonan Surat Baru";
                $message = "Mahasiswa {$this->surat->mahasiswa->nama_mahasiswa} mengajukan surat {$tipeLabel}.";
                // URL sudah diatur di atas, tapi case pending khusus override jika bukan Kaprodi (pengamanan tambahan)
                if ($notifiable->hasRole('Kaprodi')) {
                    $url = route('kaprodi.surat.show', $this->surat->id);
                }
                break;
            case 'validasi':
                $title = "Surat Divalidasi Kaprodi";
                $message = "Permohonan surat {$tipeLabel} telah divalidasi Kaprodi dan sedang diteruskan ke Admin.";
                break;
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
