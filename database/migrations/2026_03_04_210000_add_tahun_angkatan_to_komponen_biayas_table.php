<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('komponen_biayas', function (Blueprint $table) {
            $table->string('tahun_angkatan', 4)->nullable()->after('id_prodi')
                ->comment('Tahun masuk mahasiswa (misal 2023). Null = semua angkatan.');
            $table->index('tahun_angkatan');
        });
    }

    public function down(): void
    {
        Schema::table('komponen_biayas', function (Blueprint $table) {
            $table->dropIndex(['tahun_angkatan']);
            $table->dropColumn('tahun_angkatan');
        });
    }
};
