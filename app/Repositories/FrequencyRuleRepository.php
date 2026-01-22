<?php

namespace App\Repositories;

use App\Models\PelanggaranFrequencyRule;
use App\Repositories\Contracts\FrequencyRuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Frequency Rule Repository
 * 
 * Responsibility: ALL database operations for Frequency Rules
 * 
 * CLEAN ARCHITECTURE:
 * - Controller calls Service
 * - Service calls Repository
 * - Repository queries database
 * 
 * @implements FrequencyRuleRepositoryInterface
 */
class FrequencyRuleRepository extends BaseRepository implements FrequencyRuleRepositoryInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(new PelanggaranFrequencyRule());
    }

    /**
     * Get rules by jenis pelanggaran.
     */
    public function getByJenisPelanggaran(int $jenisPelanggaranId): Collection
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get rule for specific frequency.
     */
    public function getRuleForFrequency(int $jenisPelanggaranId, int $frequency): ?PelanggaranFrequencyRule
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->where('frequency_min', '<=', $frequency)
            ->where('frequency_max', '>=', $frequency)
            ->first();
    }

    /**
     * Get all rules with jenis pelanggaran relation.
     */
    public function getAllWithJenisPelanggaran(): Collection
    {
        return PelanggaranFrequencyRule::with('jenisPelanggaran')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Delete all rules for jenis pelanggaran.
     */
    public function deleteByJenisPelanggaran(int $jenisPelanggaranId): int
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)->delete();
    }

    /**
     * Bulk create rules for jenis pelanggaran.
     */
    public function bulkCreateForJenis(int $jenisPelanggaranId, array $rules): bool
    {
        DB::beginTransaction();
        try {
            foreach ($rules as $index => $rule) {
                PelanggaranFrequencyRule::create([
                    'jenis_pelanggaran_id' => $jenisPelanggaranId,
                    'frequency_min' => $rule['frequency_min'],
                    'frequency_max' => $rule['frequency_max'],
                    'poin' => $rule['poin'],
                    'sanksi' => $rule['sanksi'] ?? null,
                    'trigger_surat' => $rule['trigger_surat'] ?? false,
                    'tipe_surat' => $rule['tipe_surat'] ?? null,
                    'pembina_roles' => $rule['pembina_roles'] ?? null,
                    'display_order' => $index + 1,
                ]);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ===================================================================
    // LEGACY METHODS (Keep for backward compatibility)
    // ===================================================================

    /**
     * Get all rules for a specific jenis pelanggaran - LEGACY ALIAS
     */
    public function findByJenisPelanggaran(int $jenisPelanggaranId, ?string $orderBy = 'display_order'): Collection
    {
        return $this->getByJenisPelanggaran($jenisPelanggaranId);
    }

    /**
     * Get maximum display_order for a jenis pelanggaran
     */
    public function getMaxDisplayOrder(int $jenisPelanggaranId): ?int
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->max('display_order');
    }

    /**
     * Create new frequency rule
     */
    public function create(array $data): PelanggaranFrequencyRule
    {
        return PelanggaranFrequencyRule::create($data);
    }

    /**
     * Find rule by ID
     */
    public function findOrFail(int $ruleId): PelanggaranFrequencyRule
    {
        return PelanggaranFrequencyRule::findOrFail($ruleId);
    }

    /**
     * Update rule (legacy method - use parent::update instead)
     */
    public function updateRule(int $ruleId, array $data): bool
    {
        $rule = $this->findOrFail($ruleId);
        return $rule->update($data);
    }

    /**
     * Delete rule (legacy method - use parent::delete instead)
     */
    public function deleteRule(int $ruleId): bool
    {
        $rule = $this->findOrFail($ruleId);
        return $rule->delete();
    }

    /**
     * Count rules for a jenis pelanggaran
     */
    public function countByJenisPelanggaran(int $jenisPelanggaranId): int
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->count();
    }

    /**
     * Check if jenis pelanggaran has any rules
     */
    public function hasRules(int $jenisPelanggaranId): bool
    {
        return $this->countByJenisPelanggaran($jenisPelanggaranId) > 0;
    }

    /**
     * Get all rules paginated for admin view
     */
    public function getAllPaginated(int $jenisPelanggaranId, int $perPage = 15)
    {
        return PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->orderBy('display_order')
            ->paginate($perPage);
    }

    /**
     * Reorder rules after deletion
     * Updates display_order for all rules after the deleted one
     */
    public function reorderAfterDeletion(int $jenisPelanggaranId, int $deletedOrder): void
    {
        PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->where('display_order', '>', $deletedOrder)
            ->decrement('display_order');
    }
}
