<?php

namespace App\Repositories\Contracts;

use App\Models\Kelas;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kelas Repository Interface
 * 
 * Contract for data access layer of Kelas.
 * Following Repository Pattern - abstracts data persistence.
 * 
 * @package App\Repositories\Contracts
 */
interface KelasRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all kelas with statistics.
     *
     * @return Collection
     */
    public function getAllWithStats(): Collection;

    /**
     * Get kelas by jurusan.
     *
     * @param int $jurusanId
     * @return Collection
     */
    public function getByJurusan(int $jurusanId): Collection;

    /**
     * Get kelas with wali kelas relation.
     *
     * @param int $id
     * @return Kelas|null
     */
    public function getWithWaliKelas(int $id): ?Kelas;

    /**
     * Get kelas with siswa relation.
     *
     * @param int $id
     * @return Kelas|null
     */
    public function getWithSiswa(int $id): ?Kelas;

    /**
     * Get all kelas for dropdown filter.
     *
     * @return Collection
     */
    public function getForFilter(): Collection;

    /**
     * Assign wali kelas to kelas.
     *
     * @param int $kelasId
     * @param int|null $userId
     * @return bool
     */
    public function assignWaliKelas(int $kelasId, ?int $userId): bool;

    /**
     * Get siswa count for kelas.
     *
     * @param int $kelasId
     * @return int
     */
    public function getSiswaCount(int $kelasId): int;
}
