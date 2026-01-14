<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Konsentrasi;

/**
 * Kelas Seeder
 * 
 * Seed data kelas SMK Negeri 1
 * 
 * Catatan:
 * - Kelas X: biasanya belum masuk konsentrasi (konsentrasi_id = null)
 * - Kelas XI & XII: sudah masuk konsentrasi
 */
class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Kelas dengan format: [nama_kelas, tingkat, kode_jurusan, kode_konsentrasi (nullable)]
        $kelasData = [
            // === AKL ===
            ['nama' => 'X AKL 1', 'tingkat' => 'X', 'jurusan' => 'AKL', 'konsentrasi' => null],
            ['nama' => 'XI AKL 1', 'tingkat' => 'XI', 'jurusan' => 'AKL', 'konsentrasi' => 'AK'],
            ['nama' => 'XII AKL 1', 'tingkat' => 'XII', 'jurusan' => 'AKL', 'konsentrasi' => 'AK'],
            
            // === APHP ===
            ['nama' => 'X APHP 1', 'tingkat' => 'X', 'jurusan' => 'APHP', 'konsentrasi' => null],
            ['nama' => 'XI APHP 1', 'tingkat' => 'XI', 'jurusan' => 'APHP', 'konsentrasi' => 'PHP'],
            
            // === ATP ===
            ['nama' => 'X ATP 1', 'tingkat' => 'X', 'jurusan' => 'ATP', 'konsentrasi' => null],
            ['nama' => 'XI ATP 1', 'tingkat' => 'XI', 'jurusan' => 'ATP', 'konsentrasi' => 'BTP'],
            ['nama' => 'XI ATP 2', 'tingkat' => 'XI', 'jurusan' => 'ATP', 'konsentrasi' => 'PKJT'],
            
            // === ATU ===
            ['nama' => 'X ATU 1', 'tingkat' => 'X', 'jurusan' => 'ATU', 'konsentrasi' => null],
            
            // === TEB ===
            ['nama' => 'X TEB 1', 'tingkat' => 'X', 'jurusan' => 'TEB', 'konsentrasi' => null],
        ];

        $count = 0;
        foreach ($kelasData as $kelas) {
            $jurusan = Jurusan::where('kode_jurusan', $kelas['jurusan'])->first();
            
            if (!$jurusan) {
                $this->command->warn("  ⚠ Jurusan {$kelas['jurusan']} tidak ditemukan, skip kelas {$kelas['nama']}");
                continue;
            }

            // Cari konsentrasi jika ada
            $konsentrasiId = null;
            if ($kelas['konsentrasi']) {
                $konsentrasi = Konsentrasi::where('kode_konsentrasi', $kelas['konsentrasi'])
                    ->where('jurusan_id', $jurusan->id)
                    ->first();
                    
                if ($konsentrasi) {
                    $konsentrasiId = $konsentrasi->id;
                } else {
                    $this->command->warn("  ⚠ Konsentrasi {$kelas['konsentrasi']} tidak ditemukan untuk jurusan {$kelas['jurusan']}");
                }
            }

            Kelas::updateOrCreate(
                ['nama_kelas' => $kelas['nama']],
                [
                    'nama_kelas' => $kelas['nama'],
                    'tingkat' => $kelas['tingkat'],
                    'jurusan_id' => $jurusan->id,
                    'konsentrasi_id' => $konsentrasiId,
                    // wali_kelas_user_id will be set later by UserSeeder
                ]
            );
            $count++;
        }

        $this->command->info('✓ Kelas seeded: ' . $count . ' kelas');
    }
}
