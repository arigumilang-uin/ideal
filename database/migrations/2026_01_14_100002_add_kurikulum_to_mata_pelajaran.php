<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add kurikulum_id to mata_pelajaran table
 * 
 * Setiap mata pelajaran sekarang terikat ke kurikulum tertentu.
 * Ini memungkinkan mapel yang sama (misal: Matematika) ada di multiple kurikulum
 * dengan karakteristik berbeda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            // Add kurikulum FK after id
            $table->foreignId('kurikulum_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('kurikulum')
                  ->nullOnDelete();
            
            // Add kelompok mapel (A=Umum, B=Kejuruan, C=Pilihan/Muatan Lokal)
            $table->enum('kelompok', ['A', 'B', 'C'])
                  ->nullable()
                  ->after('kode_mapel')
                  ->comment('A=Umum, B=Kejuruan, C=Pilihan/Muatan Lokal');
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['kurikulum_id']);
            $table->dropColumn(['kurikulum_id', 'kelompok']);
        });
    }
};
