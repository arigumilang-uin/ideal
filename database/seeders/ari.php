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
            'Devi, S.Pd',
            'Agus, S.Pd',
            'Budi, S.Pd',
            'Cindy, S.Pd',
            'Dewi, S.Pd',
            'Eka, S.Pd',
            'Fajar, S.Pd',
            'Jono, S.Pd., M.Pd',
            'Kurniawan, S.Pd., M.Pd',
            'Lina, S.Pd., M.Pd',
            'Maman, S.Pd., M.Pd',
            'Nina, S.Pd., M.Pd',
            'Oke, S.Pd., M.Pd',
            'Pandu, S.Pd., M.Pd',
            'Qura, S.Pd., M.Pd',
            'Rudi, S.Pd., M.Pd',
            'Siti, S.Pd., M.Pd',
            'Tono, S.Pd., M.Pd',
            'Udin, S.Pd., M.Pd',
            'Vika, S.Pd., M.Pd',
            'Wati, S.Pd., M.Pd',
            'Xander, S.Pd., M.Pd',
            'Yuni, S.Pd., M.Pd',
            'Zaki, S.Pd., M.Pd',
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
