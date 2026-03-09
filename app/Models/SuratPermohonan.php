<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SuratPermohonanDetail;
use App\Models\SuratPermohonanAnggota;

class SuratPermohonan extends Model
{
    protected $table = 'surat_permohonans';

    protected $fillable = [
        'id_mahasiswa',
        'id_semester',
        'nomor_tiket',
        'tipe_surat',
        'nomor_surat',
        'status',
        'catatan_admin',
        'instansi_tujuan',
        'alamat_instansi',
        'tgl_mulai',
        'tgl_selesai',
        'alasan',
        'file_pendukung',
        'file_final',
        'external_id',
        'sumber_data',
        'status_sinkronisasi',
        'is_deleted_server',
        'last_synced_at',
        'sync_error_message',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'tgl_pengajuan' => 'datetime',
        'is_deleted_server' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relationship to Mahasiswa (The requester).
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }

    /**
     * Relationship to Semester.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    /**
     * Relationship to meta details.
     */
    public function details(): HasMany
    {
        return $this->hasMany(SuratPermohonanDetail::class, 'id_surat_permohonan');
    }

    /**
     * Relationship to group members (PKL).
     */
    public function anggotas(): HasMany
    {
        return $this->hasMany(SuratPermohonanAnggota::class, 'id_surat_permohonan');
    }

    /**
     * Helper to get meta value by key.
     */
    public function getMeta($key, $default = null)
    {
        $detail = $this->details->where('meta_key', $key)->first();
        return $detail ? $detail->meta_value : $default;
    }
}
