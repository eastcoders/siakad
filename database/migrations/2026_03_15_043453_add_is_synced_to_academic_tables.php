<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->boolean('is_synced')->default(false)->after('id_feeder');
            $table->index(['is_synced', 'status_sinkronisasi']);
        });

        Schema::table('kurikulums', function (Blueprint $table) {
            $table->boolean('is_synced')->default(false)->after('id_feeder');
            $table->index(['is_synced', 'status_sinkronisasi']);
        });

        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->boolean('is_synced')->default(false)->after('id_feeder');
            $table->index(['is_synced', 'status_sinkronisasi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->dropIndex(['is_synced', 'status_sinkronisasi']);
            $table->dropColumn('is_synced');
        });

        Schema::table('kurikulums', function (Blueprint $table) {
            $table->dropIndex(['is_synced', 'status_sinkronisasi']);
            $table->dropColumn('is_synced');
        });

        Schema::table('matkul_kurikulums', function (Blueprint $table) {
            $table->dropIndex(['is_synced', 'status_sinkronisasi']);
            $table->dropColumn('is_synced');
        });
    }
};
