<?php

namespace App\Repositories;

use App\Models\Jurusan;
use App\Repositories\Contracts\JurusanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Jurusan Repository
 * 
 * Purpose: Encapsulate all database operations for Jurusan
 * Pattern: Repository Pattern
 * Responsibility: Data Access ONLY (no business logic!)
 * 
 * @implements JurusanRepositoryInterface
 */
class JurusanRepository extends BaseRepository implements JurusanRepositoryInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(new Jurusan());
    }

    /**
     * Get all jurusan with statistics.
     */
    public function getAllWithStats(): Collection
    {
        return Jurusan::withCount(['kelas', 'siswa', 'konsentrasi'])
            ->with('kaprodi')
            ->orderBy('nama_jurusan')
            ->get();
    }

    /**
     * Get jurusan with kelas relation.
     */
    public function getWithKelas(int $id): ?Jurusan
    {
        return Jurusan::with(['kelas.siswa', 'kelas.waliKelas'])->find($id);
    }

    /**
     * Get jurusan with kaprodi relation.
     */
    public function getWithKaprodi(int $id): ?Jurusan
    {
        return Jurusan::with('kaprodi')->find($id);
    }

    /**
     * Get all jurusan for dropdown filter.
     */
    public function getForFilter(): Collection
    {
        return Jurusan::select('id', 'nama_jurusan', 'kode_jurusan')
            ->orderBy('nama_jurusan')
            ->get();
    }

    /**
     * Find jurusan by kode.
     */
    public function findByKode(string $kode): ?Jurusan
    {
        return Jurusan::where('kode_jurusan', $kode)->first();
    }

    /**
     * Assign kaprodi to jurusan.
     */
    public function assignKaprodi(int $jurusanId, ?int $userId): bool
    {
        return Jurusan::where('id', $jurusanId)->update(['kaprodi_user_id' => $userId]);
    }

    // ===================================================================
    // LEGACY METHODS (Keep for backward compatibility)
    // ===================================================================

    /**
     * Get all jurusan with counts (for index) - LEGACY ALIAS
     */
    public function getAllWithCounts(): Collection
    {
        return $this->getAllWithStats();
    }
    
    
    /**
     * Get jurusan with relationships (for show)
     */
    public function getWithRelationships(int $id): ?Jurusan
    {
        return Jurusan::with([
            'kaprodi', 
            'konsentrasi.kelas', 
            'kelas.konsentrasi',
            'kelas.waliKelas', 
            'kelas.siswa',
            'siswa'
        ])->find($id);
    }
    
    /**
     * Create new jurusan
     */
    public function create(array $data): Jurusan
    {
        return Jurusan::create($data);
    }
    
    /**
     * Update existing jurusan (legacy method - use parent::update instead)
     */
    public function updateJurusan(Jurusan $jurusan, array $data): bool
    {
        return $jurusan->update($data);
    }
    
    /**
     * Delete jurusan (legacy method - use parent::delete instead)
     */
    public function deleteJurusan(Jurusan $jurusan): bool
    {
        return $jurusan->delete();
    }
    
    /**
     * Check if kode_jurusan exists
     */
    public function kodeExists(string $kode, ?int $excludeId = null): bool
    {
        $query = Jurusan::where('kode_jurusan', $kode);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }
    
    /**
     * Generate unique kode_jurusan by appending number
     * 
     * EXACT LOGIC from original controller (lines 44-49, 117-122)
     */
    public function generateUniqueKode(string $baseKode, ?int $excludeId = null): string
    {
        $kode = $baseKode;
        $i = 1;
        
        while ($this->kodeExists($kode, $excludeId)) {
            $i++;
            $kode = $baseKode . $i;
        }
        
        return $kode;
    }
    
    /**
     * Get all kelas for a jurusan (grouped by tingkat)
     */
    public function getKelasGroupedByTingkat(Jurusan $jurusan): Collection
    {
        return $jurusan->kelas()
            ->orderBy('id')
            ->get()
            ->groupBy('tingkat');
    }
    
    /**
     * Get jurusan count statistics (for deletion validation)
     */
    public function getCounts(Jurusan $jurusan): array
    {
        return [
            'kelas' => $jurusan->kelas()->count(),
            'siswa' => $jurusan->siswa()->count(),
        ];
    }
    
    /**
     * Get jurusan with monitoring relationships (Kepala Sekolah view)
     */
    public function getAllForMonitoring(): Collection
    {
        return Jurusan::withCount(['kelas', 'siswa'])
            ->with('kaprodi')
            ->orderBy('nama_jurusan')
            ->get();
    }
    
    /**
     * Get jurusan for monitoring show view
     */
    public function getForMonitoringShow(int $id): ?Jurusan
    {
        return Jurusan::with([
            'kaprodi',
            'kelas' => function($query) {
                $query->withCount('siswa')
                      ->with('waliKelas');
            }
        ])->find($id);
    }
}
