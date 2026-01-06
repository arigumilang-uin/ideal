<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mengubah kolom email menjadi nullable.
     * User tidak wajib memiliki email, terutama untuk akun yang dibuat otomatis
     * (Wali Murid, Wali Kelas, Kaprodi auto-generated).
     * 
     * JUGA: Membersihkan email asal-asalan yang sudah ada.
     */
    public function up(): void
    {
        // Step 1: Drop unique constraint dulu 
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        // Step 2: Ubah kolom menjadi nullable DULU
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Step 3: Baru hapus email palsu yang sudah ada (@walimurid.local, @no-reply.local)
        DB::table('users')
            ->where('email', 'like', '%@walimurid.local')
            ->orWhere('email', 'like', '%@no-reply.local')
            ->update(['email' => null]);

        // Step 4: Tambahkan unique constraint kembali (NULL tidak dihitung duplikat di MySQL)
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: buat email NOT NULL lagi (perlu isi default dulu)
        DB::table('users')
            ->whereNull('email')
            ->update(['email' => DB::raw("CONCAT(username, '@placeholder.local')")]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
