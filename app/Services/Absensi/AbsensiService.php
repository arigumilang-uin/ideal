<?php

namespace App\Services\Absensi;

use App\Enums\StatusAbsensi;
use App\Models\Absensi;
use App\Models\JadwalMengajar;
use App\Models\RiwayatPelanggaran;
use App\Models\Siswa;
use App\Services\Pelanggaran\PelanggaranRulesEngine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Absensi Service
 * 
 * Core logic untuk pencatatan absensi.
 * 
 * INTEGRASI PELANGGARAN:
 * - Status ALFA otomatis mencatat ke RiwayatPelanggaran
 * - Jenis Pelanggaran ID = 1 adalah "Alfa/Tidak Hadir Tanpa Keterangan"
 * - Rules Engine akan dievaluasi untuk trigger surat panggilan
 */
class AbsensiService
{
    // ID Jenis Pelanggaran untuk Alfa (dikonfigurasi di database)
    private const JENIS_PELANGGARAN_ALFA_ID = 1;

    public function __construct(
        private PelanggaranRulesEngine $rulesEngine
    ) {}

    /**
     * Catat absensi untuk satu siswa
     */
    public function recordAbsensi(
        int $siswaId,
        int $jadwalMengajarId,
        string $tanggal,
        StatusAbsensi $status,
        int $pencatatId,
        ?string $keterangan = null
    ): Absensi {
        return DB::transaction(function() use ($siswaId, $jadwalMengajarId, $tanggal, $status, $pencatatId, $keterangan) {
            // Hapus absensi existing jika ada (update)
            Absensi::where('siswa_id', $siswaId)
                ->where('jadwal_mengajar_id', $jadwalMengajarId)
                ->whereDate('tanggal', $tanggal)
                ->delete();

            // Buat absensi baru
            $absensi = Absensi::create([
                'siswa_id' => $siswaId,
                'jadwal_mengajar_id' => $jadwalMengajarId,
                'tanggal' => $tanggal,
                'status' => $status,
                'keterangan' => $keterangan,
                'pencatat_user_id' => $pencatatId,
                'absen_at' => now(),
            ]);

            // Jika status ALFA, otomatis catat sebagai pelanggaran
            if ($status === StatusAbsensi::Alfa) {
                $this->recordAlfaAsPelanggaran($absensi, $pencatatId);
            }

            return $absensi;
        });
    }

    /**
     * Catat absensi batch untuk semua siswa dalam satu jadwal
     * 
     * @param int $jadwalMengajarId
     * @param string $tanggal
     * @param array $absensiData Array of [siswa_id => ['status' => 'Hadir', 'keterangan' => '...']]
     * @param int $pencatatId
     * @return Collection<Absensi>
     */
    public function recordAbsensiBatch(
        int $jadwalMengajarId,
        string $tanggal,
        array $absensiData,
        int $pencatatId
    ): Collection {
        return DB::transaction(function() use ($jadwalMengajarId, $tanggal, $absensiData, $pencatatId) {
            $results = collect();

            foreach ($absensiData as $siswaId => $data) {
                $status = StatusAbsensi::from($data['status']);
                $keterangan = $data['keterangan'] ?? null;

                $absensi = $this->recordAbsensi(
                    siswaId: $siswaId,
                    jadwalMengajarId: $jadwalMengajarId,
                    tanggal: $tanggal,
                    status: $status,
                    pencatatId: $pencatatId,
                    keterangan: $keterangan
                );

                $results->push($absensi);
            }

            return $results;
        });
    }

    /**
     * Record Alfa as Pelanggaran (integrate with existing system)
     */
    private function recordAlfaAsPelanggaran(Absensi $absensi, int $pencatatId): void
    {
        try {
            $siswa = Siswa::with('kelas')->find($absensi->siswa_id);
            $jadwal = JadwalMengajar::with('mataPelajaran')->find($absensi->jadwal_mengajar_id);
            
            $mapelName = $jadwal?->mataPelajaran?->nama_mapel ?? 'Mata Pelajaran';
            $tanggal = $absensi->tanggal->format('d/m/Y');
            
            $keterangan = "Alfa pada {$mapelName} tanggal {$tanggal}";
            if ($absensi->keterangan) {
                $keterangan .= " - Catatan: {$absensi->keterangan}";
            }

            // Langsung buat RiwayatPelanggaran via model
            $riwayatPelanggaran = RiwayatPelanggaran::create([
                'siswa_id' => $absensi->siswa_id,
                'jenis_pelanggaran_id' => self::JENIS_PELANGGARAN_ALFA_ID,
                'guru_pencatat_user_id' => $pencatatId,
                'tanggal_kejadian' => $absensi->tanggal,
                'keterangan' => $keterangan,
            ]);

            // Link absensi dengan pelanggaran
            $absensi->linkToPelanggaran($riwayatPelanggaran->id);

            // Trigger Rules Engine untuk evaluasi dampak
            $this->rulesEngine->processBatch(
                $absensi->siswa_id,
                [self::JENIS_PELANGGARAN_ALFA_ID]
            );

            Log::info("Alfa recorded as pelanggaran", [
                'absensi_id' => $absensi->id,
                'siswa_id' => $absensi->siswa_id,
                'pelanggaran_id' => $riwayatPelanggaran->id,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to record Alfa as pelanggaran", [
                'absensi_id' => $absensi->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - absensi sudah tercatat, pelanggaran failed tapi tidak fatal
        }
    }

    /**
     * Get absensi statistics for a specific jadwal on a date
     */
    public function getStatistikAbsensi(int $jadwalMengajarId, string $tanggal): array
    {
        $absensi = Absensi::forJadwal($jadwalMengajarId)
            ->onDate($tanggal)
            ->get();

        $stats = [
            'total' => $absensi->count(),
            'hadir' => $absensi->where('status', StatusAbsensi::Hadir)->count(),
            'sakit' => $absensi->where('status', StatusAbsensi::Sakit)->count(),
            'izin' => $absensi->where('status', StatusAbsensi::Izin)->count(),
            'alfa' => $absensi->where('status', StatusAbsensi::Alfa)->count(),
        ];

        $stats['persentase_hadir'] = $stats['total'] > 0 
            ? round(($stats['hadir'] / $stats['total']) * 100, 1) 
            : 0;

        return $stats;
    }

    /**
     * Get rekap absensi for a siswa in date range
     */
    public function getRekapSiswa(int $siswaId, string $startDate, string $endDate): array
    {
        $absensi = Absensi::forSiswa($siswaId)
            ->betweenDates($startDate, $endDate)
            ->get();

        return [
            'total_hari' => $absensi->count(),
            'hadir' => $absensi->where('status', StatusAbsensi::Hadir)->count(),
            'sakit' => $absensi->where('status', StatusAbsensi::Sakit)->count(),
            'izin' => $absensi->where('status', StatusAbsensi::Izin)->count(),
            'alfa' => $absensi->where('status', StatusAbsensi::Alfa)->count(),
        ];
    }

    /**
     * Get rekap absensi for a kelas in date range
     */
    public function getRekapKelas(int $kelasId, string $startDate, string $endDate): Collection
    {
        $siswaList = Siswa::where('kelas_id', $kelasId)->get();
        
        return $siswaList->map(function($siswa) use ($startDate, $endDate) {
            $rekap = $this->getRekapSiswa($siswa->id, $startDate, $endDate);
            $rekap['siswa'] = $siswa;
            return $rekap;
        });
    }

    /**
     * Check apakah jadwal sudah diabsen hari ini
     */
    public function isJadwalSudahDiabsen(int $jadwalMengajarId, string $tanggal): bool
    {
        return Absensi::forJadwal($jadwalMengajarId)
            ->onDate($tanggal)
            ->exists();
    }

    /**
     * Get existing absensi for a jadwal on specific date
     */
    public function getAbsensiByJadwal(int $jadwalMengajarId, string $tanggal): Collection
    {
        return Absensi::with('siswa')
            ->forJadwal($jadwalMengajarId)
            ->onDate($tanggal)
            ->get()
            ->keyBy('siswa_id');
    }
}
