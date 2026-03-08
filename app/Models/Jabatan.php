<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\UserJabatan;
use Spatie\Permission\Models\Role;

class Jabatan extends Model
{
    protected $fillable = [
        'nama_jabatan',
        'kode_role',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Spatie Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'kode_role', 'name');
    }

    /**
     * Relasi ke penugasan user.
     */
    public function penugasans(): HasMany
    {
        return $this->hasMany(UserJabatan::class, 'jabatan_id');
    }
}
