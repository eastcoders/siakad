<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';

    // Allow mass assignment for all except ID (handled by Auto-Increment)
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'penerima_kps' => 'boolean',
        'is_synced' => 'boolean',
        'last_sync' => 'datetime',
        'tgl_lahir_ayah' => 'date',
        'tgl_lahir_ibu' => 'date',
        'tgl_lahir_wali' => 'date',
    ];

    /**
     * Mutator: Ensure email is lowercase.
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Mutator: Sanitize Handphone (Numbers only).
     */
    public function setHandphoneAttribute($value)
    {
        $this->attributes['handphone'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Mutator: Sanitize NISN (Numbers only).
     */
    public function setNisnAttribute($value)
    {
        $this->attributes['nisn'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function riwayatPendidikans()
    {
        return $this->hasMany(RiwayatPendidikan::class, 'id_mahasiswa', 'id');
    }
}
