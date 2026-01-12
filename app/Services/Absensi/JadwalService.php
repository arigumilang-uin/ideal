<?php

namespace App\Services\Absensi;

use App\Enums\Hari;
use App\Enums\Semester;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Jadwal Service
 * 
 * Logic untuk manajemen jadwal mengajar.
 */
class JadwalService
{
    /**
     * Get jadwal hari ini untuk seorang guru
     */
    public function getJadwalHariIniForGuru(int $userId): Collection
    {
        $hariIni = Hari::today();
        
        if (!$hariIni) {
            return collect(); // Hari Minggu
        }

        return JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan'])
            ->active()
            ->currentPeriod()
            ->forGuru($userId)
            ->forHari($hariIni)
            ->orderByTime()
            ->get();
    }

    /**
     * Get all jadwal for a guru in current period
     */
    public function getJadwalForGuru(int $userId): Collection
    {
        return JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan'])
            ->active()
            ->currentPeriod()
            ->forGuru($userId)
            ->orderBy('hari')
            ->orderByTime()
            ->get()
            ->groupBy('hari');
    }

    /**
     * Get jadwal for a kelas in current period
     */
    public function getJadwalForKelas(int $kelasId): Collection
    {
        return JadwalMengajar::with(['mataPelajaran', 'guru'])
            ->active()
            ->currentPeriod()
            ->forKelas($kelasId)
            ->orderBy('hari')
            ->orderByTime()
            ->get()
            ->groupBy('hari');
    }

    /**
     * Create new jadwal
     */
    public function createJadwal(array $data): JadwalMengajar
    {
        // Set default semester dan tahun ajaran jika tidak ada
        $data['semester'] = $data['semester'] ?? Semester::current()->value;
        $data['tahun_ajaran'] = $data['tahun_ajaran'] ?? Semester::currentTahunAjaran();

        return JadwalMengajar::create($data);
    }

    /**
     * Update jadwal
     */
    public function updateJadwal(int $jadwalId, array $data): JadwalMengajar
    {
        $jadwal = JadwalMengajar::findOrFail($jadwalId);
        $jadwal->update($data);
        return $jadwal->fresh();
    }

    /**
     * Delete jadwal
     */
    public function deleteJadwal(int $jadwalId): void
    {
        JadwalMengajar::findOrFail($jadwalId)->delete();
    }

    /**
     * Check for scheduling conflict
     */
    public function hasConflict(
        int $kelasId,
        Hari $hari,
        string $jamMulai,
        string $jamSelesai,
        Semester $semester,
        string $tahunAjaran,
        ?int $excludeJadwalId = null
    ): bool {
        $query = JadwalMengajar::where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                // Check for time overlap
                $q->where(function($inner) use ($jamMulai, $jamSelesai) {
                    $inner->where('jam_mulai', '<', $jamSelesai)
                          ->where('jam_selesai', '>', $jamMulai);
                });
            });

        if ($excludeJadwalId) {
            $query->where('id', '!=', $excludeJadwalId);
        }

        return $query->exists();
    }

    /**
     * Get dropdown data for creating jadwal
     */
    public function getDropdownData(): array
    {
        return [
            'guru' => User::whereHas('role', function($q) {
                    $q->whereIn('nama_role', ['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Sarana', 'Kepala Sekolah']);
                })
                ->where('is_active', true)
                ->orderBy('nama')
                ->get(['id', 'nama', 'username']),
            
            'mata_pelajaran' => MataPelajaran::active()
                ->orderBy('nama_mapel')
                ->get(['id', 'nama_mapel', 'kode_mapel']),
            
            'kelas' => Kelas::with('jurusan')
                ->orderBy('tingkat')
                ->orderBy('nama_kelas')
                ->get(),
            
            'hari' => Hari::forSelect(),
            'semester' => Semester::forSelect(),
            
            'current_semester' => Semester::current()->value,
            'current_tahun_ajaran' => Semester::currentTahunAjaran(),
        ];
    }

    /**
     * Copy jadwal from previous period
     */
    public function copyFromPreviousPeriod(
        Semester $fromSemester,
        string $fromTahunAjaran,
        Semester $toSemester,
        string $toTahunAjaran
    ): int {
        $jadwalLama = JadwalMengajar::where('semester', $fromSemester)
            ->where('tahun_ajaran', $fromTahunAjaran)
            ->get();

        $copied = 0;
        foreach ($jadwalLama as $jadwal) {
            JadwalMengajar::create([
                'user_id' => $jadwal->user_id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'kelas_id' => $jadwal->kelas_id,
                'hari' => $jadwal->hari,
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'semester' => $toSemester,
                'tahun_ajaran' => $toTahunAjaran,
                'is_active' => true,
            ]);
            $copied++;
        }

        return $copied;
    }
}
