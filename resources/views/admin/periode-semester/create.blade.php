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
            <a href="{{ route('admin.periode-semester.index') }}" class="text-sm text-slate-500 hover:text-slate-700 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-teal-50">
                <h2 class="text-lg font-bold text-teal-900 m-0">Tambah Periode Semester</h2>
            </div>
            <form action="{{ route('admin.periode-semester.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Nama Periode *</label>
                    <input type="text" name="nama_periode" value="{{ old('nama_periode') }}" required placeholder="Contoh: Semester Ganjil 2025/2026" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                    @error('nama_periode')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Semester *</label>
                        <select name="semester" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                            <option value="Ganjil" {{ old('semester') === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ old('semester') === 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran *</label>
                        <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', date('Y') . '/' . (date('Y')+1)) }}" required placeholder="2025/2026" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        @error('tahun_ajaran')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tanggal Mulai *</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        @error('tanggal_mulai')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tanggal Selesai *</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none">
                        @error('tanggal_selesai')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-teal-600 text-white font-bold text-sm hover:bg-teal-700 border-0 cursor-pointer">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
