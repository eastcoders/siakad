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
        Schema::create('surat_permohonan_anggotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_surat_permohonan')->constrained('surat_permohonans')->onDelete('cascade');
            $table->foreignId('id_mahasiswa')->constrained('mahasiswas')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['id_surat_permohonan', 'id_mahasiswa'], 'unique_anggotas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_permohonan_anggotas');
    }
};
