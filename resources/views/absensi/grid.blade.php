@extends('layouts.app')

@section('content')
<style>
    .absensi-grid { overflow-x: auto; }
    .absensi-grid table { border-collapse: separate; border-spacing: 0; }
    .absensi-grid th, .absensi-grid td { border: 1px solid #e2e8f0; }
    
    /* Sticky columns for full grid */
    .absensi-grid .sticky-left { position: sticky; background: white; z-index: 10; box-shadow: 2px 0 4px rgba(0,0,0,0.05); }
    .absensi-grid .col-no { left: 0; min-width: 35px; }
    .absensi-grid .col-nama { left: 35px; min-width: 140px; }
    .absensi-grid .col-nisn { left: 175px; min-width: 90px; }
    .absensi-grid .sticky-right { position: sticky; background: white; z-index: 10; box-shadow: -2px 0 4px rgba(0,0,0,0.05); }
    .absensi-grid .col-tidakhadir { right: 70px; min-width: 50px; }
    .absensi-grid .col-ket { right: 0; min-width: 70px; }
    .absensi-grid thead th { position: sticky; top: 0; background: #f1f5f9; z-index: 5; }
    .absensi-grid thead th.sticky-left, .absensi-grid thead th.sticky-right { z-index: 15; background: #f1f5f9; }
    
    /* Status select styling */
    .status-select { 
        appearance: none; padding: 2px 14px 2px 4px; font-size: 10px; width: 32px; text-align: center;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 2px center; background-repeat: no-repeat; background-size: 10px;
    }
    .status-select.hadir { background-color: #d1fae5; color: #065f46; }
    .status-select.sakit { background-color: #fef3c7; color: #92400e; }
    .status-select.izin { background-color: #dbeafe; color: #1e40af; }
    .status-select.alfa { background-color: #fee2e2; color: #991b1b; }
    
    /* Status button group */
    .status-btn { padding: 6px 12px; font-size: 11px; font-weight: 600; border: 1px solid #e2e8f0; cursor: pointer; transition: all 0.15s; }
    .status-btn:first-child { border-radius: 6px 0 0 6px; }
    .status-btn:last-child { border-radius: 0 6px 6px 0; }
    .status-btn:not(:first-child) { margin-left: -1px; }
    .status-btn.active-hadir { background: #10b981; color: white; border-color: #10b981; }
    .status-btn.active-sakit { background: #f59e0b; color: white; border-color: #f59e0b; }
    .status-btn.active-izin { background: #3b82f6; color: white; border-color: #3b82f6; }
    .status-btn.active-alfa { background: #ef4444; color: white; border-color: #ef4444; }
    .status-btn:hover:not([class*="active-"]) { background: #f1f5f9; }
</style>

<div class="min-h-screen p-4 bg-slate-50" x-data="absensiPage()">
    <div class="max-w-full mx-auto">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-200">
            <div>
                <a href="{{ route('absensi.index') }}" class="text-xs text-slate-500 hover:text-slate-700 no-underline mb-1 inline-block">
                    ← Kembali
                </a>
                <h1 class="text-xl font-bold text-slate-800 m-0 flex items-center gap-2">
                    📋 {{ $jadwal->mataPelajaran->nama_mapel }}
                </h1>
                <p class="text-xs text-slate-500 mt-0.5 m-0">
                    {{ $jadwal->kelas->nama_kelas }} • {{ $jadwal->hari->value }} {{ $jadwal->waktu }} • {{ $pertemuanList->count() }} pertemuan
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="mb-4">
            <div class="flex gap-1 bg-slate-100 p-1 rounded-lg inline-flex">
                <button @click="activeTab = 'single'" 
                        :class="activeTab === 'single' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-all">
                    📝 Absensi Pertemuan
                </button>
                <button @click="activeTab = 'grid'" 
                        :class="activeTab === 'grid' ? 'bg-white shadow text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-all">
                    📊 Lihat Semua (Grid)
                </button>
            </div>
        </div>

        {{-- Tab 1: Single Pertemuan Attendance --}}
        <div x-show="activeTab === 'single'" x-cloak>
            {{-- Pertemuan Selector --}}
            <div class="bg-white rounded-xl shadow border border-slate-200 p-4 mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Pertemuan:</label>
                <select x-model="selectedPertemuanId" @change="onPertemuanChange()" 
                        class="w-full max-w-md px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">-- Pilih Pertemuan --</option>
                    @foreach($pertemuanList->sortByDesc('tanggal') as $pertemuan)
                        <option value="{{ $pertemuan->id }}" 
                                {{ $todayPertemuan && $todayPertemuan->id == $pertemuan->id ? 'selected' : '' }}>
                            Pertemuan {{ $pertemuan->pertemuan_ke }} - {{ $pertemuan->hari?->value ?? 'N/A' }}, {{ $pertemuan->tanggal->format('d M Y') }}
                            @if($todayPertemuan && $todayPertemuan->id == $pertemuan->id) ✓ Hari Ini @endif
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Legend --}}
            <div class="mb-3 p-2 rounded-lg bg-white border border-slate-200 flex items-center gap-4 text-xs">
                <span class="font-bold text-slate-600">Status:</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500"></span> H = Hadir</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-500"></span> S = Sakit</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500"></span> I = Izin</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-500"></span> A = Alfa</span>
            </div>

            {{-- Attendance Table (Single Pertemuan) --}}
            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden" x-show="selectedPertemuanId">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600 w-12">No</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600">Nama Siswa</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-slate-600 w-48">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($siswaList as $index => $siswa)
                            <tr class="hover:bg-slate-50" x-data="{ status: getStatus({{ $siswa->id }}) }">
                                <td class="px-3 py-2 text-slate-500 text-xs">{{ $index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-800">{{ $siswa->nama_siswa }}</div>
                                    <div class="text-xs text-slate-400">NISN: {{ $siswa->nisn }}</div>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <div class="inline-flex">
                                        <button type="button" @click="setStatus({{ $siswa->id }}, 'Hadir')" 
                                                :class="getStatus({{ $siswa->id }}) === 'Hadir' ? 'active-hadir' : ''" class="status-btn">H</button>
                                        <button type="button" @click="setStatus({{ $siswa->id }}, 'Sakit')" 
                                                :class="getStatus({{ $siswa->id }}) === 'Sakit' ? 'active-sakit' : ''" class="status-btn">S</button>
                                        <button type="button" @click="setStatus({{ $siswa->id }}, 'Izin')" 
                                                :class="getStatus({{ $siswa->id }}) === 'Izin' ? 'active-izin' : ''" class="status-btn">I</button>
                                        <button type="button" @click="setStatus({{ $siswa->id }}, 'Alfa')" 
                                                :class="getStatus({{ $siswa->id }}) === 'Alfa' ? 'active-alfa' : ''" class="status-btn">A</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Empty State --}}
            <div x-show="!selectedPertemuanId" class="bg-white rounded-xl shadow border border-slate-200 p-8 text-center">
                <div class="text-slate-400 text-4xl mb-3">📅</div>
                <p class="text-slate-600 font-medium">Pilih pertemuan untuk mulai mengabsen</p>
                <p class="text-slate-400 text-sm">Pilih dari dropdown di atas</p>
            </div>

            {{-- Quick Actions --}}
            <div class="mt-4 flex justify-between items-center" x-show="selectedPertemuanId">
                <div class="text-sm text-slate-500">
                    <strong>{{ $siswaList->count() }}</strong> siswa
                </div>
                <button @click="setAllStatus('Hadir')" 
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white font-medium text-sm hover:bg-emerald-700 transition-colors">
                    ✓ Semua Hadir
                </button>
            </div>
        </div>

        {{-- Tab 2: Full Grid View --}}
        <div x-show="activeTab === 'grid'" x-cloak>
            {{-- Legend --}}
            <div class="mb-3 p-2 rounded-lg bg-white border border-slate-200 flex items-center gap-4 text-xs">
                <span class="font-bold text-slate-600">Status:</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-200"></span> H=Hadir</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-200"></span> S=Sakit</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-200"></span> I=Izin</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-200"></span> A=Alfa</span>
                <span class="ml-auto text-slate-400">Auto-save saat diubah</span>
            </div>

            {{-- Grid Table --}}
            <div class="bg-white rounded-xl shadow-lg border border-slate-200">
                <div class="absensi-grid" style="max-height: 65vh; overflow-x: scroll; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    <table class="text-xs" style="min-width: max-content;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-left col-no px-1 py-2 text-center font-bold text-slate-600 bg-slate-100">No</th>
                                <th rowspan="2" class="sticky-left col-nama px-2 py-2 text-left font-bold text-slate-600 bg-slate-100">Nama Siswa</th>
                                <th rowspan="2" class="sticky-left col-nisn px-1 py-2 text-center font-bold text-slate-600 bg-slate-100">NISN</th>
                                <th colspan="{{ $pertemuanList->count() }}" class="px-2 py-1 text-center font-bold text-indigo-700 bg-indigo-50 border-b-0">
                                    Pertemuan ke / {{ $jadwal->hari->value }} / Tanggal
                                </th>
                                <th rowspan="2" class="sticky-right col-tidakhadir px-1 py-2 text-center font-bold text-slate-600 bg-rose-50">
                                    <div class="text-[8px] leading-tight">Tdk</div>
                                    <div class="text-[8px] leading-tight">Hadir</div>
                                </th>
                                <th rowspan="2" class="sticky-right col-ket px-1 py-2 text-center font-bold text-slate-600 bg-slate-100">Ket</th>
                            </tr>
                            <tr>
                                @foreach($pertemuanList as $pertemuan)
                                    <th class="px-0.5 py-1 text-center min-w-[32px] bg-indigo-50">
                                        <div class="font-bold text-indigo-600 text-[10px]">{{ $pertemuan->pertemuan_ke }}</div>
                                        <div class="text-[7px] text-slate-400">{{ $pertemuan->tanggal->format('d/m') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswaList as $index => $siswa)
                                @php
                                    $tidakHadir = 0;
                                    foreach($pertemuanList as $p) {
                                        $abs = $absensiMatrix[$siswa->id][$p->id] ?? null;
                                        if ($abs && $abs->status->value !== 'Hadir') { $tidakHadir++; }
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="sticky-left col-no px-1 py-1 text-center text-slate-500 bg-white text-[10px]">{{ $index + 1 }}</td>
                                    <td class="sticky-left col-nama px-1 py-1 bg-white">
                                        <div class="font-medium text-slate-800 truncate max-w-[130px] text-[10px]">{{ $siswa->nama_siswa }}</div>
                                    </td>
                                    <td class="sticky-left col-nisn px-1 py-1 text-center text-slate-500 bg-white font-mono text-[9px]">{{ $siswa->nisn }}</td>
                                    
                                    @foreach($pertemuanList as $pertemuan)
                                        @php
                                            $absensi = $absensiMatrix[$siswa->id][$pertemuan->id] ?? null;
                                            $currentStatus = $absensi?->status?->value ?? '';
                                            $statusClass = strtolower($currentStatus);
                                        @endphp
                                        <td class="px-0.5 py-1 text-center">
                                            @if($pertemuan->status === 'kosong')
                                                <span class="text-slate-300">-</span>
                                            @else
                                                <select class="status-select {{ $statusClass }} rounded border-0 cursor-pointer"
                                                        data-siswa-id="{{ $siswa->id }}"
                                                        data-pertemuan-id="{{ $pertemuan->id }}"
                                                        onchange="updateAbsensiGrid(this)">
                                                    <option value="">-</option>
                                                    <option value="Hadir" {{ $currentStatus === 'Hadir' ? 'selected' : '' }}>H</option>
                                                    <option value="Sakit" {{ $currentStatus === 'Sakit' ? 'selected' : '' }}>S</option>
                                                    <option value="Izin" {{ $currentStatus === 'Izin' ? 'selected' : '' }}>I</option>
                                                    <option value="Alfa" {{ $currentStatus === 'Alfa' ? 'selected' : '' }}>A</option>
                                                </select>
                                            @endif
                                        </td>
                                    @endforeach
                                    
                                    <td class="sticky-right col-tidakhadir px-1 py-1 text-center font-bold text-[10px] {{ $tidakHadir > 0 ? 'text-rose-600 bg-rose-50' : 'text-slate-400 bg-white' }}">
                                        {{ $tidakHadir }}
                                    </td>
                                    <td class="sticky-right col-ket px-1 py-1 text-center bg-white">
                                        <input type="text" class="w-full px-1 py-0.5 text-[9px] border border-slate-200 rounded focus:border-indigo-400 focus:ring-0 outline-none" placeholder="-">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-3 p-3 rounded-lg bg-slate-100 border border-slate-200 flex justify-between items-center text-xs">
                <div class="text-slate-600">
                    <strong>{{ $siswaList->count() }}</strong> siswa • 
                    <strong>{{ $pertemuanList->count() }}</strong> pertemuan
                </div>
                @if($todayPertemuan)
                    <button onclick="setAllTodayGrid('Hadir')" class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 font-bold hover:bg-emerald-200 border-0 cursor-pointer">
                        ✓ Semua Hadir (Hari Ini)
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
const jadwalId = {{ $jadwal->id }};
const todayPertemuanId = {{ $todayPertemuan?->id ?? 'null' }};
const csrfToken = '{{ csrf_token() }}';

// Initial absensi data
const initialAbsensi = @json(collect($absensiMatrix)->map(function($pertemuanData) {
    return collect($pertemuanData)->map(function($item) {
        return $item ? $item->status->value : null;
    });
}));

function absensiPage() {
    return {
        activeTab: 'single',
        selectedPertemuanId: todayPertemuanId ? String(todayPertemuanId) : '',
        absensiData: initialAbsensi,
        
        onPertemuanChange() {
            // Refresh status display when pertemuan changes
        },
        
        getStatus(siswaId) {
            if (!this.selectedPertemuanId) return '';
            const pertemuanData = this.absensiData[siswaId];
            if (!pertemuanData) return '';
            return pertemuanData[this.selectedPertemuanId] || '';
        },
        
        setStatus(siswaId, status) {
            if (!this.selectedPertemuanId) return;
            
            // Update local state
            if (!this.absensiData[siswaId]) this.absensiData[siswaId] = {};
            this.absensiData[siswaId][this.selectedPertemuanId] = status;
            
            // Send to server
            this.saveAbsensi(siswaId, this.selectedPertemuanId, status);
        },
        
        setAllStatus(status) {
            if (!this.selectedPertemuanId) return;
            
            @foreach($siswaList as $siswa)
                this.setStatus({{ $siswa->id }}, status);
            @endforeach
        },
        
        saveAbsensi(siswaId, pertemuanId, status) {
            fetch('{{ route("absensi.updateSingle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    siswa_id: siswaId,
                    pertemuan_id: pertemuanId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    console.error('Save failed:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    };
}

// Grid view functions
function updateAbsensiGrid(selectElement) {
    const siswaId = selectElement.dataset.siswaId;
    const pertemuanId = selectElement.dataset.pertemuanId;
    const status = selectElement.value;
    
    selectElement.className = 'status-select rounded border-0 cursor-pointer ' + status.toLowerCase();
    
    fetch('{{ route("absensi.updateSingle") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ siswa_id: siswaId, pertemuan_id: pertemuanId, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) alert('Gagal menyimpan: ' + data.message);
    })
    .catch(error => alert('Terjadi kesalahan saat menyimpan'));
}

function setAllTodayGrid(status) {
    if (!todayPertemuanId) { alert('Tidak ada pertemuan hari ini.'); return; }
    
    document.querySelectorAll(`select[data-pertemuan-id="${todayPertemuanId}"]`).forEach(select => {
        select.value = status;
        select.className = 'status-select rounded border-0 cursor-pointer ' + status.toLowerCase();
    });
    
    fetch('{{ route("absensi.batchUpdate") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ pertemuan_id: todayPertemuanId, status: status })
    });
}
</script>
@endsection
