<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikan extends Model
{
    protected $table = 'riwayat_pendidikans';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_sk_yudisium' => 'date',
        'biaya_masuk' => 'decimal:2',
        'sks_diakui' => 'integer',
        'is_synced' => 'boolean',
        'last_sync' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Accessor: Fallback to id_riwayat_pendidikan if id_feeder is null.
     */
    public function getIdFeederAttribute($value)
    {
        return $value ?? $this->id_riwayat_pendidikan;
    }

    /**
     * Accessor: Fallback to last_sync if last_synced_at is null.
     */
    public function getLastSyncedAtAttribute($value)
    {
        return $value ?? $this->last_sync;
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id');
    }

    public function perguruanTinggi()
    {
        return $this->belongsTo(RefPerguruanTinggi::class, 'id_perguruan_tinggi', 'id');
    }

    public function prodi()
    {
        return $this->belongsTo(RefProdi::class, 'id_prodi', 'id');
    }

    public function programStudi()
    {
        return $this->belongsTo(RefProdi::class, 'id_prodi', 'id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_periode_masuk', 'id_semester');
    }

    public function jenisDaftar()
    {
        return $this->belongsTo(JenisDaftar::class, 'id_jenis_daftar', 'id_jenis_daftar');
    }

    public function getIdJenisDaftarAttribute($value)
    {
        return trim($value);
    }

    public function getIdJalurDaftarAttribute($value)
    {
        return trim($value);
    }

    public function getIdPeriodeMasukAttribute($value)
    {
        return trim($value);
    }

    /**
     * Relasi ke Data Kehadiran Mahasiswa.
     */
    public function presensiMahasiswas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PresensiMahasiswa::class, 'riwayat_pendidikan_id');
    }

    /**
     * Relasi ke Peserta Kelas Kuliah.
     */
    public function pesertaKelasKuliahs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PesertaKelasKuliah::class, 'riwayat_pendidikan_id');
    }

    /**
     * Accessor: Hitung tingkat semester masa studi secara teoritis.
     * Menggunakan selisih waktu Periode Masuk vs Periode Aktif Akademik.
     * Cap maksimal 14 semester.
     */
    public function getTingkatSemesterAttribute()
    {
        $id_periode_masuk = $this->id_periode_masuk;
        $id_semester_aktif = getActiveSemesterId();

        if (! $id_periode_masuk || ! $id_semester_aktif) {
            return 1;
        }

        $tahunMasuk = (int) substr($id_periode_masuk, 0, 4);
        $sesiMasuk = (int) substr($id_periode_masuk, 4, 1);

        $tahunAktif = (int) substr($id_semester_aktif, 0, 4);
        $sesiAktif = (int) substr($id_semester_aktif, 4, 1);

        // Abaikan semester pendek (3) untuk kalkulasi tingkat (Sesi Pendek jalan di genap)
        $sesiMasuk = $sesiMasuk === 3 ? 2 : $sesiMasuk;
        $sesiAktif = $sesiAktif === 3 ? 2 : $sesiAktif;

        $selisihTahun = $tahunAktif - $tahunMasuk;
        $selisihSesi = $sesiAktif - $sesiMasuk;

        $semester = ($selisihTahun * 2) + $selisihSesi + 1;

        // Antisipasi mahasiswa DO otomatis/masa studi habis (Max S1: 14 smt)
        if ($semester > 14) {
            $semester = 14;
        }

        return max(1, $semester); // Minimal tingkat 1
    }
}
