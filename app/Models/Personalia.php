<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personalia extends Model
{
    use \App\Models\Traits\JabatanAdapterTrait;

    const KODE_JABATAN = 'Personalia';

    protected $fillable = ['user_id', 'nomor_sk', 'is_active'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
}
