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
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 text-indigo-600 mb-2 inline-block">Laporan</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-chart-bar text-indigo-600"></i> Rekap Absensi
                </h1>
                <p class="text-sm text-slate-500 mt-1 m-0">Rekap kehadiran siswa per kelas</p>
            </div>
            <a href="{{ route('absensi.index') }}" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        {{-- Filter Form --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6 mb-6">
            <form action="{{ route('absensi.report') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ $selectedKelasId == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }} - {{ $kelas->jurusan->nama_jurusan ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-[160px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>
                <div class="w-[160px]">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                </div>
                <button type="submit" class="px-6 py-2 rounded-lg bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition border-0 cursor-pointer">
                    <i class="fas fa-search mr-1"></i> Tampilkan
                </button>
            </form>
        </div>

        {{-- Results --}}
        @if($rekap)
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="font-bold text-slate-800 m-0">Rekap Kehadiran: {{ $kelasList->find($selectedKelasId)?->nama_kelas }}</h3>
                    <p class="text-sm text-slate-500 m-0">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider w-20">Total</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-emerald-600 uppercase tracking-wider w-20">Hadir</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-amber-600 uppercase tracking-wider w-20">Sakit</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-blue-600 uppercase tracking-wider w-20">Izin</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-rose-600 uppercase tracking-wider w-20">Alfa</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider w-24">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rekap as $index => $item)
                                @php
                                    $persenHadir = $item['total_hari'] > 0 
                                        ? round(($item['hadir'] / $item['total_hari']) * 100, 1) 
                                        : 0;
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $item['siswa']->nama_siswa }}</td>
                                    <td class="px-4 py-3 text-center text-sm">{{ $item['total_hari'] }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-emerald-600 font-bold">{{ $item['hadir'] }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-amber-600">{{ $item['sakit'] }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-blue-600">{{ $item['izin'] }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-rose-600 font-bold">{{ $item['alfa'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-xs font-bold 
                                            {{ $persenHadir >= 90 ? 'bg-emerald-100 text-emerald-700' : ($persenHadir >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                            {{ $persenHadir }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-filter text-3xl text-slate-400"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700 mb-2">Pilih Kelas</h3>
                <p class="text-slate-500">Pilih kelas dan rentang tanggal untuk melihat rekap absensi.</p>
            </div>
        @endif
    </div>
</div>
@endsection
