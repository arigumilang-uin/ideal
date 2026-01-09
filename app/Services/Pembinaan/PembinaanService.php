<?php

namespace App\Services\Pembinaan;

use App\Models\PembinaanStatus;
use App\Models\PembinaanInternalRule;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Enums\StatusPembinaan;
use App\Services\Pelanggaran\PoinCalculationService;
use App\Services\User\RoleService;
use Illuminate\Support\Collection;

/**
 * Pembinaan Service
 * 
 * RESPONSIBILITY: Handle pembinaan internal business logic
 * - Get siswa perlu pembinaan dengan filtering
 * - Sync pembinaan status records
 * - Mulai/selesaikan pembinaan
 * - Statistics
 * 
 * CLEAN ARCHITECTURE: Business logic extracted from Controller
 * 
 * @package App\Services\Pembinaan
 */
class PembinaanService
{
    public function __construct(
        private PoinCalculationService $poinService
    ) {}

    /**
     * Get pembinaan list with filtering based on user role.
     *
     * @param User $user
     * @param array $filters
     * @return array ['list' => Collection, 'stats' => array]
     */
    public function getPembinaanList(User $user, array $filters = []): array
    {
        $userRole = RoleService::effectiveRoleName($user);
        $ruleId = $filters['rule_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;
        $jurusanId = $filters['jurusan_id'] ?? null;
        $statusFilter = $filters['status'] ?? null;

        // Get all rules
        $rules = $this->getAllRules();

        // Sync pembinaan records (auto-create if not exists)
        $this->syncPembinaanRecords($user, $filters);

        // Build query
        $query = PembinaanStatus::with([
            'siswa.kelas.jurusan',
            'rule',
            'dibinaOleh',
            'diselesaikanOleh',
        ]);

        // Apply role-based scope
        $this->applyRoleScope($query, $user, $userRole);

        // Apply filters
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        }
        if ($jurusanId) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanId));
        }
        if ($ruleId) {
            $query->where('pembinaan_rule_id', $ruleId);
        }

        // Filter by pembina_roles
        $query->whereJsonContains('pembina_roles', $userRole);

        $list = $query->orderByDesc('created_at')->get();

        return [
            'list' => $list,
            'stats' => $this->calculateStats($list),
        ];
    }

    /**
     * Sync pembinaan status records.
     * Auto-create if not exists for siswa matching rules.
     */
    private function syncPembinaanRecords(User $user, array $filters): void
    {
        $userRole = RoleService::effectiveRoleName($user);
        $ruleId = $filters['rule_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;
        $jurusanId = $filters['jurusan_id'] ?? null;

        // Get poin range from selected rule
        $poinMin = null;
        $poinMax = null;
        
        if ($ruleId) {
            $selectedRule = PembinaanInternalRule::find($ruleId);
            if ($selectedRule) {
                $poinMin = $selectedRule->poin_min;
                $poinMax = $selectedRule->poin_max;
            }
        }

        // Get siswa list from poin service
        $siswaList = $this->poinService->getSiswaPerluPembinaan($poinMin, $poinMax);
        $rules = $this->getAllRules();

        // Filter by role
        $siswaList = $this->filterByRole($siswaList, $user, $userRole, $kelasId, $jurusanId);

        // Auto-create pembinaan status for each matching siswa
        foreach ($siswaList as $item) {
            $siswa = $item['siswa'];
            $rekomendasi = $item['rekomendasi'];
            $totalPoin = $item['total_poin'];

            $matchingRule = $rules->first(fn($rule) => $rule->matchesPoin($totalPoin));

            if ($matchingRule) {
                PembinaanStatus::createIfNotExists(
                    $siswa->id,
                    $matchingRule->id,
                    $totalPoin,
                    $rekomendasi['range_text'],
                    $rekomendasi['keterangan'],
                    $rekomendasi['pembina_roles']
                );
            }
        }
    }

    /**
     * Apply role-based scope to query.
     */
    private function applyRoleScope($query, User $user, string $userRole): void
    {
        if ($userRole === 'Wali Kelas') {
            $kelasBinaan = $user->kelasDiampu;
            if ($kelasBinaan) {
                $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasBinaan->id));
            }
        } elseif ($userRole === 'Kaprodi') {
            $jurusanBinaan = $user->jurusanDiampu;
            if ($jurusanBinaan) {
                $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanBinaan->id));
            }
        }
    }

    /**
     * Filter siswa list by user role.
     */
    private function filterByRole(Collection $siswaList, User $user, string $userRole, ?int $kelasId, ?int $jurusanId): Collection
    {
        return $siswaList->filter(function ($item) use ($user, $userRole, $kelasId, $jurusanId) {
            $pembinaRoles = $item['rekomendasi']['pembina_roles'] ?? [];
            
            if (is_string($pembinaRoles)) {
                $pembinaRoles = json_decode($pembinaRoles, true) ?? [];
            }
            
            if (!in_array($userRole, $pembinaRoles)) {
                return false;
            }
            
            $siswa = $item['siswa'];
            
            // Wali Kelas scope
            if ($userRole === 'Wali Kelas') {
                $kelasBinaan = $user->kelasDiampu;
                if (!$kelasBinaan || $siswa->kelas_id !== $kelasBinaan->id) {
                    return false;
                }
            }
            
            // Kaprodi scope
            if ($userRole === 'Kaprodi') {
                $jurusanBinaan = $user->jurusanDiampu;
                if (!$jurusanBinaan || !$siswa->kelas || $siswa->kelas->jurusan_id !== $jurusanBinaan->id) {
                    return false;
                }
            }
            
            // Additional filters
            if ($kelasId && $siswa->kelas_id != $kelasId) {
                return false;
            }
            if ($jurusanId && $siswa->kelas?->jurusan_id != $jurusanId) {
                return false;
            }
            
            return true;
        });
    }

    /**
     * Calculate statistics from pembinaan list.
     */
    private function calculateStats(Collection $list): array
    {
        return [
            'total' => $list->count(),
            'perlu_pembinaan' => $list->where('status', StatusPembinaan::PERLU_PEMBINAAN)->count(),
            'sedang_dibina' => $list->where('status', StatusPembinaan::SEDANG_DIBINA)->count(),
            'selesai' => $list->where('status', StatusPembinaan::SELESAI)->count(),
        ];
    }

    /**
     * Mulai pembinaan.
     */
    public function mulaiPembinaan(int $pembinaanId, User $user, ?string $catatan = null): array
    {
        $pembinaan = PembinaanStatus::findOrFail($pembinaanId);
        $userRole = RoleService::effectiveRoleName($user);

        // Authorization check
        if (!in_array($userRole, $pembinaan->pembina_roles)) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses untuk membina siswa ini.'];
        }

        if (!$pembinaan->mulaiPembinaan($user->id)) {
            return ['success' => false, 'message' => 'Tidak dapat memulai pembinaan. Status saat ini bukan "Perlu Pembinaan".'];
        }

        // Simpan catatan
        if ($catatan) {
            $pembinaan->update(['catatan_pembinaan' => $catatan]);
        }

        // Log activity
        activity()
            ->performedOn($pembinaan)
            ->causedBy($user)
            ->withProperties([
                'siswa_nama' => $pembinaan->siswa->nama_siswa,
                'old_status' => 'Perlu Pembinaan',
                'new_status' => 'Sedang Dibina',
            ])
            ->log('Memulai pembinaan internal');

        return ['success' => true, 'message' => 'Pembinaan untuk ' . $pembinaan->siswa->nama_siswa . ' berhasil dimulai!'];
    }

    /**
     * Selesaikan pembinaan.
     */
    public function selesaikanPembinaan(int $pembinaanId, User $user, string $hasilPembinaan = ''): array
    {
        $pembinaan = PembinaanStatus::findOrFail($pembinaanId);
        $userRole = RoleService::effectiveRoleName($user);

        // Authorization check
        $canComplete = $pembinaan->dibina_oleh_user_id === $user->id 
                    || in_array($userRole, $pembinaan->pembina_roles);

        if (!$canComplete) {
            return ['success' => false, 'message' => 'Anda tidak memiliki akses untuk menyelesaikan pembinaan ini.'];
        }

        if (!$pembinaan->selesaikanPembinaan($user->id, $hasilPembinaan)) {
            return ['success' => false, 'message' => 'Tidak dapat menyelesaikan pembinaan. Status saat ini bukan "Sedang Dibina".'];
        }

        // Log activity
        activity()
            ->performedOn($pembinaan)
            ->causedBy($user)
            ->withProperties([
                'siswa_nama' => $pembinaan->siswa->nama_siswa,
                'old_status' => 'Sedang Dibina',
                'new_status' => 'Selesai',
                'hasil_pembinaan' => $hasilPembinaan,
            ])
            ->log('Menyelesaikan pembinaan internal');

        return ['success' => true, 'message' => 'Pembinaan untuk ' . $pembinaan->siswa->nama_siswa . ' berhasil diselesaikan!'];
    }

    /**
     * Get pembinaan detail.
     */
    public function getPembinaanDetail(int $id): PembinaanStatus
    {
        return PembinaanStatus::with([
            'siswa.kelas.jurusan',
            'rule',
            'dibinaOleh',
            'diselesaikanOleh',
        ])->findOrFail($id);
    }

    /**
     * Get all pembinaan rules.
     */
    public function getAllRules(): Collection
    {
        return PembinaanInternalRule::orderBy('display_order')->get();
    }

    /**
     * Get all kelas for filter.
     */
    public function getAllKelas(): Collection
    {
        return Kelas::orderBy('nama_kelas')->get();
    }

    /**
     * Get all jurusan for filter.
     */
    public function getAllJurusan(): Collection
    {
        return Jurusan::orderBy('nama_jurusan')->get();
    }

    /**
     * Get export data.
     */
    public function getExportData(User $user, ?string $statusFilter = null): Collection
    {
        $userRole = RoleService::effectiveRoleName($user);

        $query = PembinaanStatus::with(['siswa.kelas.jurusan', 'rule', 'dibinaOleh'])
            ->whereJsonContains('pembina_roles', $userRole);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        return $query->orderByDesc('created_at')->get();
    }
}
