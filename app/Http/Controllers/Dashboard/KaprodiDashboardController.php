<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Models\TindakLanjut;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Kaprodi Dashboard Controller
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil DashboardService untuk data
 * - Return View
 * 
 * @package App\Http\Controllers\Dashboard
 */
class KaprodiDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Display Kaprodi dashboard.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $jurusan = $user->jurusanDiampu;

        if (!$jurusan) {
            return view('dashboards.kaprodi_no_data');
        }

        // Build filters
        $filters = [
            'start_date' => $request->input('start_date', date('Y-m-01')),
            'end_date' => $request->input('end_date', date('Y-m-d')),
            'jurusan_id' => $jurusan->id,
            'kelas_id' => $request->input('kelas_id'),
        ];

        // Get data from service
        $stats = $this->dashboardService->getKaprodiStats($jurusan->id, $filters);
        $chartData = $this->dashboardService->getChartPelanggaranByJenis($filters);
        $kelasJurusan = $this->dashboardService->getKelasByJurusan($jurusan->id);

        // Get siswa IDs for this jurusan (needed for kasus query)
        $siswaIds = Siswa::whereHas('kelas', fn($q) => $q->where('jurusan_id', $jurusan->id))
            ->when($filters['kelas_id'], fn($q) => $q->where('kelas_id', $filters['kelas_id']))
            ->pluck('id');

        // Get kasus for Kaprodi
        $kasusBaru = TindakLanjut::with(['siswa.kelas', 'suratPanggilan'])
            ->whereIn('siswa_id', $siswaIds)
            ->forPembina('Kaprodi')
            ->whereHas('suratPanggilan')
            ->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])
            ->latest()
            ->get();

        // AJAX Response
        if ($request->ajax()) {
            return response()->json([
                'stats' => view('dashboards._kaprodi_stats', [
                    'totalSiswa' => $stats['totalSiswa'],
                    'totalPelanggaran' => $stats['pelanggaranBulanIni'],
                    'totalKasus' => $stats['kasusAktif'],
                ])->render(),
                'table' => view('dashboards._kaprodi_table', compact('kasusBaru'))->render(),
                'charts' => [
                    'pelanggaran' => $chartData,
                ]
            ]);
        }

        return view('dashboards.kaprodi', [
            'jurusan' => $jurusan,
            'kasusBaru' => $kasusBaru,
            'chartLabels' => $chartData['labels'],
            'chartData' => $chartData['data'],
            'totalSiswa' => $stats['totalSiswa'],
            'totalKasus' => $stats['kasusAktif'],
            'totalPelanggaran' => $stats['pelanggaranBulanIni'],
            'kelasJurusan' => $kelasJurusan,
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
        ]);
    }
}