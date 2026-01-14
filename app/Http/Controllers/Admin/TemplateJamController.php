<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateJam;
use App\Models\PeriodeSemester;
use App\Enums\Hari;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Template Jam Controller
 * 
 * Mengelola template jam pelajaran per periode dan per hari.
 * Menggantikan JamPelajaranController yang sebelumnya global.
 */
class TemplateJamController extends Controller
{
    /**
     * Display template jam for a periode
     */
    public function index(Request $request): View
    {
        // Get all periods
        $allPeriodes = PeriodeSemester::orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->get();

        // Determine selected periode
        $periodeId = $request->input('periode_id');
        $selectedPeriode = null;
        
        if ($periodeId) {
            $selectedPeriode = PeriodeSemester::find($periodeId);
        }
        if (!$selectedPeriode) {
            $selectedPeriode = PeriodeSemester::current();
        }

        // Get selected hari (default: Senin)
        $selectedHari = $request->input('hari', 'Senin');
        
        // Get template jam for selected periode and hari
        $templateJams = collect();
        if ($selectedPeriode) {
            $templateJams = TemplateJam::forPeriode($selectedPeriode->id)
                ->forHari($selectedHari)
                ->ordered()
                ->get();
        }

        // Available hari
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // Tipe options
        $tipeOptions = [
            'pelajaran' => 'Pelajaran',
            'istirahat' => 'Istirahat',
            'ishoma' => 'Ishoma',
            'upacara' => 'Upacara',
            'lainnya' => 'Lainnya',
        ];

        return view('admin.template-jam.index', [
            'allPeriodes' => $allPeriodes,
            'selectedPeriode' => $selectedPeriode,
            'selectedHari' => $selectedHari,
            'templateJams' => $templateJams,
            'hariList' => $hariList,
            'tipeOptions' => $tipeOptions,
        ]);
    }

    /**
     * Store new template jam
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'periode_semester_id' => 'required|exists:periode_semester,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'label' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tipe' => 'required|in:pelajaran,istirahat,ishoma,upacara,lainnya',
        ]);

        // Get next urutan
        $maxUrutan = TemplateJam::forPeriode($validated['periode_semester_id'])
            ->forHari($validated['hari'])
            ->max('urutan') ?? 0;
        
        $validated['urutan'] = $maxUrutan + 1;
        $validated['is_active'] = true;

        TemplateJam::create($validated);

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $validated['periode_semester_id'],
                'hari' => $validated['hari'],
            ])
            ->with('success', 'Slot waktu berhasil ditambahkan.');
    }

    /**
     * Update template jam
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $templateJam = TemplateJam::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tipe' => 'required|in:pelajaran,istirahat,ishoma,upacara,lainnya',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $templateJam->update($validated);

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $templateJam->periode_semester_id,
                'hari' => $templateJam->hari->value ?? $templateJam->hari,
            ])
            ->with('success', 'Slot waktu berhasil diperbarui.');
    }

    /**
     * Delete template jam
     */
    public function destroy(int $id): RedirectResponse
    {
        $templateJam = TemplateJam::findOrFail($id);
        $periodeId = $templateJam->periode_semester_id;
        $hari = $templateJam->hari->value ?? $templateJam->hari;
        
        // Check if slot is used in jadwal
        if ($templateJam->jadwalMengajar()->exists()) {
            return redirect()
                ->route('admin.template-jam.index', [
                    'periode_id' => $periodeId,
                    'hari' => $hari,
                ])
                ->with('error', 'Tidak dapat menghapus slot yang sudah digunakan di jadwal mengajar.');
        }

        $templateJam->delete();

        // Reorder remaining slots
        $this->reorderSlots($periodeId, $hari);

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $periodeId,
                'hari' => $hari,
            ])
            ->with('success', 'Slot waktu berhasil dihapus.');
    }

    /**
     * Reorder slot (move up/down)
     */
    public function reorder(Request $request, int $id): RedirectResponse
    {
        $templateJam = TemplateJam::findOrFail($id);
        $direction = $request->input('direction');
        
        $periodeId = $templateJam->periode_semester_id;
        $hari = $templateJam->hari->value ?? $templateJam->hari;
        
        $neighbor = null;
        
        if ($direction === 'up') {
            $neighbor = TemplateJam::forPeriode($periodeId)
                ->forHari($hari)
                ->where('urutan', '<', $templateJam->urutan)
                ->orderByDesc('urutan')
                ->first();
        } elseif ($direction === 'down') {
            $neighbor = TemplateJam::forPeriode($periodeId)
                ->forHari($hari)
                ->where('urutan', '>', $templateJam->urutan)
                ->orderBy('urutan')
                ->first();
        }

        if ($neighbor) {
            // Swap urutan
            $temp = $templateJam->urutan;
            $templateJam->urutan = $neighbor->urutan;
            $templateJam->save();
            
            $neighbor->urutan = $temp;
            $neighbor->save();
        }

        return redirect()
            ->route('admin.template-jam.index', [
                'periode_id' => $periodeId,
                'hari' => $hari,
            ]);
    }

    /**
     * Copy template from another periode
     */
    public function copy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_periode_id' => 'required|exists:periode_semester,id',
            'to_periode_id' => 'required|exists:periode_semester,id|different:from_periode_id',
        ]);

        // Check if target already has template
        $existingCount = TemplateJam::forPeriode($validated['to_periode_id'])->count();
        if ($existingCount > 0) {
            return redirect()
                ->route('admin.template-jam.index', ['periode_id' => $validated['to_periode_id']])
                ->with('error', 'Periode tujuan sudah memiliki template jam. Hapus terlebih dahulu jika ingin copy.');
        }

        $count = TemplateJam::copyFromPeriode(
            $validated['from_periode_id'],
            $validated['to_periode_id']
        );

        return redirect()
            ->route('admin.template-jam.index', ['periode_id' => $validated['to_periode_id']])
            ->with('success', "Berhasil menyalin {$count} slot waktu.");
    }

    /**
     * Helper: Reorder slots after deletion
     */
    private function reorderSlots(int $periodeId, string $hari): void
    {
        $slots = TemplateJam::forPeriode($periodeId)
            ->forHari($hari)
            ->orderBy('urutan')
            ->get();

        foreach ($slots as $index => $slot) {
            $slot->update(['urutan' => $index + 1]);
        }
    }
}
