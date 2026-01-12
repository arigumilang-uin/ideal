<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Services\Absensi\AbsensiService;
use App\Services\Absensi\JadwalService;
use App\Models\JadwalMengajar;
use App\Models\Siswa;
use App\Enums\StatusAbsensi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Absensi Controller
 * 
 * Handle proses pencatatan absensi dan laporan.
 */
class AbsensiController extends Controller
{
    public function __construct(
        private AbsensiService $absensiService,
        private JadwalService $jadwalService
    ) {}

    /**
     * Dashboard absensi - tampilkan jadwal hari ini
     */
    public function index(): View
    {
        $user = auth()->user();
        $jadwalHariIni = $this->jadwalService->getJadwalHariIniForGuru($user->id);
        
        // Mark jadwal yang sudah diabsen
        $jadwalHariIni->each(function($jadwal) {
            $jadwal->sudah_diabsen = $this->absensiService->isJadwalSudahDiabsen(
                $jadwal->id, 
                today()->toDateString()
            );
            
            if ($jadwal->sudah_diabsen) {
                $jadwal->statistik = $this->absensiService->getStatistikAbsensi(
                    $jadwal->id,
                    today()->toDateString()
                );
            }
        });

        return view('absensi.index', [
            'jadwalHariIni' => $jadwalHariIni,
            'tanggal' => today(),
        ]);
    }

    /**
     * Form absensi untuk jadwal tertentu
     */
    public function create(int $jadwalId, Request $request): View
    {
        $tanggal = $request->input('tanggal', today()->toDateString());
        
        $jadwal = JadwalMengajar::with(['mataPelajaran', 'kelas.jurusan', 'guru'])
            ->findOrFail($jadwalId);
        
        // Get siswa di kelas ini
        $siswaList = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->orderBy('nama_siswa')
            ->get();
        
        // Get existing absensi jika sudah pernah diabsen
        $existingAbsensi = $this->absensiService->getAbsensiByJadwal($jadwalId, $tanggal);
        
        return view('absensi.create', [
            'jadwal' => $jadwal,
            'siswaList' => $siswaList,
            'existingAbsensi' => $existingAbsensi,
            'tanggal' => $tanggal,
            'statusOptions' => StatusAbsensi::forSelect(),
        ]);
    }

    /**
     * Simpan absensi batch
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
