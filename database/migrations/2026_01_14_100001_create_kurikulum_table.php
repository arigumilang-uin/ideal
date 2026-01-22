<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create kurikulum table
 * 
 * Master data kurikulum yang digunakan di sekolah.
 * Contoh: Kurikulum 2013, Kurikulum Merdeka, Kurikulum Merdeka SMK
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kurikulum', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();        // K13, MERDEKA, MERDEKA_SMK
            $table->string('nama', 100);                  // Kurikulum 2013, Kurikulum Merdeka
            $table->text('deskripsi')->nullable();
            $table->year('tahun_berlaku')->nullable();    // 2013, 2022
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kurikulum');
    }
};
