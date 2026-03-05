<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'komponen_biaya_id',
        'nominal',
        'potongan',
        'keterangan_potongan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'potongan' => 'decimal:2',
    ];

    // ── Relasi ──

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function komponenBiaya()
    {
        return $this->belongsTo(KomponenBiaya::class, 'komponen_biaya_id');
    }

    /**
     * Nominal bersih (setelah potongan).
     */
    public function getNominalBersihAttribute(): float
    {
        return max(0, $this->nominal - $this->potongan);
    }
}
