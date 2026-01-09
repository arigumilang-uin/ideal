<?php

namespace App\Services\Dashboard;

use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\JenisPelanggaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Dashboard Service
 * 
 * RESPONSIBILITY: Provide all dashboard statistics and chart data
 * 
 * CLEAN ARCHITECTURE: All query logic for dashboards is centralized here.
 * Controllers should only call these methods and pass data to views.
 * 
 * @package App\Services\Dashboard
 */
class DashboardService
{
    // =====================================================================
    // MASTER DATA STATISTICS (Operator Sekolah)
    // =====================================================================

    /**
     * Get operator sekolah dashboard statistics.
     */
    public function getOperatorStats(): array
    {
        return [
            'totalUser' => User::count(),
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::count(),
            'totalJurusan' => Jurusan::count(),
            'totalAturan' => JenisPelanggaran::count(),
        ];
    }

    // =====================================================================
    // WAKA KESISWAAN STATISTICS
    // =====================================================================

    /**
     * Get Waka Kesiswaan dashboard statistics.
     */
    public function getWakaStats(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $jurusanId = $filters['jurusan_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        return [
            'totalSiswa' => Siswa::count(),
            'pelanggaranFiltered' => $this->countPelanggaranFiltered($startDate, $endDate, $jurusanId, $kelasId),
            'kasusAktif' => TindakLanjut::forPembina('Waka Kesiswaan')
                ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
                ->count(),
            'butuhPersetujuan' => TindakLanjut::forPembina('Waka Kesiswaan')
                ->where('status', 'Menunggu Persetujuan')
                ->count(),
        ];
    }

    /**
     * Get Waka Kesiswaan kasus list.
     */
    public function getWakaKasus(array $filters = []): Collection
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $jurusanId = $filters['jurusan_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        return TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->forPembina('Waka Kesiswaan')
            ->whereHas('suratPanggilan')
            ->when($kelasId || $jurusanId, function($q) use ($kelasId, $jurusanId) {
                $q->whereHas('siswa.kelas', function($sq) use ($kelasId, $jurusanId) {
                    if ($kelasId) {
                        $sq->where('id', $kelasId);
                    } elseif ($jurusanId) {
                        $sq->where('jurusan_id', $jurusanId);
                    }
                });
            })
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->take(20)
            ->get();
    }

    // =====================================================================
    // KEPALA SEKOLAH STATISTICS
    // =====================================================================

    /**
     * Get Kepala Sekolah dashboard statistics.
     */
    public function getKepsekStats(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $jurusanId = $filters['jurusan_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        return [
            'totalSiswa' => Siswa::count(),
            'pelanggaranFiltered' => $this->countPelanggaranFiltered($startDate, $endDate, $jurusanId, $kelasId),
            'kasusAktif' => TindakLanjut::whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])->count(),
            'butuhPersetujuan' => TindakLanjut::where('status', 'Menunggu Persetujuan')->count(),
        ];
    }

    /**
     * Get Kepala Sekolah pending approval list.
     */
    public function getKepsekPendingApproval(int $limit = 10): Collection
    {
        return TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->where('status', 'Menunggu Persetujuan')
            ->latest()
            ->take($limit)
            ->get();
    }

    // =====================================================================
    // WALI KELAS STATISTICS
    // =====================================================================

    /**
     * Get Wali Kelas dashboard statistics.
     */
    public function getWaliKelasStats(int $kelasId, array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');

        return [
            'totalSiswa' => Siswa::where('kelas_id', $kelasId)->count(),
            'pelanggaranBulanIni' => $this->countPelanggaranFiltered($startDate, $endDate, null, $kelasId),
            'kasusAktif' => TindakLanjut::forPembina('Wali Kelas')
                ->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId))
                ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
                ->count(),
        ];
    }

    /**
     * Get siswa list for Wali Kelas dashboard.
     */
    public function getWaliKelasSiswa(int $kelasId): Collection
    {
        return Siswa::where('kelas_id', $kelasId)
            ->withCount('riwayatPelanggaran')
            ->orderBy('nama_siswa')
            ->get();
    }

    // =====================================================================
    // KAPRODI STATISTICS
    // =====================================================================

    /**
     * Get Kaprodi dashboard statistics.
     */
    public function getKaprodiStats(int $jurusanId, array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');

        $kelasIds = Kelas::where('jurusan_id', $jurusanId)->pluck('id');

        return [
            'totalSiswa' => Siswa::whereIn('kelas_id', $kelasIds)->count(),
            'totalKelas' => Kelas::where('jurusan_id', $jurusanId)->count(),
            'pelanggaranBulanIni' => $this->countPelanggaranFiltered($startDate, $endDate, $jurusanId, null),
            'kasusAktif' => TindakLanjut::forPembina('Kaprodi')
                ->whereHas('siswa', fn($q) => $q->whereIn('kelas_id', $kelasIds))
                ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
                ->count(),
        ];
    }

    // =====================================================================
    // WALI MURID STATISTICS
    // =====================================================================

    /**
     * Get Wali Murid dashboard statistics.
     */
    public function getWaliMuridStats(int $userId): array
    {
        $siswaIds = Siswa::where('wali_murid_id', $userId)->pluck('id');

        return [
            'totalAnak' => $siswaIds->count(),
            'totalPelanggaran' => RiwayatPelanggaran::whereIn('siswa_id', $siswaIds)->count(),
            'kasusAktif' => TindakLanjut::whereIn('siswa_id', $siswaIds)
                ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
                ->count(),
        ];
    }

    /**
     * Get anak list for Wali Murid dashboard.
     */
    public function getWaliMuridAnak(int $userId): Collection
    {
        return Siswa::where('wali_murid_id', $userId)
            ->with('kelas.jurusan')
            ->withCount('riwayatPelanggaran')
            ->get();
    }

    // =====================================================================
    // CHART DATA
    // =====================================================================

    /**
     * Get pelanggaran by jenis chart data.
     */
    public function getChartPelanggaranByJenis(array $filters = [], int $limit = 10): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $jurusanId = $filters['jurusan_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        $query = DB::table('riwayat_pelanggaran')
            ->join('jenis_pelanggaran', 'riwayat_pelanggaran.jenis_pelanggaran_id', '=', 'jenis_pelanggaran.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->whereNull('riwayat_pelanggaran.deleted_at');

        if ($kelasId) {
            $query->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelasId);
        } elseif ($jurusanId) {
            $query->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('kelas.jurusan_id', $jurusanId);
        }

        $data = $query
            ->select('jenis_pelanggaran.nama_pelanggaran', DB::raw('count(*) as total'))
            ->groupBy('jenis_pelanggaran.nama_pelanggaran')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $data->pluck('nama_pelanggaran')->toArray(),
            'data' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get pelanggaran by kelas chart data.
     */
    public function getChartPelanggaranByKelas(array $filters = [], int $limit = 10): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $jurusanId = $filters['jurusan_id'] ?? null;

        $query = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->whereNull('riwayat_pelanggaran.deleted_at');

        if ($jurusanId) {
            $query->where('kelas.jurusan_id', $jurusanId);
        }

        $data = $query
            ->select('kelas.nama_kelas', DB::raw('count(*) as total'))
            ->groupBy('kelas.nama_kelas')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $data->pluck('nama_kelas')->toArray(),
            'data' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get pelanggaran by jurusan chart data.
     */
    public function getChartPelanggaranByJurusan(array $filters = [], int $limit = 10): array
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');

        $data = DB::table('riwayat_pelanggaran')
            ->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
            ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->join('jurusan', 'kelas.jurusan_id', '=', 'jurusan.id')
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '>=', $startDate)
            ->whereDate('riwayat_pelanggaran.tanggal_kejadian', '<=', $endDate)
            ->whereNull('riwayat_pelanggaran.deleted_at')
            ->select('jurusan.nama_jurusan', DB::raw('count(*) as total'))
            ->groupBy('jurusan.nama_jurusan')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $data->pluck('nama_jurusan')->toArray(),
            'data' => $data->pluck('total')->toArray(),
        ];
    }

    /**
     * Get pelanggaran trend (monthly) chart data.
     */
    public function getChartPelanggaranTrend(array $filters = [], int $months = 6): array
    {
        $jurusanId = $filters['jurusan_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        $query = DB::table('riwayat_pelanggaran')
            ->whereNull('riwayat_pelanggaran.deleted_at')
            ->where('riwayat_pelanggaran.tanggal_kejadian', '>=', now()->subMonths($months));

        if ($kelasId) {
            $query->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelasId);
        } elseif ($jurusanId) {
            $query->join('siswa', 'riwayat_pelanggaran.siswa_id', '=', 'siswa.id')
                ->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->where('kelas.jurusan_id', $jurusanId);
        }

        $data = $query
            ->select(
                DB::raw('YEAR(tanggal_kejadian) as year'),
                DB::raw('MONTH(tanggal_kejadian) as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $values = [];
        foreach ($data as $row) {
            $labels[] = date('M Y', mktime(0, 0, 0, $row->month, 1, $row->year));
            $values[] = $row->total;
        }

        return [
            'labels' => $labels,
            'data' => $values,
        ];
    }

    // =====================================================================
    // HELPER METHODS
    // =====================================================================

    /**
     * Count pelanggaran with filters.
     */
    private function countPelanggaranFiltered(
        string $startDate,
        string $endDate,
        ?int $jurusanId = null,
        ?int $kelasId = null
    ): int {
        $query = RiwayatPelanggaran::query()
            ->whereDate('tanggal_kejadian', '>=', $startDate)
            ->whereDate('tanggal_kejadian', '<=', $endDate);

        if ($kelasId) {
            $query->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasId));
        } elseif ($jurusanId) {
            $query->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanId));
        }

        return $query->count();
    }

    /**
     * Get all jurusan for filter dropdown.
     */
    public function getAllJurusan(): Collection
    {
        return Jurusan::orderBy('nama_jurusan')->get();
    }

    /**
     * Get all kelas for filter dropdown.
     */
    public function getAllKelas(): Collection
    {
        return Kelas::orderBy('nama_kelas')->get();
    }

    /**
     * Get kelas by jurusan for filter dropdown.
     */
    public function getKelasByJurusan(int $jurusanId): Collection
    {
        return Kelas::where('jurusan_id', $jurusanId)
            ->orderBy('nama_kelas')
            ->get();
    }
}
