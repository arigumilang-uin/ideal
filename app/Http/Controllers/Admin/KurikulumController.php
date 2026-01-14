<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Kurikulum Controller
 * 
 * CRUD untuk master data kurikulum
 */
class KurikulumController extends Controller
{
    /**
     * Display list of kurikulum
     */
    public function index(): View
    {
        $kurikulums = Kurikulum::withCount(['mataPelajaran' => function($q) {
            $q->where('is_active', true);
        }])
        ->orderBy('tahun_berlaku', 'desc')
        ->get();

        return view('admin.kurikulum.index', [
            'kurikulums' => $kurikulums,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.kurikulum.create');
    }

    /**
     * Store new kurikulum
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:kurikulum,kode',
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_berlaku' => 'nullable|integer|min:2000|max:2100',
        ]);

        $validated['is_active'] = true;

        Kurikulum::create($validated);

        return redirect()
            ->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $kurikulum = Kurikulum::findOrFail($id);

        return view('admin.kurikulum.edit', [
            'kurikulum' => $kurikulum,
        ]);
    }

    /**
     * Update kurikulum
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $kurikulum = Kurikulum::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:kurikulum,kode,' . $id,
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_berlaku' => 'nullable|integer|min:2000|max:2100',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $kurikulum->update($validated);

        return redirect()
            ->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil diperbarui.');
    }

    /**
     * Delete kurikulum
     */
    public function destroy(int $id): RedirectResponse
    {
        $kurikulum = Kurikulum::findOrFail($id);
        
        // Check if kurikulum has mata pelajaran
        if ($kurikulum->mataPelajaran()->exists()) {
            return redirect()
                ->route('admin.kurikulum.index')
                ->with('error', 'Tidak dapat menghapus kurikulum yang memiliki mata pelajaran.');
        }

        $kurikulum->delete();

        return redirect()
            ->route('admin.kurikulum.index')
            ->with('success', 'Kurikulum berhasil dihapus.');
    }
}
