<?php

namespace App\Services\Pelanggaran;

use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Models\PembinaanInternalRule;
use Illuminate\Support\Collection;

/**
 * Poin Calculation Service
 * 
 * RESPONSIBILITY: Calculate siswa violation points
 * - Calculate total accumulated points
 * - Get pembinaan recommendations based on points
 * - Get list of siswa needing pembinaan
 * 
 * EXTRACTED FROM: PelanggaranRulesEngine (God Class reduction)
 * 
 * CLEAN ARCHITECTURE: Single Responsibility for poin calculation
 * 
 * @package App\Services\Pelanggaran
 */
class PoinCalculationService
{
    /**
     * Hitung total poin akumulasi siswa dari semua riwayat pelanggaran.
     * 
     * LOGIC:
     * - For frequency-based rules: Calculate using frequency thresholds
     * - For legacy rules: Use poin from jenis_pelanggaran table
     * 
     * @param int $siswaId
     * @return int
     */
    public function hitungTotalPoin(int $siswaId): int
    {
        // Get ALL riwayat for this siswa, grouped by jenis_pelanggaran
        $riwayat = RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->with('jenisPelanggaran.frequencyRules')
            ->get()
            ->groupBy('jenis_pelanggaran_id');

        $totalPoin = 0;

        // For each jenis pelanggaran, calculate poin based on its rules
        foreach ($riwayat as $jenisPelanggaranId => $records) {
            $jenisPelanggaran = $records->first()?->jenisPelanggaran;
            
            if (!$jenisPelanggaran) continue;

            if ($jenisPelanggaran->usesFrequencyRules()) {
                // FREQUENCY-BASED: Iterate through ALL frequencies and sum matched poin
                $currentFrequency = $records->count();
                $rules = $jenisPelanggaran->frequencyRules;
                
                // Iterate from frequency 1 to current
                for ($freq = 1; $freq <= $currentFrequency; $freq++) {
                    foreach ($rules as $rule) {
                        if ($rule->matchesFrequency($freq)) {
                            $totalPoin += $rule->poin;
                        }
                    }
                }
            } else {
                // LEGACY: Sum poin from all records (backward compatibility)
                $totalPoin += $records->count() * $jenisPelanggaran->poin;
            }
        }

        return $totalPoin;
    }

    /**
     * Get rekomendasi pembinaan internal berdasarkan akumulasi poin.
     * 
     * CATATAN: Ini HANYA rekomendasi, TIDAK trigger surat.
     * 
     * @param int $totalPoin Total poin akumulasi siswa
     * @return array ['pembina_roles' => array, 'keterangan' => string, 'range_text' => string]
     */
    public function getPembinaanRekomendasi(int $totalPoin): array
    {
        // Get rules from database
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
        
        return $this->getPembinaanRekomendasiWithRules($totalPoin, $rules);
    }

    /**
     * Get rekomendasi pembinaan dengan pre-fetched rules (optimized).
     * 
     * @param int $totalPoin
     * @param Collection $rules Pre-fetched rules collection
     * @return array
     */
    public function getPembinaanRekomendasiWithRules(int $totalPoin, Collection $rules): array
    {
        // Find matching rule
        $matchedRule = $rules->first(fn($rule) => $rule->matchesPoin($totalPoin));

        if (!$matchedRule) {
            return [
                'pembina_roles' => [],
                'keterangan' => '',
                'range_text' => '',
            ];
        }

        return [
            'pembina_roles' => $matchedRule->pembina_roles,
            'keterangan' => $matchedRule->keterangan,
            'range_text' => "{$matchedRule->poin_min}-" . ($matchedRule->poin_max ?? '∞') . " Poin",
        ];
    }

    /**
     * Get siswa yang perlu pembinaan berdasarkan akumulasi poin.
     * 
     * @param int|null $poinMin Filter minimum poin (optional)
     * @param int|null $poinMax Filter maximum poin (optional)
     * @return Collection
     */
    public function getSiswaPerluPembinaan(?int $poinMin = null, ?int $poinMax = null): Collection
    {
        // Fetch pembinaan rules ONCE
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
        
        // Get ALL siswa yang punya riwayat pelanggaran
        $siswaIds = RiwayatPelanggaran::distinct()->pluck('siswa_id');
        
        $siswaList = collect();
        
        foreach ($siswaIds as $siswaId) {
            // Use the frequency-based calculation
            $totalPoin = $this->hitungTotalPoin($siswaId);
            
            // Skip if poin is 0
            if ($totalPoin == 0) {
                continue;
            }
            
            // Apply poin filters
            if ($poinMin !== null && $totalPoin < $poinMin) {
                continue;
            }
            if ($poinMax !== null && $totalPoin > $poinMax) {
                continue;
            }
            
            // Get rekomendasi
            $rekomendasi = $this->getPembinaanRekomendasiWithRules($totalPoin, $rules);
            
            // Skip if no matching rule
            if (empty($rekomendasi['pembina_roles'])) {
                continue;
            }
            
            // Load siswa with relations
            $siswa = Siswa::with(['kelas.jurusan', 'kelas.waliKelas'])->find($siswaId);
            
            if (!$siswa) {
                continue;
            }
            
            $siswaList->push([
                'siswa' => $siswa,
                'total_poin' => $totalPoin,
                'rekomendasi' => $rekomendasi,
            ]);
        }
        
        return $siswaList->sortByDesc('total_poin')->values();
    }

    /**
     * Check frekuensi pelanggaran tertentu untuk siswa.
     * 
     * @param int $siswaId
     * @param int $jenisPelanggaranId
     * @return int
     */
    public function getFrequency(int $siswaId, int $jenisPelanggaranId): int
    {
        return RiwayatPelanggaran::where('siswa_id', $siswaId)
            ->where('jenis_pelanggaran_id', $jenisPelanggaranId)
            ->count();
    }

    /**
     * Get statistik poin untuk siswa.
     * 
     * @param int $siswaId
     * @return array
     */
    public function getStatistikPoin(int $siswaId): array
    {
        $totalPoin = $this->hitungTotalPoin($siswaId);
        $rekomendasi = $this->getPembinaanRekomendasi($totalPoin);
        $totalPelanggaran = RiwayatPelanggaran::where('siswa_id', $siswaId)->count();

        return [
            'total_poin' => $totalPoin,
            'total_pelanggaran' => $totalPelanggaran,
            'rekomendasi' => $rekomendasi,
        ];
    }
}
