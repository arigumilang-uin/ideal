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
                    <i class="fas fa-clipboard-check text-emerald-600"></i> Jadwal Mengajar Saya
                </h1>
                <p class="text-sm text-slate-500 mt-1 m-0">Semester {{ $currentSemester }} - {{ $currentTahunAjaran }}</p>
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

        {{-- Jadwal per Hari --}}
        @if($jadwalByHari->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-calendar-times text-3xl text-slate-400"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Tidak Ada Jadwal</h3>
                <p class="text-slate-500">Anda belum memiliki jadwal mengajar yang terdaftar.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($jadwalByHari as $hari => $jadwalList)
                    @php
                        $isToday = $hari === $hariIni;
                    @endphp
                    <div class="bg-white rounded-2xl shadow-lg border {{ $isToday ? 'border-emerald-300 ring-2 ring-emerald-100' : 'border-slate-200' }} overflow-hidden">
                        {{-- Day Header --}}
                        <div class="px-6 py-3 {{ $isToday ? 'bg-emerald-50' : 'bg-slate-50' }} border-b {{ $isToday ? 'border-emerald-100' : 'border-slate-100' }} flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-lg {{ $isToday ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-bold text-sm">
                                    {{ substr($hari, 0, 3) }}
                                </span>
                                <div>
                                    <h3 class="font-bold text-slate-800 text-lg m-0">{{ $hari }}</h3>
                                    @if($isToday)
                                        <span class="text-[10px] font-bold text-emerald-600 uppercase">Hari Ini</span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-sm text-slate-500">{{ $jadwalList->count() }} jadwal</span>
                        </div>

                        {{-- Jadwal List --}}
                        <div class="divide-y divide-slate-100">
                            @foreach($jadwalList as $jadwal)
                                <div class="px-6 py-4 flex justify-between items-center hover:bg-slate-50 transition">
                                    <div class="flex items-center gap-4">
                                        <div class="w-auto min-w-[120px] text-center">
                                            @if($jadwal->time_display ?? false)
                                                {{-- New format: shows all sessions with break info --}}
                                                <div class="text-sm font-bold text-slate-700">{{ $jadwal->time_display }}</div>
                                                @if(($jadwal->session_count ?? 1) > 1)
                                                    <div class="text-[10px] text-amber-600 font-medium">{{ $jadwal->session_count }} sesi</div>
                                                @endif
                                            @else
                                                {{-- Fallback to template_jam --}}
                                                @php
                                                    $jamMulai = $jadwal->templateJam?->jam_mulai;
                                                    $jamSelesai = $jadwal->templateJam?->jam_selesai;
                                                    $jamMulai = $jamMulai instanceof \DateTime ? $jamMulai->format('H:i') : substr($jamMulai ?? '', 0, 5);
                                                    $jamSelesai = $jamSelesai instanceof \DateTime ? $jamSelesai->format('H:i') : substr($jamSelesai ?? '', 0, 5);
                                                @endphp
                                                <div class="text-sm font-bold text-slate-700">{{ $jamMulai }}</div>
                                                <div class="text-[10px] text-slate-400">{{ $jamSelesai }}</div>
                                            @endif
                                        </div>
                                        <div class="w-px h-10 bg-slate-200"></div>
                                        <div>
                                            <div class="font-semibold text-slate-800">{{ $jadwal->mataPelajaran->nama_mapel }}</div>
                                            <div class="text-sm text-slate-500">
                                                {{ $jadwal->kelas->nama_kelas }} 
                                                <span class="text-slate-300">•</span> 
                                                {{ $jadwal->kelas->jurusan->nama_jurusan ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if(isset($jadwal->totalPertemuan))
                                            <span class="text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
                                                {{ $jadwal->totalPertemuan }} pertemuan
                                            </span>
                                        @endif
                                        <a href="{{ route('absensi.grid', $jadwal->id) }}" 
                                           class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 no-underline transition">
                                            <i class="fas fa-table mr-1"></i> Kelola Absensi
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
