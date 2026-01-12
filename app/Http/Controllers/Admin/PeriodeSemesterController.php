<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeSemester;
use App\Services\Absensi\PertemuanService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Periode Semester Controller (Admin)
 * 
 * CRUD untuk konfigurasi periode semester (tanggal mulai/selesai).
 */
class PeriodeSemesterController extends Controller
{
    public function __construct(
        private PertemuanService $pertemuanService
    ) {}

    /**
     * Display list of periods
     */
    public function index(): View
    {
        $periodes = PeriodeSemester::orderByDesc('tanggal_mulai')->get();
        
        return view('admin.periode-semester.index', [
            'periodes' => $periodes,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.periode-semester.create');
    }

    /**
     * Store new period
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_periode' => 'required|string|max:50',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:10|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2025/2026)',
        ]);

        // Check duplicate
        $exists = PeriodeSemester::where('semester', $validated['semester'])
            ->where('tahun_ajaran', $validated['tahun_ajaran'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Periode untuk semester dan tahun ajaran ini sudah ada.');
        }

        $periode = PeriodeSemester::create($validated);

        // Auto-generate pertemuan for existing jadwal
        $generated = $this->pertemuanService->generateAllPertemuanForPeriode($periode);

        $message = 'Periode semester berhasil ditambahkan.';
        if ($generated > 0) {
            $message .= " ({$generated} pertemuan ter-generate untuk jadwal yang sudah ada)";
        }

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', $message);
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $periode = PeriodeSemester::findOrFail($id);
        
        return view('admin.periode-semester.edit', [
            'periode' => $periode,
        ]);
    }

    /**
     * Update period
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);

        $validated = $request->validate([
            'nama_periode' => 'required|string|max:50',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:10|regex:/^\d{4}\/\d{4}$/',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2025/2026)',
        ]);

        // Check duplicate (exclude current)
        $exists = PeriodeSemester::where('semester', $validated['semester'])
            ->where('tahun_ajaran', $validated['tahun_ajaran'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Periode untuk semester dan tahun ajaran ini sudah ada.');
        }

        $periode->update($validated);

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', 'Periode semester berhasil diperbarui.');
    }

    /**
     * Set period as active
     */
    public function setActive(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);
        $periode->setAsActive();

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', "Periode '{$periode->nama_periode}' berhasil diaktifkan.");
    }

    /**
     * Generate pertemuan for all jadwal in this period
     */
    public function generatePertemuan(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);
        $generated = $this->pertemuanService->generateAllPertemuanForPeriode($periode);

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', "{$generated} pertemuan berhasil di-generate untuk periode '{$periode->nama_periode}'.");
    }

    /**
     * Delete period
     */
    public function destroy(int $id): RedirectResponse
    {
        $periode = PeriodeSemester::findOrFail($id);

        // Check if has pertemuan with absensi
        $hasAbsensi = $periode->jadwalMengajar()->whereHas('absensi')->exists();

        if ($hasAbsensi) {
            return back()->with('error', 'Periode tidak dapat dihapus karena sudah ada data absensi.');
        }

        $periode->delete();

        return redirect()
            ->route('admin.periode-semester.index')
            ->with('success', 'Periode semester berhasil dihapus.');
    }
}
