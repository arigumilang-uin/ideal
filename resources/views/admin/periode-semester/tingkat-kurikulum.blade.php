@extends('layouts.app')

@section('title', 'Konfigurasi Kurikulum - ' . $periode->nama_periode)

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Konfigurasi Kurikulum" 
        subtitle="Tetapkan kurikulum untuk setiap tingkat pada periode {{ $periode->nama_periode }}"
    />

    {{-- Info Card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <x-ui.icon name="info" class="text-blue-500 mt-0.5" size="20" />
            <div class="text-sm text-blue-700">
                <p class="font-medium">Tentang Tingkat Kurikulum</p>
                <p class="mt-1">Setiap tingkat (X, XI, XII) dapat menggunakan kurikulum yang berbeda. Pengaturan ini menentukan mata pelajaran apa yang tersedia untuk kelas pada tingkat tersebut dalam periode ini.</p>
            </div>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <x-ui.icon name="layers" size="18" class="text-slate-400" />
                <span>{{ $periode->nama_periode }}</span>
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.periode-semester.saveTingkatKurikulum', $periode->id) }}" method="POST" class="space-y-6">
                @csrf

                {{-- Tingkat X --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach(['X', 'XI', 'XII'] as $tingkat)
                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg">
                                    {{ $tingkat }}
                                </span>
                                <div>
                                    <div class="font-semibold text-slate-800">Tingkat {{ $tingkat }}</div>
                                    <div class="text-xs text-slate-500">
                                        @if($tingkat === 'X')
                                            Kelas X (Kelas 10)
                                        @elseif($tingkat === 'XI')
                                            Kelas XI (Kelas 11)
                                        @else
                                            Kelas XII (Kelas 12)
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <select name="tingkat[{{ $tingkat }}]" 
                                    class="form-input w-full @error('tingkat.' . $tingkat) border-red-500 @enderror">
                                <option value="">-- Pilih Kurikulum --</option>
                                @foreach($kurikulums as $kur)
                                    <option value="{{ $kur->id }}" {{ ($tingkatConfig[$tingkat] ?? null) == $kur->id ? 'selected' : '' }}>
                                        {{ $kur->kode }} - {{ $kur->nama }}
                                    </option>
                                @endforeach
                            </select>

                            @error('tingkat.' . $tingkat)
                                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                            @enderror

                            @if($tingkatConfig[$tingkat] ?? null)
                                <div class="mt-2 text-xs text-emerald-600 flex items-center gap-1">
                                    <x-ui.icon name="check-circle" size="12" />
                                    <span>Terkonfigurasi</span>
                                </div>
                            @else
                                <div class="mt-2 text-xs text-amber-600 flex items-center gap-1">
                                    <x-ui.icon name="alert-circle" size="12" />
                                    <span>Belum dikonfigurasi</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.periode-semester.index') }}" class="btn btn-secondary">
                        <x-ui.icon name="arrow-left" size="16" />
                        <span>Kembali</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="16" />
                        <span>Simpan Konfigurasi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quick Info --}}
    @if($kurikulums->count() == 0)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <x-ui.icon name="alert-triangle" class="text-amber-500 mt-0.5" size="20" />
                <div class="text-sm text-amber-700">
                    <p class="font-medium">Belum Ada Kurikulum</p>
                    <p class="mt-1">Anda perlu <a href="{{ route('admin.kurikulum.create') }}" class="underline font-medium">menambahkan kurikulum</a> terlebih dahulu sebelum bisa mengkonfigurasi tingkat.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
