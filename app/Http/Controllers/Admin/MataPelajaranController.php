<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Mata Pelajaran Controller (Admin)
 * 
 * CRUD untuk master data mata pelajaran.
 * Sekarang mata pelajaran terikat ke kurikulum.
 */
class MataPelajaranController extends Controller
{
    /**
     * Display list of mata pelajaran
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $kurikulumId = $request->input('kurikulum_id');
        
        $query = MataPelajaran::with('kurikulum')
            ->search($search);
        
        if ($kurikulumId) {
            $query->forKurikulum($kurikulumId);
        }
        
        $mataPelajaran = $query->orderBy('nama_mapel')->get();
        
        // Get all kurikulums for filter
        $kurikulums = Kurikulum::active()->orderBy('nama')->get();

        return view('admin.mata-pelajaran.index', [
            'mataPelajaran' => $mataPelajaran,
            'kurikulums' => $kurikulums,
            'search' => $search,
            'kurikulumId' => $kurikulumId,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): View
    {
        $kurikulums = Kurikulum::active()->orderBy('nama')->get();
        
        return view('admin.mata-pelajaran.create', [
            'kurikulums' => $kurikulums,
        ]);
    }

    /**
     * Store new mata pelajaran
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok' => 'nullable|in:A,B,C',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = true;

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
        $mataPelajaran = MataPelajaran::with('kurikulum')->findOrFail($id);
        $kurikulums = Kurikulum::active()->orderBy('nama')->get();
        
        return view('admin.mata-pelajaran.edit', [
            'mataPelajaran' => $mataPelajaran,
            'kurikulums' => $kurikulums,
        ]);
    }

    /**
     * Update mata pelajaran
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $mataPelajaran = MataPelajaran::findOrFail($id);

        $validated = $request->validate([
            'kurikulum_id' => 'required|exists:kurikulum,id',
            'nama_mapel' => 'required|string|max:100',
            'kode_mapel' => 'nullable|string|max:20',
            'kelompok' => 'nullable|in:A,B,C',
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

    /**
     * API: Get mapel for a specific kurikulum (untuk AJAX dropdown)
     */
    public function getByKurikulum(int $kurikulumId)
    {
        $mapels = MataPelajaran::forKurikulum($kurikulumId)
            ->active()
            ->orderBy('nama_mapel')
            ->get(['id', 'nama_mapel', 'kode_mapel']);

        return response()->json($mapels);
    }
}
