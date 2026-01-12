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
    <div class="max-w-6xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-purple-50 px-2 py-0.5 rounded border border-purple-100 text-purple-600 mb-2 inline-block">Master Data</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0">Mata Pelajaran</h1>
            </div>
            <a href="{{ route('admin.mata-pelajaran.create') }}" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-xs font-bold hover:bg-purple-700 no-underline">
                <i class="fas fa-plus mr-1"></i> Tambah Mapel
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
            <div class="px-6 py-4 border-b border-slate-200">
                <form action="{{ route('admin.mata-pelajaran.index') }}" method="GET" class="flex gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama/kode mapel..." class="flex-1 px-4 py-2 rounded-lg border border-slate-200 text-sm">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-sm font-bold border-0 cursor-pointer hover:bg-slate-200">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase w-16">Kode</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Nama Mata Pelajaran</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase w-24">Status</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mataPelajaran as $mp)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-mono text-slate-600">{{ $mp->kode_mapel ?? '-' }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $mp->nama_mapel }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($mp->is_active)
                                    <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">Aktif</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    <a href="{{ route('admin.mata-pelajaran.edit', $mp->id) }}" class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-xs hover:bg-amber-200 no-underline">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.mata-pelajaran.destroy', $mp->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 rounded bg-rose-100 text-rose-700 text-xs hover:bg-rose-200 border-0 cursor-pointer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-200">{{ $mataPelajaran->links() }}</div>
        </div>
    </div>
</div>
@endsection
