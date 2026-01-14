<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create tingkat_kurikulum table
 * 
 * Menentukan kurikulum yang digunakan oleh setiap tingkat (X, XI, XII)
 * pada periode semester tertentu.
 * 
 * Contoh:
 * - Periode Ganjil 2026/2027: Tingkat X → Kurikulum Merdeka
 * - Periode Ganjil 2026/2027: Tingkat XII → Kurikulum 2013
 * 
 * Ini memungkinkan transisi kurikulum secara gradual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tingkat_kurikulum', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('periode_semester_id')
                  ->constrained('periode_semester')
                  ->cascadeOnDelete();
            
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            
            $table->foreignId('kurikulum_id')
                  ->constrained('kurikulum')
                  ->cascadeOnDelete();
            
            $table->timestamps();
            
            // Satu tingkat hanya bisa punya satu kurikulum per periode
            $table->unique(['periode_semester_id', 'tingkat'], 'unique_tingkat_per_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tingkat_kurikulum');
    }
};
