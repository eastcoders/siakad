<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait JabatanAdapterTrait
{
    /**
     * Boot the trait.
     */
    protected static function bootJabatanAdapterTrait()
    {
        static::addGlobalScope('jabatan_type', function (Builder $builder) {
            $kode = defined('static::KODE_JABATAN') ? static::KODE_JABATAN : null;

            $builder->from('user_jabatans')
                ->join('jabatans', 'user_jabatans.jabatan_id', '=', 'jabatans.id')
                ->where('jabatans.kode_role', $kode)
                ->where('user_jabatans.is_active', true)
                ->select('user_jabatans.*');
        });
    }

    /**
     * Override getTable to point to centralized table.
     */
    public function getTable()
    {
        return 'user_jabatans';
    }

    /**
     * Accessor untuk id_dosen (Backward Compatibility).
     */
    public function getIdDosenAttribute()
    {
        return $this->user?->dosen?->id;
    }

    /**
     * Accessor untuk id_pegawai (Backward Compatibility).
     */
    public function getIdPegawaiAttribute()
    {
        return $this->user?->pegawai?->id;
    }

    /**
     * Relasi ke User (Centralized).
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
