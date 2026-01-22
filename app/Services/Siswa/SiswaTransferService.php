<?php

namespace App\Services\Siswa;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Exceptions\BusinessValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Siswa Transfer Service
 * 
 * RESPONSIBILITY: Handle kenaikan kelas / pindah kelas
 * - Bulk transfer siswa ke kelas lain
 * - Get siswa for transfer UI
 * - Validate transfer operations
 * 
 * CLEAN ARCHITECTURE: Single Responsibility Principle
 * Split from SiswaService for better maintainability.
 * 
 * @package App\Services\Siswa
 */
class SiswaTransferService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository
    ) {}

    /**
     * Bulk transfer siswa to another class.
     * 
     * FITUR KENAIKAN KELAS / PINDAH KELAS:
     * - Hanya mengubah kelas_id, semua data historis tetap terjaga
     * - Riwayat pelanggaran, pembinaan, dan wali murid tidak terpengaruh
     *
     * @param array $siswaIds Array of siswa IDs to transfer
     * @param int $targetKelasId Target class ID
     * @return array ['success_count' => int, 'failed_count' => int, 'transferred_names' => array]
     * @throws BusinessValidationException
     */
    public function bulkTransferSiswa(array $siswaIds, int $targetKelasId): array
    {
        // Validate target kelas exists
        $targetKelas = Kelas::find($targetKelasId);
        if (!$targetKelas) {
            throw new BusinessValidationException('Kelas tujuan tidak ditemukan');
        }

        $successCount = 0;
        $failedCount = 0;
        $transferredNames = [];

        DB::beginTransaction();
        try {
            foreach ($siswaIds as $siswaId) {
                $siswa = Siswa::find($siswaId);
                if (!$siswa) {
                    $failedCount++;
                    continue;
                }

                // Skip if already in target class
                if ($siswa->kelas_id === $targetKelasId) {
                    continue;
                }

                $oldKelas = $siswa->kelas;
                $siswa->kelas_id = $targetKelasId;
                $siswa->save();

                $transferredNames[] = $siswa->nama_siswa;
                $successCount++;
            }

            DB::commit();

            // Log activity
            if ($successCount > 0) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($targetKelas)
                    ->withProperties([
                        'target_kelas_id' => $targetKelasId,
                        'target_kelas_nama' => $targetKelas->nama_kelas,
                        'transferred_count' => $successCount,
                        'transferred_names' => $transferredNames,
                    ])
                    ->log("Bulk transfer {$successCount} siswa ke kelas {$targetKelas->nama_kelas}");
            }

            return [
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'transferred_names' => $transferredNames,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw new BusinessValidationException('Gagal transfer siswa: ' . $e->getMessage());
        }
    }

    /**
     * Get siswa grouped by kelas for transfer UI.
     * 
     * OPTIMIZED: Calculate total_poin in single query using subquery
     * to avoid N+1 problem.
     *
     * @param int|null $kelasId Filter by specific kelas
     * @return Collection
     */
    public function getSiswaForTransfer(?int $kelasId = null): Collection
    {
        $query = Siswa::with('kelas.jurusan')
            ->whereNull('deleted_at')
            ->select([
                'siswa.id',
                'siswa.nisn',
                'siswa.nama_siswa',
                'siswa.nomor_hp_wali_murid',
                'siswa.kelas_id',
            ])
            // Calculate total_poin in single query with subquery
            ->selectSub(
                \App\Models\RiwayatPelanggaran::query()
                    ->selectRaw('COALESCE(SUM(jenis_pelanggaran.poin), 0)')
                    ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
                    ->whereColumn('riwayat_pelanggaran.siswa_id', 'siswa.id')
                    ->whereNull('riwayat_pelanggaran.deleted_at'),
                'total_poin'
            )
            ->orderBy('nama_siswa');

        if ($kelasId) {
            $query->where('siswa.kelas_id', $kelasId);
        }

        return $query->get();
    }

    /**
     * Get all kelas for transfer dropdown.
     *
     * @return Collection
     */
    public function getKelasForTransfer(): Collection
    {
        return Kelas::with('jurusan')
            ->withCount('siswa')
            ->orderBy('nama_kelas')
            ->get();
    }

    /**
     * Validate transfer target kelas.
     *
     * @param int $kelasId
     * @return bool
     */
    public function isValidTransferTarget(int $kelasId): bool
    {
        return Kelas::where('id', $kelasId)->exists();
    }
}
