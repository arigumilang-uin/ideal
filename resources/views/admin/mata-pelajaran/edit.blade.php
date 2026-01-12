@extends('layouts.app')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: { extend: { colors: { primary: '#0f172a' } } },
        corePlugins: { preflight: false }
    }
</script>

<div class="min-h-screen p-6 bg-slate-50">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.mata-pelajaran.index') }}" class="text-sm text-slate-500 hover:text-slate-700 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-amber-50">
                <h2 class="text-lg font-bold text-amber-900 m-0">Edit Mata Pelajaran</h2>
            </div>
            <form action="{{ route('admin.mata-pelajaran.update', $mataPelajaran->id) }}" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nama Mata Pelajaran *</label>
                    <input type="text" name="nama_mapel" value="{{ old('nama_mapel', $mataPelajaran->nama_mapel) }}" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                    @error('nama_mapel')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kode Mapel</label>
                    <input type="text" name="kode_mapel" value="{{ old('kode_mapel', $mataPelajaran->kode_mapel) }}" maxlength="20" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                    @error('kode_mapel')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none resize-none">{{ old('deskripsi', $mataPelajaran->deskripsi) }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $mataPelajaran->is_active ? 'checked' : '' }} id="isActive" class="rounded border-slate-300">
                    <label for="isActive" class="text-sm text-slate-700">Aktif</label>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-amber-600 text-white font-bold text-sm hover:bg-amber-700 border-0 cursor-pointer">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
