<?php

namespace App\Notifications;

use App\Models\Pembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranDitolakNotification extends Notification
{
    use Queueable;

    protected $pembayaran;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pembayaran $pembayaran)
    {
        $this->pembayaran = $pembayaran;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $namaKomponens = $this->pembayaran->tagihan->items->map(function ($i) {
            return $i->komponenBiaya->nama_komponen;
        })->implode(', ');

        return [
            'pembayaran_id' => $this->pembayaran->id,
            'tagihan_id' => $this->pembayaran->tagihan_id,
            'nominal' => $this->pembayaran->jumlah_bayar,
            'komponen' => $namaKomponens,
            'pesan' => 'Pembayaran Anda ditolak.',
            'catatan_admin' => $this->pembayaran->catatan_admin,
            'tanggal' => $this->pembayaran->updated_at->format('Y-m-d H:i')
        ];
    }
}
