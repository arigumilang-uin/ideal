<?php

namespace App\Services\Siswa;

use App\Models\Siswa;
use App\Repositories\Contracts\SiswaRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Exceptions\BusinessValidationException;
use Illuminate\Support\Facades\DB;

/**
 * Siswa Bulk Import Service
 * 
 * RESPONSIBILITY: Handle bulk import/create siswa
 * - Parse CSV/manual data
 * - Validate bulk rows
 * - Bulk create siswa with optional wali
 * - Bulk delete siswa by kelas
 * 
 * CLEAN ARCHITECTURE: Single Responsibility Principle
 * Split from SiswaService for better maintainability.
 * 
 * @package App\Services\Siswa
 */
class SiswaBulkService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepository,
        private UserRepositoryInterface $userRepository,
        private SiswaWaliService $waliService
    ) {}

    // =====================================================================
    // PARSING METHODS
    // =====================================================================

    /**
     * Parse bulk data from file or manual input.
     * 
     * @param string $type 'csv' or 'manual'
     * @param mixed $data File path for CSV, string for manual
     * @return array Array of parsed rows
     * @throws BusinessValidationException
     */
    public function parseBulkData(string $type, mixed $data): array
    {
        if ($type === 'csv' && is_string($data) && file_exists($data)) {
            return $this->parseCsvFile($data);
        } elseif ($type === 'manual' && is_string($data)) {
            return $this->parseManualData($data);
        }
        
        throw new BusinessValidationException('Invalid bulk data type or missing data');
    }

    /**
     * Parse CSV file for bulk import.
     *
     * @param string $filePath
     * @return array
     */
    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        
        if (!$handle) {
            throw new BusinessValidationException('Cannot open file for reading');
        }
        
        // Skip header
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 2) {
                $rows[] = [
                    'nisn' => trim($data[0] ?? ''),
                    'nama' => trim($data[1] ?? ''),
                    'nomor_hp_wali_murid' => trim($data[2] ?? ''),
                ];
            }
        }
        
        fclose($handle);
        return $rows;
    }

    /**
     * Parse manual input (textarea) for bulk import.
     * Supports comma, semicolon, and tab delimiters.
     *
     * @param string $data
     * @return array
     */
    private function parseManualData(string $data): array
    {
        $rows = [];
        $lines = explode("\n", $data);
        $isFirstLine = true;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Support comma, semicolon, and tab delimiter
            $parts = preg_split('/[,;\t]/', $line);
            
            // Skip header line if it looks like a header (contains 'nisn')
            if ($isFirstLine && stripos($parts[0], 'nisn') !== false) {
                $isFirstLine = false;
                continue;
            }
            $isFirstLine = false;
            
            if (count($parts) >= 2) {
                $rows[] = [
                    'nisn' => trim($parts[0] ?? ''),
                    'nama' => trim($parts[1] ?? ''),
                    'nomor_hp_wali_murid' => trim($parts[2] ?? ''),
                ];
            }
        }
        
        return $rows;
    }

    // =====================================================================
    // VALIDATION METHODS
    // =====================================================================

    /**
     * Validate bulk import rows and return validated rows with errors.
     *
     * @param array $rows Raw parsed rows
     * @return array ['valid_rows' => array, 'errors' => array]
     */
    public function validateBulkRows(array $rows): array
    {
        $validRows = [];
        $errors = [];
        $seenNisns = []; // Track NISNs in current batch
        
        foreach ($rows as $index => $row) {
            $lineNumber = $index + 1;
            
            // Validate NISN and Nama (required)
            if (empty($row['nisn']) || empty($row['nama'])) {
                $errors[] = "Baris {$lineNumber}: NISN dan Nama harus diisi";
                continue;
            }
            
            // Validate NISN format (10 digits)
            if (!preg_match('/^\d{10}$/', $row['nisn'])) {
                $errors[] = "Baris {$lineNumber}: NISN harus 10 digit angka";
                continue;
            }
            
            // Check duplicate NISN in current batch
            if (isset($seenNisns[$row['nisn']])) {
                $errors[] = "Baris {$lineNumber}: NISN {$row['nisn']} duplicate dengan baris {$seenNisns[$row['nisn']]}";
                continue;
            }
            
            // Check duplicate NISN in database
            if (Siswa::where('nisn', $row['nisn'])->exists()) {
                $errors[] = "Baris {$lineNumber}: NISN {$row['nisn']} sudah terdaftar di database";
                continue;
            }
            
            // Mark this NISN as seen
            $seenNisns[$row['nisn']] = $lineNumber;
            
            $validRows[] = $row;
        }
        
        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
        ];
    }

    // =====================================================================
    // BULK CREATE METHODS
    // =====================================================================

    /**
     * Process complete bulk create workflow.
     * 
     * CLEAN ARCHITECTURE: Single entry point for bulk create.
     * Controller should only call this method.
     *
     * @param string $dataType 'csv' or 'manual'
     * @param mixed $data File path or manual string
     * @param int $kelasId
     * @param bool $createWaliAll
     * @return array Complete result with success count, errors, credentials
     * @throws BusinessValidationException
     */
    public function processBulkCreate(string $dataType, mixed $data, int $kelasId, bool $createWaliAll = false): array
    {
        // Step 1: Parse data
        $rows = $this->parseBulkData($dataType, $data);
        
        if (empty($rows)) {
            throw new BusinessValidationException('Tidak ada data yang dapat diproses');
        }
        
        // Step 2: Validate rows
        $validation = $this->validateBulkRows($rows);
        
        if (empty($validation['valid_rows'])) {
            return [
                'success_count' => 0,
                'wali_credentials' => [],
                'skipped_wali_count' => 0,
                'errors' => $validation['errors'],
            ];
        }
        
        // Step 3: Create siswa
        $result = $this->bulkCreateSiswa($validation['valid_rows'], $kelasId, $createWaliAll);
        
        // Combine with validation errors
        $result['errors'] = $validation['errors'];
        
        return $result;
    }

    /**
     * Bulk create siswa from validated rows.
     *
     * @param array $rows
     * @param int $kelasId
     * @param bool $createWaliAll
     * @return array
     */
    public function bulkCreateSiswa(array $rows, int $kelasId, bool $createWaliAll = false): array
    {
        DB::beginTransaction();
        
        try {
            $successCount = 0;
            $waliCredentials = [];
            $skippedWaliCount = 0;
            
            foreach ($rows as $row) {
                $waliMuridUserId = null;
                $nomorHpWali = $row['nomor_hp_wali_murid'] ?? '';
                
                // Bersihkan nomor HP
                $phoneClean = preg_replace('/\D+/', '', $nomorHpWali);
                
                if ($createWaliAll && $phoneClean !== '') {
                    $waliCred = $this->waliService->findOrCreateWaliByPhone(
                        $nomorHpWali,
                        $row['nama'],
                        $row['nisn'] ?? null
                    );
                    
                    $waliMuridUserId = $waliCred['user_id'];
                    
                    // Only add to credentials if it's a new account
                    if ($waliCred['is_new']) {
                        $waliCredentials[] = $waliCred;
                    }
                } elseif ($createWaliAll) {
                    // No phone number - skip wali creation but continue with siswa
                    $skippedWaliCount++;
                }
                
                $siswaArray = [
                    'kelas_id' => $kelasId,
                    'wali_murid_user_id' => $waliMuridUserId,
                    'nisn' => $row['nisn'],
                    'nama_siswa' => $row['nama'],
                    'nomor_hp_wali_murid' => $phoneClean !== '' ? $phoneClean : null,
                ];
                
                $this->siswaRepository->create($siswaArray);
                $successCount++;
            }
            
            DB::commit();
            
            return [
                'success_count' => $successCount,
                'wali_credentials' => $waliCredentials,
                'skipped_wali_count' => $skippedWaliCount,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =====================================================================
    // BULK DELETE METHODS  
    // =====================================================================

    /**
     * Bulk delete siswa by kelas with comprehensive cleanup.
     *
     * @param int $kelasId
     * @param array $options ['deleteOrphanedWali' => bool, 'alasanKeluar' => string, 'keteranganKeluar' => string]
     * @return array ['count' => int, 'deleted_ids' => array, 'orphaned_wali_deleted' => int]
     */
    public function bulkDeleteByKelas(int $kelasId, array $options = []): array
    {
        $deleteOrphanedWali = $options['deleteOrphanedWali'] ?? false;
        $alasanKeluar = $options['alasanKeluar'] ?? null;
        $keteranganKeluar = $options['keteranganKeluar'] ?? null;

        DB::beginTransaction();
        
        try {
            $siswaList = Siswa::where('kelas_id', $kelasId)->get();
            $count = $siswaList->count();
            $deletedIds = [];
            $orphanedWaliDeleted = 0;
            
            $orphanedWaliIds = [];
            if ($deleteOrphanedWali) {
                $orphanedWaliIds = $this->detectOrphanedWali($kelasId);
            }
            
            foreach ($siswaList as $siswa) {
                // Set alasan keluar before delete
                if ($alasanKeluar) {
                    $siswa->alasan_keluar = $alasanKeluar;
                    $siswa->keterangan_keluar = $keteranganKeluar;
                    $siswa->save();
                }
                
                // Soft delete related data
                \App\Models\RiwayatPelanggaran::where('siswa_id', $siswa->id)->delete();
                \App\Models\TindakLanjut::where('siswa_id', $siswa->id)->delete();
                
                $deletedIds[] = $siswa->id;
                $siswa->delete();
            }
            
            if ($deleteOrphanedWali && !empty($orphanedWaliIds)) {
                foreach ($orphanedWaliIds as $waliId) {
                    \App\Models\User::where('id', $waliId)->delete();
                    $orphanedWaliDeleted++;
                }
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'kelas_id' => $kelasId,
                    'count' => $count,
                    'deleted_ids' => $deletedIds,
                    'orphaned_wali_deleted' => $orphanedWaliDeleted
                ])
                ->log("Bulk delete {$count} siswa dari kelas ID {$kelasId}");
            
            return [
                'count' => $count,
                'deleted_ids' => $deletedIds,
                'orphaned_wali_deleted' => $orphanedWaliDeleted
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Detect orphaned wali murid for kelas.
     *
     * @param int $kelasId
     * @return array
     */
    private function detectOrphanedWali(int $kelasId): array
    {
        // Get all wali_murid_id from siswa in this kelas
        $waliIds = Siswa::where('kelas_id', $kelasId)
            ->whereNotNull('wali_murid_id')
            ->pluck('wali_murid_id')
            ->unique()
            ->toArray();

        $orphanedIds = [];
        
        foreach ($waliIds as $waliId) {
            // Check if this wali has siswa in OTHER kelas
            $hasOtherSiswa = Siswa::where('wali_murid_id', $waliId)
                ->where('kelas_id', '!=', $kelasId)
                ->whereNull('deleted_at')
                ->exists();
            
            if (!$hasOtherSiswa) {
                $orphanedIds[] = $waliId;
            }
        }
        
        return $orphanedIds;
    }
}
