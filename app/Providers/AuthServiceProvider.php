<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Policies\SiswaPolicy;
use App\Policies\UserPolicy;
use App\Policies\JurusanPolicy;
use App\Policies\KelasPolicy;
use App\Policies\RiwayatPelanggaranPolicy;
use App\Policies\TindakLanjutPolicy;

/**
 * Auth Service Provider
 * 
 * Register authorization policies untuk setiap model.
 * Policies define who can perform actions (view, create, update, delete, approve).
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Core domain policies
        Siswa::class => SiswaPolicy::class,
        User::class => UserPolicy::class,
        
        // Master data policies
        Jurusan::class => JurusanPolicy::class,
        Kelas::class => KelasPolicy::class,
        
        // Transaction policies
        RiwayatPelanggaran::class => RiwayatPelanggaranPolicy::class,
        TindakLanjut::class => TindakLanjutPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register policies
        $this->registerPolicies();

        // Additional gates if needed
        // Gate::define('approve-surat-4', function (User $user) {
        //     return $user->hasRole('Kepala Sekolah');
        // });
    }
}
