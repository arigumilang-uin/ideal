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
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('admin.jadwal-mengajar.index') }}" class="text-sm text-slate-500 hover:text-slate-700 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-indigo-50">
                <h2 class="text-lg font-bold text-indigo-900 m-0">Tambah Jadwal Mengajar</h2>
            </div>
            <form action="{{ route('admin.jadwal-mengajar.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Guru *</label>
                        <select name="user_id" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                            <option value="">-- Pilih Guru --</option>
                            @foreach($guru as $g)
                                <option value="{{ $g->id }}" {{ old('user_id') == $g->id ? 'selected' : '' }}>{{ $g->nama }} ({{ $g->username }})</option>
                            @endforeach
                        </select>
                        @error('user_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Mata Pelajaran *</label>
                        <select name="mata_pelajaran_id" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($mata_pelajaran as $mp)
                                <option value="{{ $mp->id }}" {{ old('mata_pelajaran_id') == $mp->id ? 'selected' : '' }}>{{ $mp->nama_mapel }}</option>
                            @endforeach
                        </select>
                        @error('mata_pelajaran_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kelas *</label>
                    <select name="kelas_id" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan ?? '' }}</option>
                        @endforeach
                    </select>
                    @error('kelas_id')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Hari *</label>
                        <select name="hari" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                            @foreach($hari as $h)
                                <option value="{{ $h['value'] }}" {{ old('hari') == $h['value'] ? 'selected' : '' }}>{{ $h['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Jam Mulai *</label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai', '07:00') }}" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                        @error('jam_mulai')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Jam Selesai *</label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai', '08:30') }}" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                        @error('jam_selesai')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Semester *</label>
                        <select name="semester" required class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                            @foreach($semester as $s)
                                <option value="{{ $s['value'] }}" {{ old('semester', $current_semester) == $s['value'] ? 'selected' : '' }}>{{ $s['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran *</label>
                        <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $current_tahun_ajaran) }}" required placeholder="2025/2026" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                        @error('tahun_ajaran')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 border-0 cursor-pointer">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
