<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPermohonanAnggota extends Model
{
    protected $table = 'surat_permohonan_anggotas';

    protected $fillable = [
        'id_surat_permohonan',
        'id_mahasiswa',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(SuratPermohonan::class, 'id_surat_permohonan');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }
}
