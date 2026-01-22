<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Absensi - Catatan kehadiran siswa per sesi/per hari
     * Integrasi dengan Pelanggaran: 
     * - Status ALFA akan otomatis mencatat ke riwayat_pelanggaran
     * - riwayat_pelanggaran_id menyimpan referensi ke pelanggaran yang dibuat
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            
            // Siswa yang diabsen
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            
            // Jadwal mengajar (nullable untuk absensi harian tanpa jadwal spesifik)
            $table->foreignId('jadwal_mengajar_id')->nullable()->constrained('jadwal_mengajar')->onDelete('set null');
            
            // Tanggal absensi
            $table->date('tanggal');
            
            // Status kehadiran
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alfa']);
            
            // Keterangan tambahan (untuk Sakit/Izin bisa berisi alasan)
            $table->text('keterangan')->nullable();
            
            // Guru/Staff yang mencatat absensi
            $table->foreignId('pencatat_user_id')->constrained('users')->onDelete('cascade');
            
            // Link ke pelanggaran (jika status = Alfa)
            $table->foreignId('riwayat_pelanggaran_id')
                  ->nullable()
                  ->constrained('riwayat_pelanggaran')
                  ->onDelete('set null');
            
            // Waktu pencatatan
            $table->timestamp('absen_at')->useCurrent();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['siswa_id', 'tanggal']);
            $table->index(['jadwal_mengajar_id', 'tanggal']);
            $table->index(['status', 'tanggal']);
            $table->index('tanggal');
            
            // Unique: 1 siswa hanya bisa diabsen 1x per jadwal per hari
            $table->unique(
                ['siswa_id', 'jadwal_mengajar_id', 'tanggal'],
                'absensi_siswa_jadwal_tanggal_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
