<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Services\Pelanggaran\PelanggaranService;
use App\Models\RiwayatPelanggaran;
use App\Models\TindakLanjut;
use App\Models\PembinaanStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Wali Murid Dashboard Controller
 * 
 * PERAN: Kurir (Courier)
 * - Menerima HTTP Request
 * - Panggil Service untuk data
 * - Return View
 * 
 * @package App\Http\Controllers\Dashboard
 */
class WaliMuridDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private PelanggaranService $pelanggaranService
    ) {}

    /**
     * Display Wali Murid dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get all anak linked to this wali murid
        $semuaAnak = $user->anakWali;

        if ($semuaAnak->isEmpty()) {
            return view('dashboards.wali_murid_no_data');
        }

        // Handle anak selection (child switch)
        $selectedSiswaId = $request->query('siswa_id');
        $siswaAktif = $selectedSiswaId
            ? $semuaAnak->firstWhere('id', $selectedSiswaId) ?? $semuaAnak->first()
            : $semuaAnak->first();

        // Get statistics from services
        $totalPoin = $this->pelanggaranService->calculateTotalPoin($siswaAktif->id);

        // Get riwayat pelanggaran
        $riwayat = RiwayatPelanggaran::with('jenisPelanggaran')
            ->where('siswa_id', $siswaAktif->id)
            ->orderByDesc('tanggal_kejadian')
            ->get();

        // Get kasus/sanksi
        $kasus = TindakLanjut::where('siswa_id', $siswaAktif->id)
            ->orderByDesc('created_at')
            ->get();

        // Get active pembinaan
        $pembinaanAktif = PembinaanStatus::forSiswa($siswaAktif->id)
            ->active()
            ->with(['rule', 'dibinaOleh'])
            ->latest()
            ->first();

        return view('dashboards.wali_murid', [
            'semuaAnak' => $semuaAnak,
            'siswa' => $siswaAktif,
            'totalPoin' => $totalPoin,
            'riwayat' => $riwayat,
            'kasus' => $kasus,
            'pembinaanAktif' => $pembinaanAktif,
        ]);
    }
}
