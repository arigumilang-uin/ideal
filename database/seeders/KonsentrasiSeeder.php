<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Konsentrasi;
use App\Models\Jurusan;

/**
 * Konsentrasi Seeder
 * 
 * Seed data konsentrasi keahlian SMK Negeri 1
 * Konsentrasi adalah turunan dari Jurusan (Program Keahlian)
 * Biasanya siswa masuk konsentrasi di kelas XI
 */
class KonsentrasiSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping: jurusan_kode => [konsentrasi list]
        $konsentrasiData = [
            'ATP' => [
                ['nama' => 'Budidaya Tanaman Perkebunan', 'kode' => 'BTP'],
                ['nama' => 'Perbenihan dan Kultur Jaringan Tanaman', 'kode' => 'PKJT'],
            ],
            'APHP' => [
                ['nama' => 'Pengolahan Hasil Pertanian', 'kode' => 'PHP'],
                ['nama' => 'Pengawasan Mutu Hasil Pertanian', 'kode' => 'PMHP'],
            ],
            'ATU' => [
                ['nama' => 'Budidaya Ternak Unggas', 'kode' => 'BTU'],
            ],
            'TEB' => [
                ['nama' => 'Teknik Pembangkit Biomassa', 'kode' => 'TPB'],
            ],
            'AKL' => [
                ['nama' => 'Akuntansi', 'kode' => 'AK'],
                ['nama' => 'Keuangan Lembaga', 'kode' => 'KL'],
            ],
        ];

        $count = 0;
        foreach ($konsentrasiData as $kodeJurusan => $konsentrasiList) {
            $jurusan = Jurusan::where('kode_jurusan', $kodeJurusan)->first();
            
            if (!$jurusan) {
                $this->command->warn("  ⚠ Jurusan {$kodeJurusan} tidak ditemukan, skip konsentrasi");
                continue;
            }

            foreach ($konsentrasiList as $kons) {
                Konsentrasi::updateOrCreate(
                    [
                        'jurusan_id' => $jurusan->id,
                        'kode_konsentrasi' => $kons['kode'],
                    ],
                    [
                        'jurusan_id' => $jurusan->id,
                        'nama_konsentrasi' => $kons['nama'],
                        'kode_konsentrasi' => $kons['kode'],
                        'is_active' => true,
                        'deskripsi' => null,
                    ]
                );
                $count++;
            }
        }

        $this->command->info('✓ Konsentrasi seeded: ' . $count . ' konsentrasi');
    }
}
