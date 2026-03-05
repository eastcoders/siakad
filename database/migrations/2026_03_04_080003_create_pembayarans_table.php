<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kuitansi', 30)->nullable()->unique();
            $table->foreignId('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->decimal('jumlah_bayar', 16, 2);
            $table->date('tanggal_bayar');
            $table->string('bukti_bayar', 500);
            $table->string('status_verifikasi', 20)->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index('status_verifikasi');
            $table->index('tagihan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
