<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Waka Sarana Dashboard Controller
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil DashboardService untuk data
 * - Return View
 * 
 * @package App\Http\Controllers\Dashboard
 */
class WakaSaranaDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display Waka Sarana dashboard.
     */
    public function index(Request $request): View
    {
        // Build filters
        $filters = [
            'start_date' => $request->input('start_date', date('Y-m-01')),
            'end_date' => $request->input('end_date', date('Y-m-d')),
        ];

        // Get chart data from service
        $chartPelanggaran = $this->dashboardService->getChartPelanggaranByJenis($filters);
        $chartKelas = $this->dashboardService->getChartPelanggaranByKelas($filters);

        // Get kasus for Waka Sarana
        $kasusBaru = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->forPembina('Waka Sarana')
            ->whereHas('suratPanggilan')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->get();

        // Get stats
        $kasusAktif = TindakLanjut::forPembina('Waka Sarana')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->count();

        return view('dashboards.waka_sarana', [
            'kasusBaru' => $kasusBaru,
            'chartLabels' => $chartPelanggaran['labels'],
            'chartData' => $chartPelanggaran['data'],
            'chartKelasLabels' => $chartKelas['labels'],
            'chartKelasData' => $chartKelas['data'],
            'totalSiswa' => \App\Models\Siswa::count(),
            'totalKasus' => $kasusBaru->count(),
            'kasusAktif' => $kasusAktif,
            'totalPelanggaran' => array_sum($chartPelanggaran['data']),
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
        ]);
    }
}
