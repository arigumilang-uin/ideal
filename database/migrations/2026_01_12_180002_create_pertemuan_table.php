<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Pertemuan - Instance pertemuan per tanggal real
     * Auto-generated dari template jadwal_mengajar berdasarkan periode semester
     */
    public function up(): void
    {
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            
            // Link ke template jadwal
            $table->foreignId('jadwal_mengajar_id')->constrained('jadwal_mengajar')->onDelete('cascade');
            
            // Tanggal real pertemuan
            $table->date('tanggal');
            
            // Pertemuan ke berapa (1, 2, 3, dst)
            $table->unsignedInteger('pertemuan_ke');
            
            // Status pertemuan
            $table->enum('status', ['aktif', 'selesai', 'kosong'])->default('aktif');
            
            // Keterangan (opsional, misal: "Libur Nasional")
            $table->text('keterangan')->nullable();
            
            $table->timestamps();
            
            // Satu jadwal hanya bisa punya 1 pertemuan per tanggal
            $table->unique(['jadwal_mengajar_id', 'tanggal'], 'pertemuan_jadwal_tanggal_unique');
            
            // Index untuk query pertemuan hari ini
            $table->index('tanggal');
            $table->index(['jadwal_mengajar_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
