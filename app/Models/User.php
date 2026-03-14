<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    public function dosen()
    {
        return $this->hasOne(Dosen::class, 'user_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'is_first_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class, 'user_id');
    }

    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'user_id');
    }

    /**
     * Relasi ke penugasan jabatan (Struktur Baru).
     */
    public function userJabatans()
    {
        return $this->hasMany(UserJabatan::class, 'user_id');
    }

    /**
     * Helper untuk mengecek jabatan aktif.
     */
    public function hasJabatan(string $kode): bool
    {
        return $this->userJabatans()
            ->where('is_active', true)
            ->whereHas('jabatan', function ($q) use ($kode) {
                $q->where('kode_role', $kode);
            })->exists();
    }

    /**
     * BACKWARD COMPATIBILITY ACCESSORS
     * Digunakan agar kode lama seperti $user->keuangan tetap berjalan.
     */
    public function getKeuanganAttribute()
    {
        return \App\Models\Keuangan::where('user_id', $this->id)->first();
    }

    public function getSarprasAttribute()
    {
        return \App\Models\Sarpras::where('user_id', $this->id)->first();
    }

    public function getBpmiAttribute()
    {
        return \App\Models\Bpmi::where('user_id', $this->id)->first();
    }

    public function getDirekturAttribute()
    {
        return \App\Models\Direktur::where('user_id', $this->id)->first();
    }

    public function getWakilDirekturAttribute()
    {
        return \App\Models\WakilDirektur::where('user_id', $this->id)->first();
    }

    public function getPersonaliaAttribute()
    {
        return \App\Models\Personalia::where('user_id', $this->id)->first();
    }

    public function getKemahasiswaanAttribute()
    {
        return \App\Models\Kemahasiswaan::where('user_id', $this->id)->first();
    }
}
