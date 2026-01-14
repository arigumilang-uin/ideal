@extends('layouts.app')

@section('title', 'Template Jam Pelajaran')

@section('content')
<div class="space-y-6" x-data="templateJamPage()">
    {{-- Page Header --}}
    <x-page-header 
        title="Template Jam Pelajaran" 
        subtitle="Konfigurasi slot waktu per periode semester dan per hari"
        :total="$templateJams->count()" 
    />

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pilih Periode & Hari</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Periode --}}
                <div class="form-group">
                    <label class="form-label">Periode Semester</label>
                    <select name="periode_id" class="form-input" onchange="this.form.submit()">
                        @foreach($allPeriodes as $periode)
                            <option value="{{ $periode->id }}" {{ $selectedPeriode && $selectedPeriode->id == $periode->id ? 'selected' : '' }}>
                                {{ $periode->display_name }} {{ $periode->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hari Tabs --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Hari</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($hariList as $h)
                            <a href="{{ route('admin.template-jam.index', ['periode_id' => $selectedPeriode?->id, 'hari' => $h]) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $selectedHari == $h ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $h }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedPeriode)
        {{-- Add Slot Form --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tambah Slot Waktu - {{ $selectedHari }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.template-jam.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                    @csrf
                    <input type="hidden" name="periode_semester_id" value="{{ $selectedPeriode->id }}">
                    <input type="hidden" name="hari" value="{{ $selectedHari }}">

                    <div class="form-group">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-input" placeholder="Jam Ke-1" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mulai</label>
                        <input type="time" name="jam_mulai" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Selesai</label>
                        <input type="time" name="jam_selesai" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipe</label>
                        <select name="tipe" class="form-input">
                            @foreach($tipeOptions as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group md:col-span-2">
                        <button type="submit" class="btn btn-primary w-full">
                            <x-ui.icon name="plus" size="16" />
                            <span>Tambah Slot</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Slots Table --}}
        @if($templateJams->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">No</th>
                            <th>Label</th>
                            <th class="w-24">Mulai</th>
                            <th class="w-24">Selesai</th>
                            <th class="w-28 text-center">Tipe</th>
                            <th class="w-24 text-center">Status</th>
                            <th class="w-32 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templateJams as $slot)
                            <tr x-data="{ editing: false }">
                                {{-- View Mode --}}
                                <template x-if="!editing">
                                    <td class="text-center font-medium text-slate-500">{{ $slot->urutan }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td class="font-medium">{{ $slot->label }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td>{{ $slot->jam_mulai instanceof \DateTime ? $slot->jam_mulai->format('H:i') : $slot->jam_mulai }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td>{{ $slot->jam_selesai instanceof \DateTime ? $slot->jam_selesai->format('H:i') : $slot->jam_selesai }}</td>
                                </template>
                                <template x-if="!editing">
                                    <td class="text-center">
                                        @php
                                            $tipeBadge = match($slot->tipe) {
                                                'pelajaran' => 'badge-success',
                                                'istirahat' => 'badge-amber',
                                                'ishoma' => 'badge-orange',
                                                'upacara' => 'badge-blue',
                                                default => 'badge-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $tipeBadge }}">{{ ucfirst($slot->tipe) }}</span>
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td class="text-center">
                                        @if($slot->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </template>
                                <template x-if="!editing">
                                    <td>
                                        <div class="flex items-center justify-center gap-1">
                                            {{-- Reorder Up --}}
                                            @if(!$loop->first)
                                                <form action="{{ route('admin.template-jam.reorder', $slot->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="btn btn-sm btn-icon btn-white" title="Naik">
                                                        <x-ui.icon name="chevron-up" size="14" />
                                                    </button>
                                                </form>
                                            @endif
                                            {{-- Reorder Down --}}
                                            @if(!$loop->last)
                                                <form action="{{ route('admin.template-jam.reorder', $slot->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="btn btn-sm btn-icon btn-white" title="Turun">
                                                        <x-ui.icon name="chevron-down" size="14" />
                                                    </button>
                                                </form>
                                            @endif
                                            {{-- Edit --}}
                                            <button @click="editing = true" class="btn btn-sm btn-icon btn-white" title="Edit">
                                                <x-ui.icon name="edit" size="14" />
                                            </button>
                                            {{-- Delete --}}
                                            <form action="{{ route('admin.template-jam.destroy', $slot->id) }}" method="POST" onsubmit="return confirm('Hapus slot ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-white text-red-600 hover:text-red-700" title="Hapus">
                                                    <x-ui.icon name="trash" size="14" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </template>

                                {{-- Edit Mode --}}
                                <template x-if="editing">
                                    <td colspan="7" class="p-0">
                                        <form action="{{ route('admin.template-jam.update', $slot->id) }}" method="POST" class="grid grid-cols-7 gap-2 p-3 bg-slate-50">
                                            @csrf @method('PUT')
                                            <div class="text-center font-medium text-slate-500 flex items-center justify-center">{{ $slot->urutan }}</div>
                                            <input type="text" name="label" value="{{ $slot->label }}" class="form-input text-sm" required>
                                            <input type="time" name="jam_mulai" value="{{ $slot->jam_mulai instanceof \DateTime ? $slot->jam_mulai->format('H:i') : $slot->jam_mulai }}" class="form-input text-sm" required>
                                            <input type="time" name="jam_selesai" value="{{ $slot->jam_selesai instanceof \DateTime ? $slot->jam_selesai->format('H:i') : $slot->jam_selesai }}" class="form-input text-sm" required>
                                            <select name="tipe" class="form-input text-sm">
                                                @foreach($tipeOptions as $val => $label)
                                                    <option value="{{ $val }}" {{ $slot->tipe == $val ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" name="is_active" value="1" {{ $slot->is_active ? 'checked' : '' }} class="form-checkbox">
                                                <span class="text-sm">Aktif</span>
                                            </label>
                                            <div class="flex gap-1">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <x-ui.icon name="check" size="14" />
                                                </button>
                                                <button type="button" @click="editing = false" class="btn btn-sm btn-secondary">
                                                    <x-ui.icon name="x" size="14" />
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </template>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-ui.empty-state
                icon="clock"
                title="Belum Ada Slot Waktu"
                description="Tambahkan slot waktu untuk hari {{ $selectedHari }} menggunakan form di atas."
            />
        @endif

        {{-- Copy From Other Period --}}
        @if($allPeriodes->count() > 1 && $templateJams->count() == 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Salin dari Periode Lain</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.template-jam.copy') }}" method="POST" class="flex items-end gap-4">
                        @csrf
                        <input type="hidden" name="to_periode_id" value="{{ $selectedPeriode->id }}">
                        <div class="form-group flex-1">
                            <label class="form-label">Periode Sumber</label>
                            <select name="from_periode_id" class="form-input" required>
                                <option value="">Pilih periode...</option>
                                @foreach($allPeriodes as $p)
                                    @if($p->id != $selectedPeriode->id)
                                        <option value="{{ $p->id }}">{{ $p->display_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary">
                            <x-ui.icon name="copy" size="16" />
                            <span>Salin Template</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @else
        <x-ui.empty-state
            icon="calendar"
            title="Belum Ada Periode Semester"
            description="Buat periode semester terlebih dahulu."
            :actionUrl="route('admin.periode-semester.create')"
            actionLabel="Buat Periode"
        />
    @endif
</div>

@push('scripts')
<script>
function templateJamPage() {
    return {
        // Add any Alpine.js reactive data here if needed
    }
}
</script>
@endpush
@endsection
