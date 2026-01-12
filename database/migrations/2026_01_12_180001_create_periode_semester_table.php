<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Periode Semester - Konfigurasi tanggal awal/akhir semester
     * Diinput oleh operator karena setiap sekolah bisa berbeda
     */
    public function up(): void
    {
        Schema::create('periode_semester', function (Blueprint $table) {
            $table->id();
            $table->string('nama_periode', 50); // "Semester Ganjil 2025/2026"
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->string('tahun_ajaran', 10); // "2025/2026"
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            // Hanya boleh ada 1 periode aktif
            $table->unique(['semester', 'tahun_ajaran'], 'periode_semester_unique');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_semester');
    }
};
