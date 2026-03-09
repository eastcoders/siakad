<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPermohonanDetail extends Model
{
    protected $table = 'surat_permohonan_details';

    protected $fillable = [
        'id_surat_permohonan',
        'meta_key',
        'meta_value',
    ];

    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(SuratPermohonan::class, 'id_surat_permohonan');
    }
}
