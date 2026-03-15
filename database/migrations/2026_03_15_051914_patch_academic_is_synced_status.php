<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Patch Mata Kuliah
        \Illuminate\Support\Facades\DB::table('mata_kuliahs')
            ->where('status_sinkronisasi', 'synced')
            ->update(['is_synced' => true]);

        // Patch Kurikulum
        \Illuminate\Support\Facades\DB::table('kurikulums')
            ->where('status_sinkronisasi', 'synced')
            ->update(['is_synced' => true]);

        // Patch Matkul Kurikulum (Pivot)
        \Illuminate\Support\Facades\DB::table('matkul_kurikulums')
            ->where('status_sinkronisasi', 'synced')
            ->update(['is_synced' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse logic needed for a data patch, or we could reset is_synced but that might be risky
    }
};
