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
        // 1. Create 'jabatans' table
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan');
            $table->string('kode_role')->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create 'user_jabatans' table
        Schema::create('user_jabatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Seed Master Data Jabatans & Roles
        $masterJabatans = [
            ['nama' => 'Direktur', 'kode' => 'Direktur'],
            ['nama' => 'Wakil Direktur', 'kode' => 'Wakil Direktur'],
            ['nama' => 'Keuangan', 'kode' => 'Keuangan'],
            ['nama' => 'Sarana dan Prasarana', 'kode' => 'Sarpras'],
            ['nama' => 'BPMI', 'kode' => 'BPMI'],
            ['nama' => 'Personalia', 'kode' => 'Personalia'],
            ['nama' => 'Kemahasiswaan', 'kode' => 'Kemahasiswaan'],
            ['nama' => 'Kepegawaian', 'kode' => 'Kepegawaian'],
        ];

        foreach ($masterJabatans as $m) {
            $jabatanId = DB::table('jabatans')->insertGetId([
                'nama_jabatan' => $m['nama'],
                'kode_role' => $m['kode'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Ensure Spatie Role exists
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $m['kode']]);
        }

        // 4. ETL: Migrate Data from Old Tables
        $mapping = [
            'direkturs' => ['kode' => 'Direktur', 'dosen_col' => 'id_dosen'],
            'wakil_direkturs' => ['kode' => 'Wakil Direktur', 'dosen_col' => 'id_dosen'],
            'bpmis' => ['kode' => 'BPMI', 'dosen_col' => 'id_dosen'],
            'keuangans' => ['kode' => 'Keuangan'],
            'sarpras' => ['kode' => 'Sarpras'],
            'personalias' => ['kode' => 'Personalia'],
            'kemahasiswaans' => ['kode' => 'Kemahasiswaan'],
        ];

        foreach ($mapping as $table => $conf) {
            if (!Schema::hasTable($table))
                continue;

            $jabatan = DB::table('jabatans')->where('kode_role', $conf['kode'])->first();
            $oldData = DB::table($table)->get();

            foreach ($oldData as $row) {
                $userId = null;

                // Priority: check if id_dosen exists
                if (isset($row->id_dosen) && !empty($row->id_dosen)) {
                    $dosen = DB::table('dosens')->where('id', $row->id_dosen)->first();
                    $userId = $dosen?->user_id;
                }
                // Then check if id_pegawai exists
                elseif (isset($row->id_pegawai) && !empty($row->id_pegawai)) {
                    $pegawai = DB::table('pegawais')->where('id', $row->id_pegawai)->first();
                    $userId = $pegawai?->user_id;
                }

                if ($userId) {
                    // Check for existing assignment to avoid double roles
                    $exists = DB::table('user_jabatans')
                        ->where('user_id', $userId)
                        ->where('jabatan_id', $jabatan->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('user_jabatans')->insert([
                            'user_id' => $userId,
                            'jabatan_id' => $jabatan->id,
                            'nomor_sk' => $row->sk_tugas ?? null,
                            'is_active' => $row->is_active ?? true,
                            'created_at' => $row->created_at ?? now(),
                            'updated_at' => $row->updated_at ?? now(),
                        ]);

                        // Assign Spatie Role
                        $user = \App\Models\User::find($userId);
                        if ($user) {
                            $user->assignRole($conf['kode']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_jabatans');
        Schema::dropIfExists('jabatans');
    }
};
