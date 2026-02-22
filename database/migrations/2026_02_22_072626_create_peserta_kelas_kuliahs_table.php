<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            // Nullable because it might not exist yet from server for local students
            $table->uuid('id_registrasi_mahasiswa')->nullable()->change();

            // Bridging point to local system for offline-first sync
            $table->foreignId('riwayat_pendidikan_id')
                ->nullable()
                ->after('id_registrasi_mahasiswa')
                ->constrained('riwayat_pendidikans')
                ->nullOnDelete()
                ->comment('Lokal ID untuk sinkronisasi tertunda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peserta_kelas_kuliah', function (Blueprint $table) {
            $table->dropForeign(['riwayat_pendidikan_id']);
            $table->dropColumn('riwayat_pendidikan_id');
            $table->uuid('id_registrasi_mahasiswa')->nullable(false)->change();
        });
    }
};
