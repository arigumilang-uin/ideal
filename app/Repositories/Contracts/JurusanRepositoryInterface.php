<?php

namespace App\Repositories\Contracts;

use App\Models\Jurusan;
use Illuminate\Database\Eloquent\Collection;

/**
 * Jurusan Repository Interface
 * 
 * Contract for data access layer of Jurusan (Program Studi).
 * Following Repository Pattern - abstracts data persistence.
 * 
 * @package App\Repositories\Contracts
 */
interface JurusanRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all jurusan with statistics.
     *
     * @return Collection
     */
    public function getAllWithStats(): Collection;

    /**
     * Get jurusan with kelas relation.
     *
     * @param int $id
     * @return Jurusan|null
     */
    public function getWithKelas(int $id): ?Jurusan;

    /**
     * Get jurusan with kaprodi relation.
     *
     * @param int $id
     * @return Jurusan|null
     */
    public function getWithKaprodi(int $id): ?Jurusan;

    /**
     * Get all jurusan for dropdown filter.
     *
     * @return Collection
     */
    public function getForFilter(): Collection;

    /**
     * Find jurusan by kode.
     *
     * @param string $kode
     * @return Jurusan|null
     */
    public function findByKode(string $kode): ?Jurusan;

    /**
     * Assign kaprodi to jurusan.
     *
     * @param int $jurusanId
     * @param int|null $userId
     * @return bool
     */
    public function assignKaprodi(int $jurusanId, ?int $userId): bool;
}
