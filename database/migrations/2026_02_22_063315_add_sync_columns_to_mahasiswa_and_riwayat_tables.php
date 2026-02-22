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
        // Update Mahasiswas
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi')->default('created_local')
                ->comment('synced, created_local, updated_local, deleted_local, push_failed');
            $table->enum('sync_action', ['insert', 'update', 'delete'])->default('insert');
            $table->boolean('is_local_change')->default(false);
            $table->boolean('is_deleted_server')->default(false); // Soft delete marker from server
            $table->boolean('is_deleted_local')->default(false); // Soft delete marker queueing push
            $table->timestamp('last_push_at')->nullable();
            $table->text('sync_error_message')->nullable();
        });

        // Update Riwayat Pendidikans
        Schema::table('riwayat_pendidikans', function (Blueprint $table) {
            $table->enum('sumber_data', ['server', 'lokal'])->default('lokal');
            $table->string('status_sinkronisasi')->default('created_local')
                ->comment('synced, created_local, updated_local, deleted_local, push_failed');
            $table->enum('sync_action', ['insert', 'update', 'delete'])->default('insert');
            $table->boolean('is_local_change')->default(false);
            $table->boolean('is_deleted_server')->default(false); // Soft delete marker from server
            $table->boolean('is_deleted_local')->default(false); // Soft delete marker queueing push
            $table->timestamp('last_push_at')->nullable();
            $table->text('sync_error_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'sumber_data',
            'status_sinkronisasi',
            'sync_action',
            'is_local_change',
            'is_deleted_server',
            'is_deleted_local',
            'last_push_at',
            'sync_error_message'
        ];

        Schema::table('mahasiswas', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });

        Schema::table('riwayat_pendidikans', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
