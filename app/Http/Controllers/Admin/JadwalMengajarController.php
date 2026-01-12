<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Absensi\JadwalService;
use App\Models\JadwalMengajar;
use App\Enums\Hari;
use App\Enums\Semester;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

use App\Services\Absensi\PertemuanService;

/**
 * Jadwal Mengajar Controller (Admin)
 * 
 * CRUD untuk master data jadwal mengajar.
 */
class JadwalMengajarController extends Controller
{
    public function __construct(
        private JadwalService $jadwalService,
        private PertemuanService $pertemuanService
    ) {}

    /**
     * Display list of jadwal
     */
    public function index(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $guruId = $request->input('guru_id');
        $hari = $request->input('hari');

        $query = JadwalMengajar::with(['guru', 'mataPelajaran', 'kelas.jurusan'])
            ->currentPeriod()
            ->active();

        if ($kelasId) {
            $query->forKelas($kelasId);
        }

        if ($guruId) {
            $query->forGuru($guruId);
        }

        if ($hari) {
            $query->where('hari', $hari);
        }

        $jadwal = $query->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(30);

        $dropdownData = $this->jadwalService->getDropdownData();

        return view('admin.jadwal-mengajar.index', [
            'jadwal' => $jadwal,
            'filters' => [
                'kelas_id' => $kelasId,
                'guru_id' => $guruId,
                'hari' => $hari,
            ],
            ...$dropdownData,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $dropdownData = $this->jadwalService->getDropdownData();

        return view('admin.jadwal-mengajar.create', $dropdownData);
    }

    /**
     * Store new jadwal
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:10',
        ]);

        // Check for conflict (kelas + guru)
        $conflicts = $this->jadwalService->checkConflicts(
            kelasId: $validated['kelas_id'],
            userId: $validated['user_id'],
            hari: Hari::from($validated['hari']),
            jamMulai: $validated['jam_mulai'],
            jamSelesai: $validated['jam_selesai'],
            semester: Semester::from($validated['semester']),
            tahunAjaran: $validated['tahun_ajaran']
        );

        if (!empty($conflicts)) {
            return back()
                ->withInput()
                ->with('error', implode(' ', $conflicts));
        }

        $jadwal = $this->jadwalService->createJadwal($validated);

        // Auto-generate pertemuan if periode exists
        $generated = $this->pertemuanService->generatePertemuanForJadwal($jadwal);
        $message = 'Jadwal mengajar berhasil ditambahkan.';
        if ($generated > 0) {
            $message .= " ({$generated} pertemuan ter-generate)";
        }

        return redirect()
            ->route('admin.jadwal-mengajar.index')
            ->with('success', $message);
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $jadwal = JadwalMengajar::findOrFail($id);
        $dropdownData = $this->jadwalService->getDropdownData();

        return view('admin.jadwal-mengajar.edit', [
            'jadwal' => $jadwal,
            ...$dropdownData,
        ]);
    }

    /**
     * Update jadwal
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:10',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Check for conflict (exclude current jadwal)
        $conflicts = $this->jadwalService->checkConflicts(
            kelasId: $validated['kelas_id'],
            userId: $validated['user_id'],
            hari: Hari::from($validated['hari']),
            jamMulai: $validated['jam_mulai'],
            jamSelesai: $validated['jam_selesai'],
            semester: Semester::from($validated['semester']),
            tahunAjaran: $validated['tahun_ajaran'],
            excludeJadwalId: $id
        );

        if (!empty($conflicts)) {
            return back()
                ->withInput()
                ->with('error', implode(' ', $conflicts));
        }

        $this->jadwalService->updateJadwal($id, $validated);

        return redirect()
            ->route('admin.jadwal-mengajar.index')
            ->with('success', 'Jadwal mengajar berhasil diperbarui.');
    }

    /**
     * Delete jadwal
     */
    public function destroy(int $id): RedirectResponse
    {
        $jadwal = JadwalMengajar::findOrFail($id);

        // Check if has absensi
        if ($jadwal->absensi()->exists()) {
            return back()->with('error', 'Jadwal tidak dapat dihapus karena sudah ada data absensi.');
        }

        $this->jadwalService->deleteJadwal($id);

        return redirect()
            ->route('admin.jadwal-mengajar.index')
            ->with('success', 'Jadwal mengajar berhasil dihapus.');
    }
}
