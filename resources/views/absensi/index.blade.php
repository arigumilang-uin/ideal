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
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 text-emerald-600 mb-2 inline-block">Absensi</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-clipboard-check text-emerald-600"></i> Jadwal Hari Ini
                </h1>
                <p class="text-sm text-slate-500 mt-1 m-0">{{ $tanggal->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <a href="{{ route('absensi.report') }}" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline flex items-center gap-2">
                <i class="fas fa-chart-bar"></i> Laporan Rekap
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Jadwal Cards --}}
        @if($jadwalHariIni->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-calendar-times text-3xl text-slate-400"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Tidak Ada Jadwal</h3>
                <p class="text-slate-500">Anda tidak memiliki jadwal mengajar hari ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($jadwalHariIni as $jadwal)
                    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-shadow">
                        {{-- Card Header --}}
                        <div class="px-6 py-4 {{ $jadwal->sudah_diabsen ? 'bg-emerald-50 border-b border-emerald-100' : 'bg-amber-50 border-b border-amber-100' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg text-slate-800 m-0">{{ $jadwal->mataPelajaran->nama_mapel }}</h3>
                                    <p class="text-sm text-slate-600 mt-1 m-0">{{ $jadwal->kelas->nama_kelas }} - {{ $jadwal->kelas->jurusan->nama_jurusan ?? '' }}</p>
                                </div>
                                @if($jadwal->sudah_diabsen)
                                    <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase">
                                        <i class="fas fa-check mr-1"></i>Sudah
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold uppercase">
                                        <i class="fas fa-clock mr-1"></i>Belum
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Card Body --}}
                        <div class="p-6">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-clock text-indigo-600 text-xl"></i>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Waktu</div>
                                    <div class="font-bold text-slate-800">{{ $jadwal->waktu }}</div>
                                </div>
                            </div>

                            @if($jadwal->sudah_diabsen && isset($jadwal->statistik))
                                {{-- Statistik --}}
                                <div class="grid grid-cols-4 gap-2 p-3 rounded-lg bg-slate-50 mb-4">
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-emerald-600">{{ $jadwal->statistik['hadir'] }}</div>
                                        <div class="text-[9px] text-slate-500 uppercase">Hadir</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-amber-600">{{ $jadwal->statistik['sakit'] }}</div>
                                        <div class="text-[9px] text-slate-500 uppercase">Sakit</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-blue-600">{{ $jadwal->statistik['izin'] }}</div>
                                        <div class="text-[9px] text-slate-500 uppercase">Izin</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-lg font-bold text-rose-600">{{ $jadwal->statistik['alfa'] }}</div>
                                        <div class="text-[9px] text-slate-500 uppercase">Alfa</div>
                                    </div>
                                </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex gap-2">
                                @if($jadwal->sudah_diabsen)
                                    <a href="{{ route('absensi.show', ['jadwalId' => $jadwal->id, 'tanggal' => $tanggal->toDateString()]) }}" 
                                       class="flex-1 px-4 py-3 rounded-xl bg-slate-100 text-slate-700 text-center text-sm font-bold hover:bg-slate-200 transition no-underline">
                                        <i class="fas fa-eye mr-1"></i> Lihat
                                    </a>
                                    <a href="{{ route('absensi.create', ['jadwalId' => $jadwal->id, 'tanggal' => $tanggal->toDateString()]) }}" 
                                       class="flex-1 px-4 py-3 rounded-xl bg-amber-100 text-amber-700 text-center text-sm font-bold hover:bg-amber-200 transition no-underline">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                @else
                                    <a href="{{ route('absensi.create', ['jadwalId' => $jadwal->id, 'tanggal' => $tanggal->toDateString()]) }}" 
                                       class="flex-1 px-4 py-3 rounded-xl bg-emerald-600 text-white text-center text-sm font-bold hover:bg-emerald-700 transition no-underline">
                                        <i class="fas fa-clipboard-check mr-1"></i> Absen Sekarang
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
