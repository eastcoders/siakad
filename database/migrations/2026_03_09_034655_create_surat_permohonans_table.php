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
        Schema::create('surat_permohonans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswas')->onDelete('cascade');
            $table->string('id_semester', 5)->index(); // Foreign key manually handled for academic consistency

            $table->string('nomor_tiket')->unique();
            $table->string('tipe_surat'); // cuti_kuliah, aktif_kuliah, etc.
            $table->string('nomor_surat')->nullable();

            $table->enum('status', ['pending', 'validasi', 'disetujui', 'ditolak', 'selesai'])->default('pending');
            $table->text('catatan_admin')->nullable();

            // Generic fields for common data
            $table->string('instansi_tujuan')->nullable();
            $table->text('alamat_instansi')->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->text('alasan')->nullable();

            $table->string('file_pendukung')->nullable();
            $table->string('file_final')->nullable();
            $table->timestamp('tgl_pengajuan')->useCurrent();

            // System & Sync Pillars
            $table->string('external_id')->nullable()->index();
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi')->default('created_local')->index();
            $table->boolean('is_deleted_server')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('sync_error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_permohonans');
    }
};
