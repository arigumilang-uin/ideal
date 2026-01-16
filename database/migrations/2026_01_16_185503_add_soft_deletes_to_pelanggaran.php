<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 4: Soft Delete untuk Data Tata Tertib
 * 
 * Kategori dan Jenis Pelanggaran perlu soft delete agar
 * historical riwayat pelanggaran tetap bisa menampilkan referensi.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add deleted_at to kategori_pelanggaran
        if (!Schema::hasColumn('kategori_pelanggaran', 'deleted_at')) {
            Schema::table('kategori_pelanggaran', function (Blueprint $table) {
                if (!Schema::hasColumn('kategori_pelanggaran', 'created_at')) {
                    $table->timestamps();
                }
                $table->softDeletes();
            });
        }

        // Add deleted_at to jenis_pelanggaran
        if (!Schema::hasColumn('jenis_pelanggaran', 'deleted_at')) {
            Schema::table('jenis_pelanggaran', function (Blueprint $table) {
                if (!Schema::hasColumn('jenis_pelanggaran', 'created_at')) {
                    $table->timestamps();
                }
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('kategori_pelanggaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });

        Schema::table('jenis_pelanggaran', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });
    }
};
