<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Modifikasi tabel absensi:
     * - Ganti jadwal_mengajar_id → pertemuan_id
     * - Absensi sekarang terhubung ke pertemuan spesifik (tanggal real)
     */
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Tambah kolom pertemuan_id
            $table->foreignId('pertemuan_id')
                  ->nullable()
                  ->after('jadwal_mengajar_id')
                  ->constrained('pertemuan')
                  ->onDelete('cascade');
            
            // Update unique constraint untuk menggunakan pertemuan_id
            // Drop old unique constraint jika ada
            $table->dropUnique('absensi_siswa_jadwal_tanggal_unique');
            
            // Add new unique constraint
            $table->unique(['siswa_id', 'pertemuan_id'], 'absensi_siswa_pertemuan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('absensi_siswa_pertemuan_unique');
            
            // Drop foreign key and column
            $table->dropForeign(['pertemuan_id']);
            $table->dropColumn('pertemuan_id');
            
            // Restore old unique constraint
            $table->unique(['siswa_id', 'jadwal_mengajar_id', 'tanggal'], 'absensi_siswa_jadwal_tanggal_unique');
        });
    }
};
