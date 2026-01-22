<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Kepala Sekolah Dashboard Controller
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil DashboardService untuk data
 * - Return View
 * 
 * ATURAN:
 * - TIDAK BOLEH ada query database langsung  
 * - Semua statistik dari DashboardService
 * 
 * @package App\Http\Controllers\Dashboard
 */
class KepsekDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display Kepala Sekolah dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date', date('Y-m-01')),
            'end_date' => $request->input('end_date', date('Y-m-d')),
            'jurusan_id' => $request->input('jurusan_id'),
            'kelas_id' => $request->input('kelas_id'),
        ];
        
        $chartMode = $request->input('chart_mode', 'trend');

        // Get data from service
        $stats = $this->dashboardService->getKepsekStats($filters);
        $kasusMenunggu = $this->dashboardService->getKepsekPendingApproval(10);
        
        // Get chart data based on mode
        $chartData = $this->getChartByMode($chartMode, $filters);

        // AJAX Response
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._kepsek_stats', [
                    'totalSiswa' => $stats['totalSiswa'],
                    'totalPelanggaran' => $stats['pelanggaranFiltered'],
                    'totalKasus' => $stats['kasusAktif'],
                    'totalKasusMenunggu' => $stats['butuhPersetujuan'],
                ])->render(),
                'table' => view('dashboards._kepsek_table', compact('kasusMenunggu'))->render(),
                'charts' => [
                    'mainChart' => $chartData,
                ]
            ]);
        }

        // Get filter options
        $allJurusan = $this->dashboardService->getAllJurusan();
        $allKelas = $this->dashboardService->getAllKelas();

        return view('dashboards.kepsek', [
            'kasusBaru' => collect([]), // Deprecated, use kasusMenunggu
            'kasusMenunggu' => $kasusMenunggu,
            'chartData' => $chartData,
            'totalSiswa' => $stats['totalSiswa'],
            'totalKasus' => $stats['kasusAktif'],
            'totalKasusMenunggu' => $stats['butuhPersetujuan'],
            'totalPelanggaran' => $stats['pelanggaranFiltered'],
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
            'allJurusan' => $allJurusan,
            'allKelas' => $allKelas,
            'chartMode' => $chartMode,
        ]);
    }

    /**
     * Get chart data based on mode.
     * Formats chart data for the view.
     */
    private function getChartByMode(string $mode, array $filters): array
    {
        switch ($mode) {
            case 'jenis':
                $data = $this->dashboardService->getChartPelanggaranByJenis($filters);
                return [
                    'type' => 'doughnut',
                    'title' => 'Berdasarkan Jenis',
                    'subtitle' => 'Top 10 Pelanggaran',
                    'labels' => $data['labels'],
                    'data' => $data['data'],
                ];
            
            case 'jurusan':
                $data = $this->dashboardService->getChartPelanggaranByJurusan($filters);
                return [
                    'type' => 'bar',
                    'title' => 'Berdasarkan Jurusan',
                    'subtitle' => 'Sebaran Per Jurusan',
                    'labels' => $data['labels'],
                    'data' => $data['data'],
                ];
            
            case 'kelas':
                $data = $this->dashboardService->getChartPelanggaranByKelas($filters);
                return [
                    'type' => 'bar',
                    'title' => 'Berdasarkan Kelas',
                    'subtitle' => 'Top 10 Kelas',
                    'labels' => $data['labels'],
                    'data' => $data['data'],
                    'options' => ['indexAxis' => 'y']
                ];
            
            case 'trend':
            default:
                $data = $this->dashboardService->getChartPelanggaranTrend($filters);
                return [
                    'type' => 'line',
                    'title' => 'Tren Pelanggaran',
                    'subtitle' => '6 Bulan Terakhir',
                    'labels' => $data['labels'],
                    'data' => $data['data'],
                ];
        }
    }
}
