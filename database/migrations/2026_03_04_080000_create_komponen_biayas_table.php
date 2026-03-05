<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('komponen_biayas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_komponen', 20)->unique();
            $table->string('nama_komponen', 100);
            $table->string('kategori', 20)->default('per_semester');
            $table->decimal('nominal_standar', 16, 2)->default(0);
            $table->boolean('is_wajib_krs')->default(false);
            $table->boolean('is_wajib_ujian')->default(false);
            $table->uuid('id_prodi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_prodi')->references('id_prodi')->on('program_studis')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_biayas');
    }
};
