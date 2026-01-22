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
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 text-indigo-600 mb-2 inline-block">Detail Absensi</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-indigo-600"></i> {{ $jadwal->mataPelajaran->nama_mapel }}
                </h1>
                <p class="text-sm text-slate-500 mt-1 m-0">
                    {{ $jadwal->kelas->nama_kelas }} &bull; {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('absensi.create', ['jadwalId' => $jadwal->id, 'tanggal' => $tanggal]) }}" 
                   class="px-4 py-2 rounded-lg bg-amber-100 text-amber-700 text-xs font-bold border border-amber-200 hover:bg-amber-200 no-underline">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('absensi.index') }}" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Statistik Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-xl p-4 shadow border border-slate-200 text-center">
                <div class="text-2xl font-bold text-slate-800">{{ $statistik['total'] }}</div>
                <div class="text-[10px] text-slate-500 uppercase font-bold">Total</div>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4 shadow border border-emerald-200 text-center">
                <div class="text-2xl font-bold text-emerald-600">{{ $statistik['hadir'] }}</div>
                <div class="text-[10px] text-emerald-600 uppercase font-bold">Hadir</div>
            </div>
            <div class="bg-amber-50 rounded-xl p-4 shadow border border-amber-200 text-center">
                <div class="text-2xl font-bold text-amber-600">{{ $statistik['sakit'] }}</div>
                <div class="text-[10px] text-amber-600 uppercase font-bold">Sakit</div>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 shadow border border-blue-200 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $statistik['izin'] }}</div>
                <div class="text-[10px] text-blue-600 uppercase font-bold">Izin</div>
            </div>
            <div class="bg-rose-50 rounded-xl p-4 shadow border border-rose-200 text-center">
                <div class="text-2xl font-bold text-rose-600">{{ $statistik['alfa'] }}</div>
                <div class="text-[10px] text-rose-600 uppercase font-bold">Alfa</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider w-12">#</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider">NISN</th>
                            <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($absensi as $index => $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $item->siswa->nama_siswa }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $item->siswa->nisn }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $item->status->badgeClasses() }}">
                                        {{ $item->status->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $item->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    Belum ada data absensi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
