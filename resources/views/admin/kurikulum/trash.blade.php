@extends('layouts.app')

@section('title', 'Arsip Kurikulum')

@section('page-header')
    <x-page-header 
        title="Arsip Kurikulum" 
        subtitle="Kurikulum yang telah diarsipkan"
        :total="$kurikulums->count()" 
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-white">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke Kurikulum</span>
        </a>
    </div>

    {{-- Table --}}
    @if($kurikulums->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-20">Kode</th>
                        <th>Nama Kurikulum</th>
                        <th class="w-28 text-center">Mapel</th>
                        <th class="w-40 text-center">Dihapus pada</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kurikulums as $kurikulum)
                        <tr class="bg-slate-50">
                            <td>
                                <span class="font-mono text-sm font-medium text-slate-500">{{ $kurikulum->kode }}</span>
                            </td>
                            <td>
                                <div class="font-medium text-slate-600">{{ $kurikulum->nama }}</div>
                                @if($kurikulum->deskripsi)
                                    <div class="text-sm text-slate-400 truncate max-w-md">{{ $kurikulum->deskripsi }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-slate">{{ $kurikulum->mata_pelajaran_count }} mapel</span>
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $kurikulum->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Restore Button --}}
                                    <form action="{{ route('admin.kurikulum.restore', $kurikulum->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan kurikulum ini beserta mata pelajarannya?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    {{-- Force Delete Button --}}
                                    <form action="{{ route('admin.kurikulum.forceDelete', $kurikulum->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN kurikulum ini? Data tidak dapat dikembalikan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-red-600 hover:text-red-700" title="Hapus Permanen">
                                            <x-ui.icon name="trash-2" size="14" />
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
            icon="archive"
            title="Arsip Kosong"
            description="Tidak ada kurikulum yang diarsipkan."
        />
    @endif
</div>
@endsection
