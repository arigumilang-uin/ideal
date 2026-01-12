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
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 text-indigo-600 mb-2 inline-block">Master Data</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0">Jadwal Mengajar</h1>
                <p class="text-sm text-slate-500 m-0">{{ $current_semester }} - {{ $current_tahun_ajaran }}</p>
            </div>
            <a href="{{ route('admin.jadwal-mengajar.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 no-underline">
                <i class="fas fa-plus mr-1"></i> Tambah Jadwal
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            {{-- Filter --}}
            <div class="px-6 py-4 border-b border-slate-200">
                <form action="{{ route('admin.jadwal-mengajar.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
                    <div class="w-[180px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Kelas</label>
                        <select name="kelas_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ $filters['kelas_id'] == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-[200px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Guru</label>
                        <select name="guru_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
                            <option value="">Semua Guru</option>
                            @foreach($guru as $g)
                                <option value="{{ $g->id }}" {{ $filters['guru_id'] == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-[140px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Hari</label>
                        <select name="hari" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
                            <option value="">Semua Hari</option>
                            @foreach($hari as $h)
                                <option value="{{ $h['value'] }}" {{ $filters['hari'] == $h['value'] ? 'selected' : '' }}>{{ $h['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-bold border-0 cursor-pointer hover:bg-slate-200">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </form>
            </div>

            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Hari</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Mata Pelajaran</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Kelas</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Guru</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jadwal as $j)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded bg-indigo-100 text-indigo-700 text-xs font-bold">{{ $j->hari->value }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $j->waktu }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $j->mataPelajaran->nama_mapel }}</td>
                            <td class="px-4 py-3 text-sm">{{ $j->kelas->nama_kelas }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $j->guru->nama }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('admin.jadwal-mengajar.edit', $j->id) }}" class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-xs hover:bg-amber-200 no-underline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.jadwal-mengajar.destroy', $j->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 rounded bg-rose-100 text-rose-700 text-xs hover:bg-rose-200 border-0 cursor-pointer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada jadwal</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-200">{{ $jadwal->links() }}</div>
        </div>
    </div>
</div>
@endsection
