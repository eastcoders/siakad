<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tagihan', 30)->unique();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('id_semester');
            $table->decimal('total_tagihan', 16, 2)->default(0);
            $table->decimal('total_potongan', 16, 2)->default(0);
            $table->decimal('total_dibayar', 16, 2)->default(0);
            $table->string('status', 20)->default('belum_bayar');
            $table->text('catatan_potongan')->nullable();
            $table->timestamps();

            $table->foreign('id_semester')->references('id_semester')->on('semesters')->cascadeOnDelete();
            $table->unique(['id_mahasiswa', 'id_semester']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
