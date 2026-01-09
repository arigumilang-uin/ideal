<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Models\TindakLanjut;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Wali Kelas Dashboard Controller
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil DashboardService untuk data
 * - Return View
 * 
 * @package App\Http\Controllers\Dashboard
 */
class WaliKelasDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display Wali Kelas dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $kelas = $user->kelasDiampu;

        if (!$kelas) {
            return view('dashboards.walikelas_no_data');
        }

        // Build filters
        $filters = [
            'start_date' => $request->input('start_date', date('Y-m-01')),
            'end_date' => $request->input('end_date', date('Y-m-d')),
            'kelas_id' => $kelas->id,
        ];

        // Get data from service
        $stats = $this->dashboardService->getWaliKelasStats($kelas->id, $filters);
        $chartData = $this->dashboardService->getChartPelanggaranByJenis($filters);

        // Get kasus for Wali Kelas
        $kasusBaru = TindakLanjut::with(['siswa', 'suratPanggilan'])
            ->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
            ->forPembina('Wali Kelas')
            ->whereHas('suratPanggilan')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->limit(10)
            ->get();

        // AJAX Response
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._walikelas_stats', [
                    'totalSiswa' => $stats['totalSiswa'],
                    'totalPelanggaran' => $stats['pelanggaranBulanIni'],
                    'totalKasus' => $stats['kasusAktif'],
                ])->render(),
                'table' => view('dashboards._walikelas_table', compact('kasusBaru'))->render(),
                'charts' => [
                    'pelanggaran' => $chartData,
                ]
            ]);
        }

        return view('dashboards.walikelas', [
            'kelas' => $kelas,
            'kasusBaru' => $kasusBaru,
            'chartLabels' => $chartData['labels'],
            'chartData' => $chartData['data'],
            'totalSiswa' => $stats['totalSiswa'],
            'totalKasus' => $stats['kasusAktif'],
            'totalPelanggaran' => $stats['pelanggaranBulanIni'],
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
        ]);
    }
}
