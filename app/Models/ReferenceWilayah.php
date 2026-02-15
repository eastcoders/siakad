<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceWilayah extends Model
{
    protected $table = 'ref_wilayah';
    protected $primaryKey = 'id_wilayah';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_wilayah',
        'nama_wilayah',
        'id_level_wilayah',
        'id_induk_wilayah',
        'id_negara',
    ];

    protected $casts = [
        'id_level_wilayah' => 'integer',
        'id_induk_wilayah' => 'string',
        'id_negara' => 'string',
    ];

    /**
     * Scope untuk mengambil data Provinsi (Level 1).
     */
    public function scopeProvinsi($query)
    {
        return $query->where('id_level_wilayah', 1);
    }

    /**
     * Scope untuk mengambil data Kabupaten/Kota (Level 2).
     */
    public function scopeKabupaten($query)
    {
        return $query->where('id_level_wilayah', 2);
    }

    /**
     * Scope untuk mengambil data Kecamatan (Level 3).
     */
    public function scopeKecamatan($query)
    {
        return $query->where('id_level_wilayah', 3);
    }

    /**
     * Relasi ke Parent Wilayah.
     * Contoh: Kecamatan -> Kabupaten -> Provinsi -> Negara
     */
    public function parent()
    {
        return $this->belongsTo(ReferenceWilayah::class, 'id_induk_wilayah', 'id_wilayah');
    }

    /**
     * Relasi ke Children Wilayah.
     * Contoh: Provinsi -> Kabupatens
     */
    public function children()
    {
        return $this->hasMany(ReferenceWilayah::class, 'id_induk_wilayah', 'id_wilayah');
    }

    /**
     * Helper Static untuk mendapatkan list Kabupaten berdasarkan ID Provinsi.
     */
    public static function getKabupatenByProvinsi($idProvinsi)
    {
        return self::where('id_induk_wilayah', $idProvinsi)
            ->where('id_level_wilayah', 2)
            ->orderBy('nama_wilayah')
            ->get();
    }

    /**
     * Helper Static untuk mendapatkan list Kecamatan berdasarkan ID Kabupaten.
     */
    public static function getKecamatanByKabupaten($idKabupaten)
    {
        return self::where('id_induk_wilayah', $idKabupaten)
            ->where('id_level_wilayah', 3)
            ->orderBy('nama_wilayah')
            ->get();
    }
}
