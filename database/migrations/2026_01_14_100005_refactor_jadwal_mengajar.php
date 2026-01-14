<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor jadwal_mengajar table
 * 
 * Perubahan:
 * - Hapus kolom hari, jam_mulai, jam_selesai, semester, tahun_ajaran (duplikasi)
 * - Tambah reference ke periode_semester_id dan template_jam_id
 * 
 * Benefit:
 * - Tidak ada duplikasi data waktu
 * - Perubahan template jam otomatis terupdate di jadwal
 * - Filter by periode lebih mudah
 */
return new class extends Migration
{
    public function up(): void
    {
        // Since this is a major refactor and data is dummy, we'll recreate the table
        // First, drop dependent tables (cascade will handle pertemuan)
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('pertemuan');
        Schema::dropIfExists('jadwal_mengajar');
        
        // Recreate with new structure
        Schema::create('jadwal_mengajar', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('periode_semester_id')
                  ->constrained('periode_semester')
                  ->cascadeOnDelete();
            
            $table->foreignId('template_jam_id')
                  ->constrained('template_jam')
                  ->cascadeOnDelete();
            
            $table->foreignId('kelas_id')
                  ->constrained('kelas')
                  ->cascadeOnDelete();
            
            $table->foreignId('mata_pelajaran_id')
                  ->constrained('mata_pelajaran')
                  ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                  ->comment('Guru yang mengajar')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Prevent duplicate jadwal for same slot
            $table->unique(
                ['periode_semester_id', 'template_jam_id', 'kelas_id'],
                'unique_jadwal_per_slot'
            );
        });
        
        // Recreate pertemuan table
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('jadwal_mengajar_id')
                  ->constrained('jadwal_mengajar')
                  ->cascadeOnDelete();
            
            $table->date('tanggal');
            $table->unsignedInteger('pertemuan_ke');
            
            $table->enum('status', ['aktif', 'selesai', 'kosong'])->default('aktif');
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            $table->index(['jadwal_mengajar_id', 'tanggal']);
            $table->unique(['jadwal_mengajar_id', 'tanggal'], 'unique_pertemuan');
        });
        
        // Recreate absensi table
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();
            
            $table->foreignId('jadwal_mengajar_id')
                  ->nullable()
                  ->constrained('jadwal_mengajar')
                  ->nullOnDelete();
            
            $table->foreignId('pertemuan_id')
                  ->nullable()
                  ->constrained('pertemuan')
                  ->nullOnDelete();
            
            $table->date('tanggal');
            
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alfa']);
            
            $table->text('keterangan')->nullable();
            
            $table->foreignId('pencatat_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->foreignId('riwayat_pelanggaran_id')
                  ->nullable()
                  ->constrained('riwayat_pelanggaran')
                  ->nullOnDelete();
            
            $table->timestamp('absen_at')->useCurrent();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['siswa_id', 'tanggal']);
            $table->index(['jadwal_mengajar_id', 'tanggal']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('pertemuan');
        Schema::dropIfExists('jadwal_mengajar');
        
        // Recreate old structure for rollback
        Schema::create('jadwal_mengajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->string('tahun_ajaran', 10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('tahun_ajaran');
        });
        
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_mengajar_id')->constrained('jadwal_mengajar');
            $table->date('tanggal');
            $table->unsignedInteger('pertemuan_ke');
            $table->enum('status', ['aktif', 'selesai', 'kosong'])->default('aktif');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->index('tanggal');
        });
        
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('jadwal_mengajar_id')->nullable()->constrained('jadwal_mengajar');
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alfa']);
            $table->text('keterangan')->nullable();
            $table->foreignId('pencatat_user_id')->constrained('users');
            $table->foreignId('riwayat_pelanggaran_id')->nullable()->constrained('riwayat_pelanggaran');
            $table->timestamp('absen_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
            $table->index('tanggal');
            $table->index('status');
        });
    }
};
