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
            <a href="{{ route('admin.periode-semester.index') }}" class="text-xs font-bold text-slate-500 hover:text-emerald-600 no-underline transition-colors mb-2 inline-flex items-center gap-1">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <h1 class="text-2xl font-bold text-slate-800 m-0">Tambah Periode Semester</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <form action="{{ route('admin.periode-semester.store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3 text-sm text-emerald-700">
                    <strong>Info:</strong> Nama periode akan otomatis dihasilkan dari Semester + Tahun Ajaran.
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Semester *</label>
                        <select name="semester" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none bg-white transition-all">
                            <option value="Ganjil" {{ old('semester') === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ old('semester') === 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tahun Ajaran *</label>
                        <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', date('Y') . '/' . (date('Y')+1)) }}" required placeholder="2025/2026" 
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-200 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all">
                        @error('tahun_ajaran')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Mulai *</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required 
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-200 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all">
                        @error('tanggal_mulai')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Tanggal Selesai *</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required 
                               class="w-full px-4 py-2.5 rounded-lg border border-slate-200 text-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none transition-all">
                        @error('tanggal_selesai')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 active:bg-emerald-800 border-0 cursor-pointer shadow-sm transition-all">
                        <i class="fas fa-save mr-1"></i> Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
