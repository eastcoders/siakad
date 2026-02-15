<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikan extends Model
{
    protected $table = 'riwayat_pendidikans';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_sk_yudisium' => 'date',
        'biaya_masuk' => 'decimal:2',
        'is_synced' => 'boolean',
        'last_sync' => 'datetime',
    ];


    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id');
    }
}
