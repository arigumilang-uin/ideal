<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Services\Absensi\AbsensiService;
use App\Services\Absensi\JadwalService;
use App\Services\Absensi\PertemuanService;
use App\Models\JadwalMengajar;
use App\Models\Pertemuan;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Enums\Hari;
use App\Enums\Semester;
use App\Enums\StatusAbsensi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Absensi Controller
 * 
 * Handle proses pencatatan absensi dengan grid view.
 */
class AbsensiController extends Controller
{
    public function __construct(
        private AbsensiService $absensiService,
        private JadwalService $jadwalService,
        private PertemuanService $pertemuanService
    ) {}

    /**
     * Dashboard absensi - tampilkan SEMUA jadwal per hari
     */
    public function index(): View
    {
        $user = auth()->user();
        $jadwalByHari = $this->jadwalService->getJadwalForGuru($user->id);
        
        // Add pertemuan count for each jadwal
        foreach ($jadwalByHari as $hari => $jadwalList) {
            foreach ($jadwalList as $jadwal) {
                $jadwal->totalPertemuan = $jadwal->pertemuan()->count();
            }
        }

        return view('absensi.index', [
            'jadwalByHari' => $jadwalByHari,
            'hariIni' => Hari::today()?->value,
            'currentSemester' => Semester::current()->value,
            'currentTahunAjaran' => Semester::currentTahunAjaran(),
        ]);
    }

    /**
     * Grid view absensi - siswa sebagai baris, pertemuan sebagai kolom
     */
    public function grid(int $jadwalId): View
    {
        $jadwal = JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'guru'])
            ->findOrFail($jadwalId);
        
        // Get all pertemuan for this jadwal
        $pertemuanList = Pertemuan::forJadwal($jadwalId)
            ->ordered()
            ->get();
        
        // Get siswa di kelas ini
        $siswaList = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama_siswa')
            ->get();
        
        // Build absensi matrix [siswa_id][pertemuan_id] => Absensi
        $absensiMatrix = [];
        $allAbsensi = Absensi::where('jadwal_mengajar_id', $jadwalId)
            ->whereIn('pertemuan_id', $pertemuanList->pluck('id'))
            ->get();
        
        foreach ($allAbsensi as $absensi) {
            $absensiMatrix[$absensi->siswa_id][$absensi->pertemuan_id] = $absensi;
        }

        // Find today's pertemuan if any
        $todayPertemuan = $pertemuanList->first(fn($p) => $p->tanggal->isToday());

        return view('absensi.grid', [
            'jadwal' => $jadwal,
            'pertemuanList' => $pertemuanList,
            'siswaList' => $siswaList,
            'absensiMatrix' => $absensiMatrix,
            'todayPertemuan' => $todayPertemuan,
        ]);
    }

    /**
     * Update single absensi via AJAX
     */
    public function updateSingle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'status' => 'nullable|in:Hadir,Sakit,Izin,Alfa,',
        ]);

        try {
            $pertemuan = Pertemuan::with('jadwalMengajar')->findOrFail($validated['pertemuan_id']);
            
            if (empty($validated['status'])) {
                // Delete existing absensi
                Absensi::where('siswa_id', $validated['siswa_id'])
                    ->where('pertemuan_id', $validated['pertemuan_id'])
                    ->delete();
            } else {
                // Create or update
                $absensi = $this->absensiService->recordAbsensiWithPertemuan(
                    siswaId: $validated['siswa_id'],
                    pertemuanId: $validated['pertemuan_id'],
                    jadwalMengajarId: $pertemuan->jadwal_mengajar_id,
                    tanggal: $pertemuan->tanggal->toDateString(),
                    status: StatusAbsensi::from($validated['status']),
                    pencatatId: auth()->id()
                );
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch update all siswa for specific pertemuan
     */
    public function batchUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'status' => 'required|in:Hadir,Sakit,Izin,Alfa',
        ]);

        try {
            $pertemuan = Pertemuan::with('jadwalMengajar.kelas')->findOrFail($validated['pertemuan_id']);
            $siswaList = Siswa::where('kelas_id', $pertemuan->jadwalMengajar->kelas_id)->get();

            foreach ($siswaList as $siswa) {
                $this->absensiService->recordAbsensiWithPertemuan(
                    siswaId: $siswa->id,
                    pertemuanId: $validated['pertemuan_id'],
                    jadwalMengajarId: $pertemuan->jadwal_mengajar_id,
                    tanggal: $pertemuan->tanggal->toDateString(),
                    status: StatusAbsensi::from($validated['status']),
                    pencatatId: auth()->id()
                );
            }

            return response()->json([
                'success' => true,
                'count' => $siswaList->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Form absensi untuk jadwal tertentu (legacy - redirect to grid)
     */
    public function create(int $jadwalId, Request $request): RedirectResponse
    {
        return redirect()->route('absensi.grid', $jadwalId);
    }

    /**
     * Simpan absensi batch (legacy)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jadwal_mengajar_id' => 'required|exists:jadwal_mengajar,id',
            'tanggal' => 'required|date',
            'absensi' => 'required|array',
            'absensi.*.status' => 'required|in:Hadir,Sakit,Izin,Alfa',
            'absensi.*.keterangan' => 'nullable|string|max:500',
        ]);

        $this->absensiService->recordAbsensiBatch(
            jadwalMengajarId: $validated['jadwal_mengajar_id'],
            tanggal: $validated['tanggal'],
            absensiData: $validated['absensi'],
            pencatatId: auth()->id()
        );

        return redirect()
            ->route('absensi.index')
            ->with('success', 'Absensi berhasil disimpan.');
    }

    /**
     * Lihat detail absensi untuk jadwal tertentu
     */
    public function show(int $jadwalId, Request $request): View
    {
        $tanggal = $request->input('tanggal', today()->toDateString());
        
        $jadwal = JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'guru'])
            ->findOrFail($jadwalId);
        
        $absensi = $this->absensiService->getAbsensiByJadwal($jadwalId, $tanggal);
        $statistik = $this->absensiService->getStatistikAbsensi($jadwalId, $tanggal);
        
        return view('absensi.show', [
            'jadwal' => $jadwal,
            'absensi' => $absensi,
            'statistik' => $statistik,
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * Laporan rekap absensi per kelas
     */
    public function report(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        
        $rekap = null;
        if ($kelasId) {
            $rekap = $this->absensiService->getRekapKelas($kelasId, $startDate, $endDate);
        }
        
        $kelasList = \App\Models\Kelas::with('jurusan')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();
        
        return view('absensi.report', [
            'rekap' => $rekap,
            'kelasList' => $kelasList,
            'selectedKelasId' => $kelasId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
