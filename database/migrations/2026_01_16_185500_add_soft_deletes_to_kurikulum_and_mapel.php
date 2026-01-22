<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 1: Soft Delete untuk Kurikulum & Mata Pelajaran
 * 
 * Kurikulum bertindak sebagai "container" - saat di-archive,
 * semua mata pelajaran di dalamnya otomatis ter-filter dari query default.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add deleted_at to kurikulum
        if (!Schema::hasColumn('kurikulum', 'deleted_at')) {
            Schema::table('kurikulum', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to mata_pelajaran
        if (!Schema::hasColumn('mata_pelajaran', 'deleted_at')) {
            Schema::table('mata_pelajaran', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('kurikulum', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
