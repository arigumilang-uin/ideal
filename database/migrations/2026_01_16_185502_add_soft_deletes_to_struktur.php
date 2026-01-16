<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 3: Soft Delete untuk Data Struktur Organisasi
 * 
 * Termasuk handling unique constraint untuk users:
 * - username, email, google_id akan di-suffix saat soft delete
 *   agar tidak bentrok dengan user baru
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add deleted_at to users
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to jurusan (need timestamps first)
        if (!Schema::hasColumn('jurusan', 'deleted_at')) {
            Schema::table('jurusan', function (Blueprint $table) {
                // Jurusan doesn't have timestamps, add them
                if (!Schema::hasColumn('jurusan', 'created_at')) {
                    $table->timestamps();
                }
                $table->softDeletes();
            });
        }

        // Add deleted_at to konsentrasi
        if (!Schema::hasColumn('konsentrasi', 'deleted_at')) {
            Schema::table('konsentrasi', function (Blueprint $table) {
                if (!Schema::hasColumn('konsentrasi', 'created_at')) {
                    $table->timestamps();
                }
                $table->softDeletes();
            });
        }

        // Add deleted_at to kelas
        if (!Schema::hasColumn('kelas', 'deleted_at')) {
            Schema::table('kelas', function (Blueprint $table) {
                if (!Schema::hasColumn('kelas', 'created_at')) {
                    $table->timestamps();
                }
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });

        Schema::table('konsentrasi', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });
    }
};
