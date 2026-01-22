<?php

namespace App\Repositories\Contracts;

use App\Models\PelanggaranFrequencyRule;
use Illuminate\Database\Eloquent\Collection;

/**
 * Frequency Rule Repository Interface
 * 
 * Contract for data access layer of PelanggaranFrequencyRule.
 * Following Repository Pattern - abstracts data persistence.
 * 
 * @package App\Repositories\Contracts
 */
interface FrequencyRuleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get rules by jenis pelanggaran.
     *
     * @param int $jenisPelanggaranId
     * @return Collection
     */
    public function getByJenisPelanggaran(int $jenisPelanggaranId): Collection;

    /**
     * Get rule for specific frequency.
     *
     * @param int $jenisPelanggaranId
     * @param int $frequency
     * @return PelanggaranFrequencyRule|null
     */
    public function getRuleForFrequency(int $jenisPelanggaranId, int $frequency): ?PelanggaranFrequencyRule;

    /**
     * Get all rules with jenis pelanggaran relation.
     *
     * @return Collection
     */
    public function getAllWithJenisPelanggaran(): Collection;

    /**
     * Delete all rules for jenis pelanggaran.
     *
     * @param int $jenisPelanggaranId
     * @return int Number of deleted records
     */
    public function deleteByJenisPelanggaran(int $jenisPelanggaranId): int;

    /**
     * Bulk create rules for jenis pelanggaran.
     *
     * @param int $jenisPelanggaranId
     * @param array $rules
     * @return bool
     */
    public function bulkCreateForJenis(int $jenisPelanggaranId, array $rules): bool;
}
