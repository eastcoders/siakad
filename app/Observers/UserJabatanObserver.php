<?php

namespace App\Observers;

use App\Models\UserJabatan;
use Illuminate\Support\Facades\Log;

class UserJabatanObserver
{
    /**
     * Handle the UserJabatan "created" event.
     */
    public function created(UserJabatan $userJabatan): void
    {
        if ($userJabatan->is_active) {
            $this->assignRole($userJabatan);
        }
    }

    /**
     * Handle the UserJabatan "updated" event.
     */
    public function updated(UserJabatan $userJabatan): void
    {
        // Jika status berubah menjadi aktif
        if ($userJabatan->is_active && !$userJabatan->getOriginal('is_active')) {
            $this->assignRole($userJabatan);
        }
        // Jika status berubah menjadi tidak aktif
        elseif (!$userJabatan->is_active && $userJabatan->getOriginal('is_active')) {
            $this->removeRole($userJabatan);
        }
    }

    /**
     * Handle the UserJabatan "deleted" event.
     */
    public function deleted(UserJabatan $userJabatan): void
    {
        if ($userJabatan->is_active) {
            $this->removeRole($userJabatan);
        }
    }

    /**
     * Berikan Role Spatie ke User.
     */
    protected function assignRole(UserJabatan $userJabatan)
    {
        $user = $userJabatan->user;
        $roleName = $userJabatan->jabatan->kode_role;

        if ($user && $roleName) {
            $user->assignRole($roleName);
            Log::info("SYNC_SUCCESS: [UserJabatan] Role {$roleName} diberikan ke User ID {$user->id}");
        }
    }

    /**
     * Cabut Role Spatie dari User (jika tidak ada jabatan aktif lain dengan role sama).
     */
    protected function removeRole(UserJabatan $userJabatan)
    {
        $user = $userJabatan->user;
        $roleName = $userJabatan->jabatan->kode_role;

        if ($user && $roleName) {
            // Cek apakah masih ada jabatan lain yang aktif dengan role yang sama
            $stillHas = UserJabatan::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereHas('jabatan', function ($q) use ($roleName) {
                    $q->where('kode_role', $roleName);
                })
                ->where('id', '!=', $userJabatan->id)
                ->exists();

            if (!$stillHas) {
                $user->removeRole($roleName);
                Log::info("SYNC_SUCCESS: [UserJabatan] Role {$roleName} dicabut dari User ID {$user->id}");
            }
        }
    }
}
