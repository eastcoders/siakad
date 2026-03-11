<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    protected $table = 'program_studis';

    protected $primaryKey = 'id_prodi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_prodi',
        'kode_program_studi',
        'nama_program_studi',
        'status',
        'id_jenjang_pendidikan',
        'nama_jenjang_pendidikan',
        'id_perguruan_tinggi',
    ];

    protected $appends = ['kode_prodi_alfa'];

    /**
     * Relasi ke jabatan Kaprodi Aktif.
     */
    public function kaprodi(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Kaprodi::class, 'id_prodi', 'id_prodi');
    }

    /**
     * Accessor: Mendapatkan singkatan huruf Prodi (Misal: Teknik Informatika => TI).
     */
    public function getKodeProdiAlfaAttribute()
    {
        $nama = strtoupper($this->nama_program_studi ?? '');

        return match (true) {
            str_contains($nama, 'TEKNIK INFORMATIKA') => 'TI',
            str_contains($nama, 'ADMINISTRASI BISNIS') => 'AB',
            str_contains($nama, 'BISNIS DIGITAL') => 'BD',
            str_contains($nama, 'AKUNTANSI') => 'AK',
            str_contains($nama, 'TEKNOLOGI REKAYASA PERANGKAT LUNAK') => 'TRPL',
            default => 'XX', // Fallback
        };
    }
}
