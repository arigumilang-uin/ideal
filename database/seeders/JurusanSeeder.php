<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jurusan;

/**
 * Jurusan Seeder
 * 
 * Seed data jurusan SMK Negeri 1
 */
class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $jurusan = [
            [
                'nama_jurusan' => 'Agribisnis Tanaman Perkebunan',
                'kode_jurusan' => 'ATP',
            ],
            [
                'nama_jurusan' => 'Agribisnis Pengolahan Hasil Pertanian',
                'kode_jurusan' => 'APHP',
            ],
            [
                'nama_jurusan' => 'Agribisnis Ternak Unggas',
                'kode_jurusan' => 'ATU',
            ],
            [
                'nama_jurusan' => 'Teknik Energi Biomassa',
                'kode_jurusan' => 'TEB',
            ],
            [
                'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',
                'kode_jurusan' => 'AKL',
            ],

            [
                'nama_jurusan' => 'Jurusan A',
                'kode_jurusan' => 'JA',                
            ],
            [
                'nama_jurusan' => 'Jurusan B',
                'kode_jurusan' => 'JB',
            ],
        ];

        foreach ($jurusan as $j) {
            Jurusan::updateOrCreate(
                ['kode_jurusan' => $j['kode_jurusan']],
                $j
            );
        }

        $this->command->info('✓ Jurusan seeded: ' . count($jurusan) . ' jurusan');
    }
}
