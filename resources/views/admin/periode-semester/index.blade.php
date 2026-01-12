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
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-teal-50 px-2 py-0.5 rounded border border-teal-100 text-teal-600 mb-2 inline-block">Konfigurasi</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0">Periode Semester</h1>
                <p class="text-sm text-slate-500 m-0">Kelola tanggal mulai dan selesai semester</p>
            </div>
            <a href="{{ route('admin.periode-semester.create') }}" class="px-4 py-2 rounded-lg bg-teal-600 text-white text-xs font-bold hover:bg-teal-700 no-underline">
                <i class="fas fa-plus mr-1"></i> Tambah Periode
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
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase">Nama Periode</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase w-28">Semester</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase w-24">Status</th>
                        <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($periodes as $p)
                        <tr class="hover:bg-slate-50 {{ $p->is_active ? 'bg-teal-50' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $p->nama_periode }}</div>
                                <div class="text-[11px] text-slate-500">{{ $p->tahun_ajaran }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold 
                                    {{ $p->semester->value === 'Ganjil' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $p->semester->value }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-slate-600">
                                {{ $p->tanggal_mulai->format('d/m/Y') }} - {{ $p->tanggal_selesai->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($p->is_active)
                                    <span class="px-2 py-1 rounded-full bg-teal-100 text-teal-700 text-[10px] font-bold">
                                        <i class="fas fa-check mr-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    @if(!$p->is_active)
                                        <form action="{{ route('admin.periode-semester.setActive', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="px-2 py-1 rounded bg-teal-100 text-teal-700 text-xs hover:bg-teal-200 border-0 cursor-pointer" title="Set Aktif">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.periode-semester.generatePertemuan', $p->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="px-2 py-1 rounded bg-indigo-100 text-indigo-700 text-xs hover:bg-indigo-200 border-0 cursor-pointer" title="Generate Pertemuan">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.periode-semester.edit', $p->id) }}" class="px-2 py-1 rounded bg-amber-100 text-amber-700 text-xs hover:bg-amber-200 no-underline" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.periode-semester.destroy', $p->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 rounded bg-rose-100 text-rose-700 text-xs hover:bg-rose-200 border-0 cursor-pointer" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            <div class="text-3xl mb-2">📅</div>
                            Belum ada periode semester. <a href="{{ route('admin.periode-semester.create') }}" class="text-teal-600 font-bold">Buat sekarang</a>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 p-4 rounded-xl bg-slate-100 border border-slate-200">
            <h4 class="font-bold text-slate-700 text-sm mb-2"><i class="fas fa-info-circle mr-1"></i> Panduan</h4>
            <ul class="text-sm text-slate-600 space-y-1 m-0 pl-4">
                <li>Buat periode semester baru dengan tanggal mulai dan selesai</li>
                <li>Set satu periode sebagai <strong>Aktif</strong> (periode yang sedang berjalan)</li>
                <li>Klik <strong>Generate Pertemuan</strong> untuk membuat instance pertemuan berdasarkan jadwal</li>
            </ul>
        </div>
    </div>
</div>
@endsection
