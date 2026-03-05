<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    const STATUS_BELUM_BAYAR = 'belum_bayar';
    const STATUS_CICIL = 'cicil';
    const STATUS_LUNAS = 'lunas';

    const STATUS_OPTIONS = [
        self::STATUS_BELUM_BAYAR => 'Belum Bayar',
        self::STATUS_CICIL => 'Cicil',
        self::STATUS_LUNAS => 'Lunas',
    ];

    protected $fillable = [
        'nomor_tagihan',
        'id_mahasiswa',
        'id_semester',
        'total_tagihan',
        'total_potongan',
        'total_dibayar',
        'status',
        'catatan_potongan',
    ];

    protected $casts = [
        'total_tagihan' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'total_dibayar' => 'decimal:2',
    ];

    // ── Relasi ──

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    public function items()
    {
        return $this->hasMany(TagihanItem::class, 'tagihan_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    // ── Business Logic ──

    /**
     * Hitung ulang total_dibayar dan status berdasarkan pembayaran yang disetujui.
     */
    public function recalculate(): void
    {
        $totalDibayar = $this->pembayarans()
            ->where('status_verifikasi', Pembayaran::STATUS_DISETUJUI)
            ->sum('jumlah_bayar');

        $this->total_dibayar = $totalDibayar;

        $sisaTagihan = $this->total_tagihan - $this->total_potongan;

        if ($totalDibayar >= $sisaTagihan && $sisaTagihan > 0) {
            $this->status = self::STATUS_LUNAS;
        } elseif ($totalDibayar > 0) {
            $this->status = self::STATUS_CICIL;
        } else {
            $this->status = self::STATUS_BELUM_BAYAR;
        }

        $this->save();
    }

    /**
     * Total yang harus dibayar (tagihan - potongan).
     */
    public function getSisaTagihanAttribute(): float
    {
        return max(0, $this->total_tagihan - $this->total_potongan - $this->total_dibayar);
    }

    /**
     * Persentase pembayaran.
     */
    public function getPersentaseBayarAttribute(): float
    {
        $total = $this->total_tagihan - $this->total_potongan;
        if ($total <= 0)
            return 100;
        return min(100, round(($this->total_dibayar / $total) * 100, 2));
    }

    // ── Scopes ──

    public function scopeBelumLunas($query)
    {
        return $query->whereIn('status', [self::STATUS_BELUM_BAYAR, self::STATUS_CICIL]);
    }

    public function scopeLunas($query)
    {
        return $query->where('status', self::STATUS_LUNAS);
    }
}
