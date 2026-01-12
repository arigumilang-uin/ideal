<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Mata Pelajaran Controller (Admin)
 * 
 * CRUD untuk master data mata pelajaran.
 */
class MataPelajaranController extends Controller
{
    /**
     * Display list of mata pelajaran
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        
        $mataPelajaran = MataPelajaran::query()
            ->search($search)
            ->orderBy('nama_mapel')
            ->paginate(20);

        return view('admin.mata-pelajaran.index', [
            'mataPelajaran' => $mataPelajaran,
            'search' => $search,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        return view('admin.mata-pelajaran.create');
    }

    /**
     * Store new mata pelajaran
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20|unique:mata_pelajaran,kode_mapel',
            'deskripsi' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        MataPelajaran::create($validated);

        return redirect()
            ->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id): View
    {
        $mataPelajaran = MataPelajaran::findOrFail($id);
        
        return view('admin.mata-pelajaran.edit', [
            'mataPelajaran' => $mataPelajaran,
        ]);
    }

    /**
     * Update mata pelajaran
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $mataPelajaran = MataPelajaran::findOrFail($id);

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20|unique:mata_pelajaran,kode_mapel,' . $id,
            'deskripsi' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $mataPelajaran->update($validated);

        return redirect()
            ->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Delete mata pelajaran
     */
    public function destroy(int $id): RedirectResponse
    {
        $mataPelajaran = MataPelajaran::findOrFail($id);
        
        // Check if used in jadwal
        if ($mataPelajaran->jadwalMengajar()->exists()) {
            return back()->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan di jadwal.');
        }

        $mataPelajaran->delete();

        return redirect()
            ->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
