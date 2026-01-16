<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 2: Soft Delete untuk Periode Semester & Data Akademik
 * 
 * Periode Semester bertindak sebagai "container" untuk:
 * - template_jam
 * - jadwal_mengajar
 * - pertemuan
 * 
 * Saat periode di-archive, semua data akademik terkait otomatis ter-filter.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add deleted_at to periode_semester
        if (!Schema::hasColumn('periode_semester', 'deleted_at')) {
            Schema::table('periode_semester', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to template_jam
        if (!Schema::hasColumn('template_jam', 'deleted_at')) {
            Schema::table('template_jam', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to jadwal_mengajar
        if (!Schema::hasColumn('jadwal_mengajar', 'deleted_at')) {
            Schema::table('jadwal_mengajar', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to pertemuan
        if (!Schema::hasColumn('pertemuan', 'deleted_at')) {
            Schema::table('pertemuan', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('periode_semester', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('template_jam', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jadwal_mengajar', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pertemuan', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
