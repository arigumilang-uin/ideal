<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Jadwal Mengajar - Menghubungkan Guru → Mata Pelajaran → Kelas → Hari/Jam
     * Fleksibel: 1 guru bisa mengajar banyak kelas dengan mata pelajaran berbeda
     */
    public function up(): void
    {
        Schema::create('jadwal_mengajar', function (Blueprint $table) {
            $table->id();
            
            // Guru yang mengajar
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Mata pelajaran yang diajarkan
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            
            // Kelas yang diajar
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            
            // Hari dan waktu
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            
            // Semester dan tahun ajaran
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->string('tahun_ajaran', 10); // Format: 2025/2026
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['kelas_id', 'hari', 'is_active']);
            $table->index(['tahun_ajaran', 'semester', 'is_active']);
            
            // Unique constraint: tidak boleh ada jadwal bentrok untuk kelas yang sama
            $table->unique(
                ['kelas_id', 'hari', 'jam_mulai', 'semester', 'tahun_ajaran'],
                'jadwal_kelas_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_mengajar');
    }
};
