<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direktur extends Model
{
    use \App\Models\Traits\JabatanAdapterTrait;

    const KODE_JABATAN = 'Direktur';

    protected $fillable = ['user_id', 'nomor_sk', 'is_active'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
