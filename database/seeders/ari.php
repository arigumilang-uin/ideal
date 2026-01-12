<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

/**
 * User Seeder
 * 
 * Seed data user SMK Negeri 1
 */
class ari extends Seeder
{
    public function run(): void
    {
        $roleOperator = Role::where('nama_role', 'Operator Sekolah')->first();
        $roleGuru = Role::where('nama_role', 'Guru')->first();

        $defaultPassword = Hash::make('password123');
        $createdCount = 0;
        User::updateOrCreate(
            ['username' => 'Ari Gumilang'],
            [
                'nama' => 'Operator Sekolah',
                'username' => 'Ari Gumilang',
                'email' => 'arigumilang271@gmail.com',
                'password' => $defaultPassword,
                'role_id' => $roleOperator?->id,
                'is_active' => true,
            ]
        );
        $createdCount++;

        $guruData = [
            'KJA',
            'WKJA',
            'KJB',
            'WKJB',
            'kepala sekolah',
            'waka',
        ];

        $guruCounter = 1;
        foreach ($guruData as $guru) {
            User::updateOrCreate(
                ['username' => $guru],
                [
                    'nama' => 'Guru',
                    'username' => $guru,
                    'email' => 'guru' . $guruCounter . '@smkn1.sch.id',
                    'password' => $defaultPassword,
                    'role_id' => $roleGuru?->id,
                    'is_active' => true,
                ]
            );
            $guruCounter++;
            $createdCount++;
        }


        $this->command->info('✓ Users seeded: ' . $createdCount . ' users');
        $this->command->info('  - Default password: password123');
    }

    
}
