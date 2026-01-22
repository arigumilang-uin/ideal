<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * User Seeder
 * 
 * Seed data user SMK Negeri 1
 * 
 * FORMAT:
 * - nama = Jabatan/Role (contoh: "Kepala Sekolah", "Kaprodi ATP")
 * - username = Nama Orangnya (contoh: "Salmiah, S.Pd.MM")
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // GET ROLES
        // =====================================================================
        $roles = [
            'kepsek' => Role::where('nama_role', 'Kepala Sekolah')->first(),
            'operator' => Role::where('nama_role', 'Operator Sekolah')->first(),
            'developer' => Role::where('nama_role', 'Developer')->first(),
            'waka_kesiswaan' => Role::where('nama_role', 'Waka Kesiswaan')->first(),
            'waka_sarana' => Role::where('nama_role', 'Waka Sarana')->first(),
            'kaprodi' => Role::where('nama_role', 'Kaprodi')->first(),
            'wali_kelas' => Role::where('nama_role', 'Wali Kelas')->first(),
            'guru' => Role::where('nama_role', 'Guru')->first(),
        ];

        $defaultPassword = Hash::make('password123');
        $now = Carbon::now();
        $createdCount = 0;

        // =====================================================================
        // 1. DEVELOPER (untuk testing)
        // =====================================================================
        User::updateOrCreate(
            ['username' => 'developer'],
            [
                'nama' => 'Developer',
                'username' => 'developer',
                'email' => 'dev@smkn1.sch.id',
                'password' => $defaultPassword,
                'role_id' => $roles['developer']?->id,
                'is_active' => true,
                'profile_completed_at' => $now,
            ]
        );
        $createdCount++;

        // =====================================================================
        // 2. KEPALA SEKOLAH
        // =====================================================================
        User::updateOrCreate(
            ['username' => 'Salmiah, S.Pd.MM'],
            [
                'nama' => 'Kepala Sekolah',
                'username' => 'Salmiah, S.Pd.MM',
                'email' => 'kepsek@smkn1.sch.id',
                'password' => $defaultPassword,
                'role_id' => $roles['kepsek']?->id,
                'is_active' => true,
                'profile_completed_at' => $now,
            ]
        );
        $createdCount++;

        // =====================================================================
        // 3. OPERATOR
        // =====================================================================
        User::updateOrCreate(
            ['username' => 'Muhd. Bima Satryo. F, S.Kom'],
            [
                'nama' => 'Operator',
                'username' => 'Muhd. Bima Satryo. F, S.Kom',
                'email' => 'operator@smkn1.sch.id',
                'password' => $defaultPassword,
                'role_id' => $roles['operator']?->id,
                'is_active' => true,
                'profile_completed_at' => $now,
            ]
        );
        $createdCount++;

        // =====================================================================
        // 4. WAKA KESISWAAN
        // =====================================================================
        User::updateOrCreate(
            ['username' => 'Nunung Agus Supriyanto, S.Pd'],
            [
                'nama' => 'Waka Kesiswaan',
                'username' => 'Nunung Agus Supriyanto, S.Pd',
                'email' => 'wakakesiswaan@smkn1.sch.id',
                'password' => $defaultPassword,
                'role_id' => $roles['waka_kesiswaan']?->id,
                'is_active' => true,
                'profile_completed_at' => $now,
            ]
        );
        $createdCount++;

        // =====================================================================
        // 5. WAKA SARANA
        // =====================================================================
        User::updateOrCreate(
            ['username' => "U'ud Khusnul Khamidah, SP"],
            [
                'nama' => 'Waka Sarana',
                'username' => "U'ud Khusnul Khamidah, SP",
                'email' => 'wakasarana@smkn1.sch.id',
                'password' => $defaultPassword,
                'role_id' => $roles['waka_sarana']?->id,
                'is_active' => true,
                'profile_completed_at' => $now,
            ]
        );
        $createdCount++;

        // =====================================================================
        // 6. KAPRODI
        // =====================================================================
        $kaprodiData = [
            ['username' => 'Dharma Siburian, S.P', 'jurusan' => 'ATP'],
            ['username' => 'Suyetmi Zentimer, S.TP', 'jurusan' => 'APHP'],
            ['username' => 'Asrori Naim, S.Pt', 'jurusan' => 'ATU'],
            ['username' => 'Dorta Simanjuntak, S.Pd', 'jurusan' => 'TEB'],
            ['username' => 'Devi Hendria, S.E', 'jurusan' => 'AKL'],
        ];

        foreach ($kaprodiData as $kp) {
            $jurusan = Jurusan::where('kode_jurusan', $kp['jurusan'])->first();
            
            $user = User::updateOrCreate(
                ['username' => $kp['username']],
                [
                    'nama' => 'Kaprodi ' . $kp['jurusan'],
                    'username' => $kp['username'],
                    'email' => strtolower($kp['jurusan']) . '.kaprodi@smkn1.sch.id',
                    'password' => $defaultPassword,
                    'role_id' => $roles['kaprodi']?->id,
                    'is_active' => true,
                    'profile_completed_at' => $now,
                ]
            );

            // Assign ke jurusan
            if ($jurusan) {
                $jurusan->update(['kaprodi_user_id' => $user->id]);
            }
            $createdCount++;
        }

        // =====================================================================
        // 7. WALI KELAS
        // =====================================================================
        $waliKelasData = [
            ['username' => 'Marliana, S.Pd', 'kelas' => 'X AKL 1'],
            ['username' => 'Khairunnisa Harahap, S.Pd', 'kelas' => 'XI AKL 1'],
            ['username' => 'Bella Eka Febriyanti, S.Pd', 'kelas' => 'XII AKL 1'],
            ['username' => 'Titis Solichah, S.Pd', 'kelas' => 'X APHP 1'],
            ['username' => 'Amalan Subekti, S.TP', 'kelas' => 'XI APHP 1'],
            ['username' => 'Mardiana BR Sembiring, SP', 'kelas' => 'X ATP 1'],
            ['username' => 'Meliana Dongoran, S.Pd', 'kelas' => 'XI ATP 1'],
            ['username' => 'Muhammad Rahimi, S.Pd', 'kelas' => 'XI ATP 2'],
            ['username' => 'Padmi Erizal, S.Pt', 'kelas' => 'X ATU 1'],
            ['username' => 'Bagus Friyanto, S.Kom', 'kelas' => 'X TEB 1'],
        ];

        foreach ($waliKelasData as $wk) {
            $kelas = Kelas::where('nama_kelas', $wk['kelas'])->first();
            
            $user = User::updateOrCreate(
                ['username' => $wk['username']],
                [
                    'nama' => 'Wali Kelas ' . $wk['kelas'],
                    'username' => $wk['username'],
                    'email' => strtolower(str_replace(' ', '', $wk['kelas'])) . '.wali@smkn1.sch.id',
                    'password' => $defaultPassword,
                    'role_id' => $roles['wali_kelas']?->id,
                    'is_active' => true,
                    'profile_completed_at' => $now,
                ]
            );

            // Assign ke kelas
            if ($kelas) {
                $kelas->update(['wali_kelas_user_id' => $user->id]);
            } else {
                $this->command->warn("  ⚠ Kelas {$wk['kelas']} tidak ditemukan untuk wali kelas {$wk['username']}");
            }
            $createdCount++;
        }

        // =====================================================================
        // 8. GURU (Tanpa Wali Kelas)
        // =====================================================================
        $guruData = [
            'Ari Lestari, SP',
            'Abu Khoeri, S.PdI',
            'Efendy A, S.Pd',
            'Hari Siswanto, S.Pd.,MH',
            'Endang Niken Larasati, SP',
            'Hari Supatmi, SP',
            'Nuraini, SS',
            'Sunarsih, S.Si',
            'Suwito, S.TP',
            'Margaretha, S.Pd.K',
            'Lidia Fitri, S.Pd',
            'Siwi Susilowati, S.Pt',
            'Muhammad Fadli Hidayatullah, S.Pd',
            'Anggres Intan Pratiwi, S.Pd',
            'Zulfahmi, S.A.P',
            'Rahmadhany',
            'Nurwati',
            'Erna Heryana, S.Kom',
            'Abu Sufyan',
            'Jono',
            'Poniran',
            'Budi Swito',
            'Nur Halizah Dwi Ananda Pangesti',
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
                    'role_id' => $roles['guru']?->id,
                    'is_active' => true,
                    'profile_completed_at' => $now,
                ]
            );
            $guruCounter++;
            $createdCount++;
        }

        $this->command->info('✓ Users seeded: ' . $createdCount . ' users');
        $this->command->info('  - Default password: password123');
    }
}
