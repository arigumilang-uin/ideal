@extends('layouts.app')

@section('title', 'Jam Pelajaran')

@section('page-header')
    <x-page-header 
        title="Jam Pelajaran" 
        subtitle="Kelola slot waktu jam pelajaran sekolah."
        :total="$jamPelajaran->count()"
    />
@endsection

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Fix flatpickr overlapping */
    .flatpickr-calendar { z-index: 9999 !important; }
</style>

<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Form Tambah --}}
        <div>
            <div class="card sticky top-6">
                <div class="card-header">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <x-ui.icon name="plus" size="16" />
                        </div>
                        <h3 class="card-title">Tambah Slot Waktu</h3>
                    </div>
                </div>
                <div class="card-body border-t border-gray-100">
                    <form action="{{ route('admin.jam-pelajaran.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Label Urutan <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    name="label" 
                                    value="{{ old('label') }}" 
                                    required 
                                    placeholder="Contoh: Jam Ke-1" 
                                    class="form-input block w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" 
                                />
                                @error('label')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                                    <input type="text" name="jam_mulai" value="{{ old('jam_mulai', '07:00') }}" required class="time-picker form-input block w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm bg-white" />
                                </div>
                                <div class="form-group">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                                    <input type="text" name="jam_selesai" value="{{ old('jam_selesai', '07:45') }}" required class="time-picker form-input block w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm bg-white" />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-full justify-center">
                                <x-ui.icon name="save" size="16" />
                                <span>Simpan Slot Waktu</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="bg-slate-50 p-4 border-t border-slate-100 rounded-b-xl">
                    <p class="text-xs text-slate-500 flex items-start gap-2">
                        <x-ui.icon name="info" size="14" class="text-blue-500 mt-0.5 shrink-0" />
                        <span>Jam pelajaran ini akan muncul sebagai baris referensi saat mengisi jadwal pelajaran (matrix).</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Daftar Jam --}}
        <div>
            <div class="card flex flex-col h-full max-h-[calc(100vh-12rem)]">
                <div class="card-header justify-between">
                    <h3 class="card-title">Daftar Slot Waktu</h3>
                    <span class="badge badge-secondary">{{ $jamPelajaran->count() }} Slot</span>
                </div>
                <div class="flex-1 overflow-y-auto divide-y divide-gray-100 border-t border-gray-100">
                    @forelse($jamPelajaran as $jam)
                        <div class="p-4 hover:bg-slate-50 transition-colors {{ !$jam->is_active ? 'bg-slate-50/50' : '' }}" x-data="{ editing: false }">
                            {{-- View Mode --}}
                            <div x-show="!editing" class="flex items-center justify-between group">
                                <div class="flex items-center gap-4">
                                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center border border-slate-200">
                                        #{{ $jam->urutan }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                            {{ $jam->label }}
                                            @if(!$jam->is_active)
                                                <span class="badge badge-secondary text-[10px] py-0.5 px-1.5">NONAKTIF</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                                            {{ $jam->jam_mulai->format('H:i') }} - {{ $jam->jam_selesai->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editing = true" class="btn btn-sm btn-icon btn-white text-amber-600 hover:bg-amber-50">
                                        <x-ui.icon name="edit" size="14" />
                                    </button>
                                    <form action="{{ route('admin.jam-pelajaran.destroy', $jam->id) }}" method="POST" onsubmit="return confirm('Hapus slot ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-rose-600 hover:bg-rose-50">
                                            <x-ui.icon name="trash" size="14" />
                                        </button>
                                    </form>
                                    <div class="flex flex-col gap-0.5 ml-1">
                                         @if(!$loop->first)
                                            <form action="{{ route('admin.jam-pelajaran.reorder', ['id' => $jam->id, 'direction' => 'up']) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button class="w-5 h-4 flex items-center justify-center hover:bg-slate-200 rounded text-slate-400 hover:text-slate-600 cursor-pointer" title="Geser Atas">
                                                    <x-ui.icon name="chevron-up" size="12" />
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$loop->last)
                                            <form action="{{ route('admin.jam-pelajaran.reorder', ['id' => $jam->id, 'direction' => 'down']) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button class="w-5 h-4 flex items-center justify-center hover:bg-slate-200 rounded text-slate-400 hover:text-slate-600 cursor-pointer" title="Geser Bawah">
                                                    <x-ui.icon name="chevron-down" size="12" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Edit Mode --}}
                            <div x-show="editing" x-cloak class="bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-inner">
                                <form action="{{ route('admin.jam-pelajaran.update', $jam->id) }}" method="POST" class="space-y-3">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-1 gap-3">
                                        <input type="text" name="label" value="{{ $jam->label }}" 
                                               class="form-input block w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Label">
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="jam_mulai" value="{{ $jam->jam_mulai->format('H:i') }}" 
                                                   class="time-picker form-input block w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white">
                                            <input type="text" name="jam_selesai" value="{{ $jam->jam_selesai->format('H:i') }}" 
                                                   class="time-picker form-input block w-full rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 text-sm bg-white">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="is_active" value="1" id="active-{{ $jam->id }}" {{ $jam->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <label for="active-{{ $jam->id }}" class="text-sm text-slate-600">Aktif</label>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2 pt-2">
                                        <button type="button" @click="editing = false" class="btn btn-sm btn-secondary">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">
                             <x-ui.empty-state title="Belum ada slot" description="Tambahkan slot waktu di sebelah kiri." icon="clock" />
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".time-picker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true
        });
    });
</script>
@endsection
