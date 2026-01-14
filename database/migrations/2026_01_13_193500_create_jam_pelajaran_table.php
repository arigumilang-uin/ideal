<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->integer('urutan')->default(1);
            $table->string('label', 50);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('jam_pelajaran')->insert([
            ['urutan' => 1, 'label' => 'Jam 1', 'jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 2, 'label' => 'Jam 2', 'jam_mulai' => '07:45', 'jam_selesai' => '08:30', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 3, 'label' => 'Jam 3', 'jam_mulai' => '08:30', 'jam_selesai' => '09:15', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 4, 'label' => 'Jam 4', 'jam_mulai' => '09:15', 'jam_selesai' => '10:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 5, 'label' => 'Jam 5', 'jam_mulai' => '10:15', 'jam_selesai' => '11:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 6, 'label' => 'Jam 6', 'jam_mulai' => '11:00', 'jam_selesai' => '11:45', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 7, 'label' => 'Jam 7', 'jam_mulai' => '12:30', 'jam_selesai' => '13:15', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['urutan' => 8, 'label' => 'Jam 8', 'jam_mulai' => '13:15', 'jam_selesai' => '14:00', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_pelajaran');
    }
};
