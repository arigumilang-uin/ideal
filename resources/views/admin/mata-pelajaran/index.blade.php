@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Mata Pelajaran" 
        subtitle="Kelola daftar mata pelajaran per kurikulum"
        :total="$mataPelajaran->count()"
    />

    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('admin.mata-pelajaran.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Mapel</span>
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request('search') || request('kurikulum_id') ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" class="text-gray-400" size="18" />
                <span class="card-title">Filter Data</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
            </div>
        </div>
        
        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body border-t border-gray-100">
                <form action="{{ route('admin.mata-pelajaran.index') }}" method="GET">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        {{-- Kurikulum Filter --}}
                        <div class="form-group">
                            <label class="form-label">Kurikulum</label>
                            <select name="kurikulum_id" class="form-input">
                                <option value="">Semua Kurikulum</option>
                                @foreach($kurikulums as $kur)
                                    <option value="{{ $kur->id }}" {{ $kurikulumId == $kur->id ? 'selected' : '' }}>
                                        {{ $kur->kode }} - {{ $kur->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Search --}}
                        <div class="form-group md:col-span-2">
                            <label class="form-label">Cari</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <x-ui.icon name="search" size="16" />
                                </span>
                                <input 
                                    type="text" 
                                    name="search" 
                                    value="{{ $search }}" 
                                    class="form-input block w-full pl-10" 
                                    placeholder="Nama atau Kode Mapel..."
                                >
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="flex items-end gap-2">
                            @if(request('search') || request('kurikulum_id'))
                                <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary">
                                    <x-ui.icon name="refresh-cw" size="14" />
                                    <span>Reset</span>
                                </a>
                            @endif
                            <button type="submit" class="btn btn-primary">
                                <x-ui.icon name="search" size="14" />
                                <span>Cari</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if($mataPelajaran->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-28">Kurikulum</th>
                        <th class="w-24">Kode</th>
                        <th>Nama Mata Pelajaran</th>
                        <th class="w-24 text-center">Kelompok</th>
                        <th class="w-24 text-center">Status</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mataPelajaran as $mp)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td>
                                @if($mp->kurikulum)
                                    <span class="badge badge-indigo">{{ $mp->kurikulum->kode }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="font-mono text-sm font-medium text-slate-600">{{ $mp->kode_mapel ?? '-' }}</td>
                            <td>
                                <div class="font-medium text-slate-800">{{ $mp->nama_mapel }}</div>
                                @if($mp->deskripsi)
                                    <div class="text-sm text-slate-500 truncate max-w-xs">{{ $mp->deskripsi }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($mp->kelompok)
                                    <span class="badge badge-slate">{{ $mp->kelompok_label }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($mp->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.mata-pelajaran.edit', $mp->id) }}" 
                                       class="btn btn-sm btn-icon btn-white" title="Edit">
                                        <x-ui.icon name="edit" size="14" />
                                    </a>
                                    <form action="{{ route('admin.mata-pelajaran.destroy', $mp->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-red-600 hover:text-red-700" title="Hapus">
                                            <x-ui.icon name="trash" size="14" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-ui.empty-state
            icon="book-open"
            title="Belum Ada Mata Pelajaran"
            description="Tambahkan mata pelajaran untuk memulai."
            :actionUrl="route('admin.mata-pelajaran.create')"
            actionLabel="Tambah Mapel"
        />
    @endif
</div>
@endsection
