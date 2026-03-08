<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update enum tipe di tabel kuisioners
        // Di PostgreSQL, kita perlu mengubah tipe data kolom jika menggunakan ENUM yang didefinisikan secara eksplisit,
        // namun Laravel Migrations biasanya menggunakan CHECK constraint untuk ENUM di database tertentu.
        // Kita gunakan DB statement untuk mengubah constraint atau tipe data agar lebih aman.

        // Cek driver database
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL approach: Drop old constraint and add new one
            DB::statement("ALTER TABLE kuisioners DROP CONSTRAINT IF EXISTS kuisioners_tipe_check");
            DB::statement("ALTER TABLE kuisioners ADD CONSTRAINT kuisioners_tipe_check CHECK (tipe::text = ANY (ARRAY['pelayanan'::character varying, 'dosen'::character varying, 'ami'::character varying]::text[]))");
        } else {
            // MySQL/Others
            DB::statement("ALTER TABLE kuisioners MODIFY COLUMN tipe ENUM('pelayanan', 'dosen', 'ami') NOT NULL");
        }

        // 2. Tambah kolom id_user di kuisioner_submissions
        Schema::table('kuisioner_submissions', function (Blueprint $table) {
            $table->foreignId('id_user')->nullable()->after('id_dosen')->constrained('users')->onDelete('cascade');

            // Karena responden AMI non-mahasiswa, maka id_mahasiswa harus nullable
            $table->foreignId('id_mahasiswa')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuisioner_submissions', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
            $table->dropColumn('id_user');
            $table->foreignId('id_mahasiswa')->nullable(false)->change();
        });

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE kuisioners DROP CONSTRAINT IF EXISTS kuisioners_tipe_check");
            DB::statement("ALTER TABLE kuisioners ADD CONSTRAINT kuisioners_tipe_check CHECK (tipe::text = ANY (ARRAY['pelayanan'::character varying, 'dosen'::character varying]::text[]))");
        } else {
            DB::statement("ALTER TABLE kuisioners MODIFY COLUMN tipe ENUM('pelayanan', 'dosen') NOT NULL");
        }
    }
};
