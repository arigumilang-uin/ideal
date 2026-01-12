<?php

namespace App\Services\Absensi;

use App\Enums\Hari;
use App\Models\JadwalMengajar;
use App\Models\PeriodeSemester;
use App\Models\Pertemuan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Pertemuan Service
 * 
 * Generate dan manage pertemuan (meeting instances) berdasarkan jadwal template
 * dan periode semester.
 */
class PertemuanService
{
    /**
     * Generate pertemuan untuk satu jadwal berdasarkan periode semester aktif
     * 
     * @param JadwalMengajar $jadwal
     * @return int Jumlah pertemuan yang di-generate
     */
    public function generatePertemuanForJadwal(JadwalMengajar $jadwal): int
    {
        // Get active period matching jadwal's semester
        $periode = PeriodeSemester::where('semester', $jadwal->semester)
            ->where('tahun_ajaran', $jadwal->tahun_ajaran)
            ->first();

        if (!$periode) {
            Log::warning("No periode found for jadwal", [
                'jadwal_id' => $jadwal->id,
                'semester' => $jadwal->semester->value,
                'tahun_ajaran' => $jadwal->tahun_ajaran,
            ]);
            return 0;
        }

        return $this->generatePertemuanFromPeriode($jadwal, $periode);
    }

    /**
     * Generate pertemuan dari periode tertentu
     */
    public function generatePertemuanFromPeriode(JadwalMengajar $jadwal, PeriodeSemester $periode): int
    {
        // Get day number from Hari enum
        $dayOfWeek = $jadwal->hari->dayNumber();
        
        // Get all dates for that day within the period
        $dates = $periode->getDatesForDay($dayOfWeek);
        
        $generated = 0;
        
        DB::transaction(function() use ($jadwal, $dates, &$generated) {
            foreach ($dates as $index => $date) {
                // Check if already exists
                $existing = Pertemuan::where('jadwal_mengajar_id', $jadwal->id)
                    ->whereDate('tanggal', $date)
                    ->exists();
                
                if (!$existing) {
                    Pertemuan::create([
                        'jadwal_mengajar_id' => $jadwal->id,
                        'tanggal' => $date,
                        'pertemuan_ke' => $index + 1,
                        'status' => Pertemuan::STATUS_AKTIF,
                    ]);
                    $generated++;
                }
            }
        });

        Log::info("Generated pertemuan for jadwal", [
            'jadwal_id' => $jadwal->id,
            'total_generated' => $generated,
        ]);

        return $generated;
    }

    /**
     * Generate pertemuan untuk semua jadwal dalam periode tertentu
     */
    public function generateAllPertemuanForPeriode(PeriodeSemester $periode): int
    {
        $jadwalList = JadwalMengajar::where('semester', $periode->semester)
            ->where('tahun_ajaran', $periode->tahun_ajaran)
            ->active()
            ->get();

        $totalGenerated = 0;
        
        foreach ($jadwalList as $jadwal) {
            $totalGenerated += $this->generatePertemuanFromPeriode($jadwal, $periode);
        }

        return $totalGenerated;
    }

    /**
     * Get pertemuan hari ini untuk seorang guru
     */
    public function getPertemuanHariIniForGuru(int $userId): Collection
    {
        $today = today();
        
        return Pertemuan::with(['jadwalMengajar.mataPelajaran', 'jadwalMengajar.kelas.jurusan'])
            ->whereHas('jadwalMengajar', function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('is_active', true);
            })
            ->onDate($today)
            ->aktif()
            ->get()
            ->sortBy(function($pertemuan) {
                return $pertemuan->jadwalMengajar->jam_mulai;
            });
    }

    /**
     * Get or create pertemuan for today based on jadwal
     */
    public function getOrCreatePertemuanToday(JadwalMengajar $jadwal): ?Pertemuan
    {
        $today = today();
        
        // Check if jadwal is for today
        if ($jadwal->hari !== Hari::today()) {
            return null;
        }

        // Find existing pertemuan
        $pertemuan = Pertemuan::forJadwal($jadwal->id)
            ->onDate($today)
            ->first();

        // Create if not exists
        if (!$pertemuan) {
            // Calculate pertemuan_ke
            $lastPertemuan = Pertemuan::forJadwal($jadwal->id)
                ->latest('pertemuan_ke')
                ->first();
            
            $pertemuanKe = ($lastPertemuan?->pertemuan_ke ?? 0) + 1;

            $pertemuan = Pertemuan::create([
                'jadwal_mengajar_id' => $jadwal->id,
                'tanggal' => $today,
                'pertemuan_ke' => $pertemuanKe,
                'status' => Pertemuan::STATUS_AKTIF,
            ]);
        }

        return $pertemuan;
    }

    /**
     * Get statistics for a jadwal
     */
    public function getJadwalStatistics(int $jadwalId): array
    {
        $pertemuanList = Pertemuan::forJadwal($jadwalId)->ordered()->get();
        
        return [
            'total_pertemuan' => $pertemuanList->count(),
            'pertemuan_selesai' => $pertemuanList->where('status', Pertemuan::STATUS_SELESAI)->count(),
            'pertemuan_aktif' => $pertemuanList->where('status', Pertemuan::STATUS_AKTIF)->count(),
            'pertemuan_kosong' => $pertemuanList->where('status', Pertemuan::STATUS_KOSONG)->count(),
        ];
    }

    /**
     * Recalculate pertemuan_ke for a jadwal (in case of deletions)
     */
    public function recalculatePertemuanKe(int $jadwalId): void
    {
        $pertemuanList = Pertemuan::forJadwal($jadwalId)
            ->orderBy('tanggal')
            ->get();

        foreach ($pertemuanList as $index => $pertemuan) {
            $pertemuan->update(['pertemuan_ke' => $index + 1]);
        }
    }
}
