<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tambah unique constraint untuk user_id di jadwal_mengajar
     * Agar guru tidak bisa double-book (mengajar 2 kelas di waktu sama)
     */
    public function up(): void
    {
        Schema::table('jadwal_mengajar', function (Blueprint $table) {
            // Guru tidak bisa mengajar 2 kelas di waktu yang sama
            $table->unique(
                ['user_id', 'hari', 'jam_mulai', 'semester', 'tahun_ajaran'],
                'jadwal_guru_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_mengajar', function (Blueprint $table) {
            $table->dropUnique('jadwal_guru_unique');
        });
    }
};
