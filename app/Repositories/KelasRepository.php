<?php

namespace App\Repositories;

use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use App\Repositories\Contracts\KelasRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kelas Repository
 * 
 * Purpose: Encapsulate all database operations for Kelas
 * Pattern: Repository Pattern
 * Responsibility: Data Access ONLY (no business logic!)
 * 
 * @implements KelasRepositoryInterface
 */
class KelasRepository extends BaseRepository implements KelasRepositoryInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(new Kelas());
    }

    /**
     * Get all kelas with statistics.
     */
    public function getAllWithStats(): Collection
    {
        return Kelas::with('jurusan', 'konsentrasi', 'waliKelas')
            ->withCount('siswa')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Get kelas by jurusan.
     */
    public function getByJurusan(int $jurusanId): Collection
    {
        return Kelas::where('jurusan_id', $jurusanId)
            ->with('waliKelas')
            ->withCount('siswa')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Get kelas with wali kelas relation.
     */
    public function getWithWaliKelas(int $id): ?Kelas
    {
        return Kelas::with('waliKelas')->find($id);
    }

    /**
     * Get kelas with siswa relation.
     */
    public function getWithSiswa(int $id): ?Kelas
    {
        return Kelas::with('siswa')->find($id);
    }

    /**
     * Get all kelas for dropdown filter.
     */
    public function getForFilter(): Collection
    {
        return Kelas::select('id', 'nama_kelas', 'jurusan_id')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Assign wali kelas to kelas.
     */
    public function assignWaliKelas(int $kelasId, ?int $userId): bool
    {
        return Kelas::where('id', $kelasId)->update(['wali_kelas_user_id' => $userId]);
    }

    /**
     * Get siswa count for kelas.
     */
    public function getSiswaCount(int $kelasId): int
    {
        return Kelas::find($kelasId)?->siswa()->count() ?? 0;
    }

    // ===================================================================
    // LEGACY METHODS (Keep for backward compatibility)
    // ===================================================================

    /**
     * Get all kelas with relationships (for index) - LEGACY ALIAS
     */
    public function getAllWithRelationships(): Collection
    {
        return $this->getAllWithStats();
    }
    
    
    /**
     * Get kelas with relationships (for show)
     */
    public function getWithRelationships(int $id): ?Kelas
    {
        return Kelas::with(['jurusan', 'waliKelas', 'siswa.waliMurid'])
            ->find($id);
    }
    
    /**
     * Get jurusan by ID
     */
    public function getJurusan(int $jurusanId): ?Jurusan
    {
        return Jurusan::findOrFail($jurusanId);
    }
    
    /**
     * Get available users for wali kelas selection
     * RULE: All users EXCEPT Wali Murid can be assigned as Wali Kelas
     */
    public function getAvailableWaliKelas(): Collection
    {
        return User::whereHas('role', function($q) {
            $q->where('nama_role', '!=', 'Wali Murid');
        })->orderBy('username')->get();
    }
    
    /**
     * Create new kelas
     */
    public function create(array $data): Kelas
    {
        return Kelas::create($data);
    }
    
    /**
     * Update existing kelas (legacy method - use parent::update instead)
     */
    public function updateKelas(Kelas $kelas, array $data): bool
    {
        return $kelas->update($data);
    }
    
    /**
     * Delete kelas (legacy method - use parent::delete instead)
     */
    public function deleteKelas(Kelas $kelas): bool
    {
        return $kelas->delete();
    }
    
    /**
     * Get existing kelas names for a jurusan/konsentrasi with base name pattern
     * 
     * @param int $jurusanId
     * @param string $basePattern
     * @param int|null $konsentrasiId
     * @return array
     */
    public function getExistingKelasNames(int $jurusanId, string $basePattern, ?int $konsentrasiId = null): array
    {
        $query = Kelas::where('jurusan_id', $jurusanId)
            ->where('nama_kelas', 'like', $basePattern . '%');
        
        // If konsentrasi is specified, filter by it for accurate sequential numbering
        if ($konsentrasiId !== null) {
            $query->where('konsentrasi_id', $konsentrasiId);
        }
        
        return $query->pluck('nama_kelas')->toArray();
    }
    
    /**
     * Get all kelas for monitoring (Kepala Sekolah view)
     */
    public function getAllForMonitoring(): Collection
    {
        return Kelas::with(['jurusan', 'waliKelas', 'siswa'])
            ->orderBy('nama_kelas')
            ->get();
    }
}
