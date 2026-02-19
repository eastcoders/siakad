<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliahs';

    public function prodi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }

    protected $fillable = [
        'id_matkul',
        'id_prodi',
        'kode_mk',
        'nama_mk',
        'sks',
        'sks_tatap_muka',
        'sks_praktek',
        'sks_praktek_lapangan',
        'sks_simulasi',
        'metode_kuliah',
        'tanggal_mulai_efektif',
        'tanggal_akhir_efektif',
        'jenis_mk',
        'kelompok_mk',
        'semester',
        'status_aktif',
        'sumber_data',
        'status_sinkronisasi',
        'is_deleted_server',
        'last_synced_at',
        'sync_version',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'is_deleted_server' => 'boolean',
        'last_synced_at' => 'datetime',
        'tanggal_mulai_efektif' => 'date',
        'tanggal_akhir_efektif' => 'date',
        'sks' => 'decimal:2',
        'sks_tatap_muka' => 'decimal:2',
        'sks_praktek' => 'decimal:2',
        'sks_praktek_lapangan' => 'decimal:2',
        'sks_simulasi' => 'decimal:2',
        'semester' => 'integer',
    ];

    const STATUS_SYNCED = 'synced';
    const STATUS_CREATED_LOCAL = 'created_local';
    const STATUS_UPDATED_LOCAL = 'updated_local';
    const STATUS_DELETED_LOCAL = 'deleted_local';
    const STATUS_PENDING_PUSH = 'pending_push';

    /**
     * Scope a query to only include active mata kuliah.
     */
    public function scopeActive($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Scope a query to only include unsynced data (local changes).
     */
    public function scopeUnsynced($query)
    {
        return $query->where('status_sinkronisasi', '!=', self::STATUS_SYNCED);
    }
}
