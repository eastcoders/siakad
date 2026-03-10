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
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Drop unique constraint on email
            // In Postgres, we may need to drop it by name if it was created as unique()
            // Usually it's mahasiswas_email_unique
            $table->dropUnique('mahasiswas_email_unique');

            // Make fields nullable
            $table->string('nik', 16)->nullable()->change();
            $table->char('nisn', 10)->nullable()->change();
            $table->string('tempat_lahir', 32)->nullable()->change();
            $table->string('nama_ibu_kandung', 100)->nullable()->change();
            $table->char('id_wilayah', 8)->nullable()->change();
            $table->string('kelurahan', 60)->nullable()->change();
            $table->string('handphone', 20)->nullable()->change();
            $table->string('email', 60)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->string('email', 60)->unique()->change();
            $table->string('nik', 16)->unique()->change();
            $table->char('nisn', 10)->unique()->change();
            // Note: Reverting nullable to non-nullable might fail if there's null data
        });
    }
};
