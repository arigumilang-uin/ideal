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
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 text-emerald-600 mb-2 inline-block">Form Absensi</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-emerald-600"></i> {{ $jadwal->mataPelajaran->nama_mapel }}
                </h1>
                <p class="text-sm text-slate-500 mt-1 m-0">
                    {{ $jadwal->kelas->nama_kelas }} &bull; {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }} &bull; {{ $jadwal->waktu }}
                </p>
            </div>
            <a href="{{ route('absensi.index') }}" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Form Absensi --}}
        <form action="{{ route('absensi.store') }}" method="POST" id="absensiForm">
            @csrf
            <input type="hidden" name="jadwal_mengajar_id" value="{{ $jadwal->id }}">
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">

            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                {{-- Quick Actions --}}
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <div class="text-sm text-slate-600">
                        <i class="fas fa-users mr-1"></i> Total: <strong>{{ $siswaList->count() }}</strong> siswa
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="setAllStatus('Hadir')" class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 text-xs font-bold hover:bg-emerald-200 transition border-0 cursor-pointer">
                            <i class="fas fa-check-double mr-1"></i> Semua Hadir
                        </button>
                        <button type="button" onclick="setAllStatus('Alfa')" class="px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 text-xs font-bold hover:bg-rose-200 transition border-0 cursor-pointer">
                            <i class="fas fa-times-circle mr-1"></i> Semua Alfa
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider w-12">#</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider w-24">Hadir</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider w-24">Sakit</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider w-24">Izin</th>
                                <th class="px-4 py-3 text-center text-[11px] font-bold text-slate-600 uppercase tracking-wider w-24">Alfa</th>
                                <th class="px-4 py-3 text-left text-[11px] font-bold text-slate-600 uppercase tracking-wider w-48">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswaList as $index => $siswa)
                                @php
                                    $existing = $existingAbsensi->get($siswa->id);
                                    $currentStatus = $existing?->status->value ?? 'Hadir';
                                    $currentKeterangan = $existing?->keterangan ?? '';
                                @endphp
                                <tr class="hover:bg-slate-50 transition" data-siswa-id="{{ $siswa->id }}">
                                    <td class="px-4 py-3 text-sm text-slate-600">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                                {{ strtoupper(substr($siswa->nama_siswa, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-slate-800 text-sm">{{ $siswa->nama_siswa }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $siswa->nisn }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach(['Hadir', 'Sakit', 'Izin', 'Alfa'] as $status)
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" 
                                                       name="absensi[{{ $siswa->id }}][status]" 
                                                       value="{{ $status }}"
                                                       {{ $currentStatus === $status ? 'checked' : '' }}
                                                       class="status-radio sr-only peer">
                                                <span class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition-all
                                                    peer-checked:scale-110
                                                    @if($status === 'Hadir') border-emerald-300 peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-checked:text-white @endif
                                                    @if($status === 'Sakit') border-amber-300 peer-checked:bg-amber-500 peer-checked:border-amber-500 peer-checked:text-white @endif
                                                    @if($status === 'Izin') border-blue-300 peer-checked:bg-blue-500 peer-checked:border-blue-500 peer-checked:text-white @endif
                                                    @if($status === 'Alfa') border-rose-300 peer-checked:bg-rose-500 peer-checked:border-rose-500 peer-checked:text-white @endif
                                                ">
                                                    @if($status === 'Hadir')<i class="fas fa-check text-xs"></i>@endif
                                                    @if($status === 'Sakit')<i class="fas fa-heart text-xs"></i>@endif
                                                    @if($status === 'Izin')<i class="fas fa-file text-xs"></i>@endif
                                                    @if($status === 'Alfa')<i class="fas fa-times text-xs"></i>@endif
                                                </span>
                                            </label>
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3">
                                        <input type="text" 
                                               name="absensi[{{ $siswa->id }}][keterangan]"
                                               value="{{ $currentKeterangan }}"
                                               placeholder="Opsional..."
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Submit Button --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-slate-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Status <span class="text-rose-600 font-bold">Alfa</span> akan otomatis tercatat sebagai pelanggaran
                        </div>
                        <button type="submit" class="px-8 py-3 rounded-xl bg-emerald-600 text-white font-bold text-sm hover:bg-emerald-700 transition shadow-lg border-0 cursor-pointer">
                            <i class="fas fa-save mr-2"></i> Simpan Absensi
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function setAllStatus(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
    });
}
</script>

<style>
    .status-radio:focus + span {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
</style>
@endsection
