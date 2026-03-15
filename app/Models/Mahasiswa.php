<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';

    protected $fillable = [
        'id_mahasiswa',
        'id_feeder',
        'nama_mahasiswa',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'id_agama',
        'nik',
        'nisn',
        'nama_ibu_kandung',
        'id_wilayah',
        'kelurahan',
        'jalan',
        'rt',
        'rw',
        'kode_pos',
        'handphone',
        'email',
        'id_prodi',
        'user_id',
        'tipe_kelas',
        'bypass_krs_until',
        'status_sinkronisasi',
        'last_synced_at',
        'sync_error_message',
        'is_synced',
        'sumber_data',
        'whatsapp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected $appends = ['tingkat_semester'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'penerima_kps' => 'boolean',
        'is_synced' => 'boolean',
        'last_sync' => 'datetime',
        'last_synced_at' => 'datetime',
        'tgl_lahir_ayah' => 'date',
        'tgl_lahir_ibu' => 'date',
        'tgl_lahir_wali' => 'date',
        'bypass_krs_until' => 'datetime',
    ];

    /**
     * Accessor: Fallback to last_sync if last_synced_at is null.
     */
    public function getLastSyncedAtAttribute($value)
    {
        return $value ?? $this->last_sync;
    }

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

    public function riwayatAktif()
    {
        return $this->hasOne(RiwayatPendidikan::class, 'id_mahasiswa', 'id')
            ->latest('id_periode_masuk'); // Assuming logic for "active" is latest period or based on status logic if available
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'id_mahasiswa');
    }

    // Relationships needed for Detail View
    public function agama()
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }

    /**
     * Accessor: Get NIM from active education history.
     */
    public function getNimAttribute()
    {
        return $this->riwayatAktif?->nim;
    }

    public function jenisTinggal()
    {
        return $this->belongsTo(JenisTinggal::class, 'id_jenis_tinggal', 'id_jenis_tinggal');
    }

    public function alatTransportasi()
    {
        return $this->belongsTo(AlatTransportasi::class, 'id_alat_transportasi', 'id_alat_transportasi');
    }

    public function wilayah()
    {
        return $this->belongsTo(ReferenceWilayah::class, 'id_wilayah', 'id_wilayah');
    }

    /**
     * Dapatkan Dosen PA (Dinamis per Semester Aktif & Prodi).
     */
    public function getDosenPembimbingAttribute()
    {
        $activeSemesterId = getActiveSemesterId();
        $idProdi = $this->riwayatAktif?->id_prodi;

        if (! $idProdi || ! $activeSemesterId) {
            return null;
        }

        $pa = PembimbingAkademik::where('id_prodi', $idProdi)
            ->where('id_semester', $activeSemesterId)
            ->first();

        return $pa ? $pa->dosen : null;
    }

    public function permohonans()
    {
        return $this->hasMany(SuratPermohonan::class, 'id_mahasiswa', 'id');
    }

    public function suratAnggota()
    {
        return $this->hasMany(SuratPermohonanAnggota::class, 'id_mahasiswa', 'id');
    }

    /**
     * Accessor: Dapatkan tingkat semester dari Riwayat Aktif Mahasiswa.
     */
    public function getTingkatSemesterAttribute()
    {
        return $this->riwayatAktif?->tingkat_semester ?? 1;
    }
}
