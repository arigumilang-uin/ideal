<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create template_jam table (replaces jam_pelajaran)
 * 
 * Template slot waktu per periode semester dan per hari.
 * Ini memungkinkan:
 * - Format jam berbeda antar periode (tahun ajaran berbeda)
 * - Format jam berbeda antar hari (Senin 8 slot, Jumat 6 slot)
 * - Tracking slot non-pelajaran (istirahat, ishoma, upacara)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop old global jam_pelajaran table
        Schema::dropIfExists('jam_pelajaran');
        
        // Create new template_jam with more flexibility
        Schema::create('template_jam', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('periode_semester_id')
                  ->constrained('periode_semester')
                  ->cascadeOnDelete();
            
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            
            $table->tinyInteger('urutan')->unsigned();  // 1, 2, 3, 4...
            
            $table->string('label', 50);                // "Jam Ke-1", "Istirahat", "Ishoma"
            
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            
            $table->enum('tipe', ['pelajaran', 'istirahat', 'ishoma', 'upacara', 'lainnya'])
                  ->default('pelajaran');
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Unique slot per hari per periode
            $table->unique(['periode_semester_id', 'hari', 'urutan'], 'unique_slot_per_hari');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_jam');
        
        // Recreate old jam_pelajaran for rollback
        Schema::create('jam_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->integer('urutan')->default(1);
            $table->string('label', 50);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
