<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomponenBiaya extends Model
{
    use HasFactory;

    protected $table = 'komponen_biayas';

    const KATEGORI_PER_SEMESTER = 'per_semester';
    const KATEGORI_SEKALI_BAYAR = 'sekali_bayar';

    const KATEGORI_OPTIONS = [
        self::KATEGORI_PER_SEMESTER => 'Per Semester',
        self::KATEGORI_SEKALI_BAYAR => 'Sekali Bayar',
    ];

    protected $fillable = [
        'kode_komponen',
        'nama_komponen',
        'kategori',
        'nominal_standar',
        'is_wajib_krs',
        'is_wajib_ujian',
        'id_prodi',
        'tahun_angkatan',
        'is_active',
    ];

    protected $casts = [
        'nominal_standar' => 'decimal:2',
        'is_wajib_krs' => 'boolean',
        'is_wajib_ujian' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relasi ──

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }

    public function tagihanItems()
    {
        return $this->hasMany(TagihanItem::class, 'komponen_biaya_id');
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWajibKrs($query)
    {
        return $query->where('is_wajib_krs', true);
    }

    public function scopeWajibUjian($query)
    {
        return $query->where('is_wajib_ujian', true);
    }

    public function scopeForProdi($query, $idProdi = null)
    {
        return $query->where(function ($q) use ($idProdi) {
            $q->whereNull('id_prodi'); // komponen global
            if ($idProdi) {
                $q->orWhere('id_prodi', $idProdi); // + komponen khusus prodi
            }
        });
    }

    /**
     * Filter komponen biaya berdasarkan prodi DAN tahun angkatan.
     * Komponen dengan tahun_angkatan NULL = berlaku untuk semua angkatan.
     */
    public function scopeForTarget($query, $idProdi = null, $tahunAngkatan = null)
    {
        return $query->where(function ($q) use ($idProdi) {
            $q->whereNull('id_prodi');
            if ($idProdi) {
                $q->orWhere('id_prodi', $idProdi);
            }
        })->where(function ($q) use ($tahunAngkatan) {
            $q->whereNull('tahun_angkatan');
            if ($tahunAngkatan) {
                $q->orWhere('tahun_angkatan', $tahunAngkatan);
            }
        });
    }
}
