@extends('layouts.app')

@section('title', 'Arsip Jurusan')

@section('page-header')
    <x-page-header 
        title="Arsip Jurusan" 
        subtitle="Jurusan yang telah diarsipkan"
        :total="$jurusanList->count()" 
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('jurusan.index') }}" class="btn btn-white">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke Jurusan</span>
        </a>
    </div>

    {{-- Table --}}
    @if($jurusanList->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Jurusan</th>
                        <th class="w-24 text-center">Kelas</th>
                        <th class="w-40 text-center">Diarsipkan pada</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jurusanList as $jurusan)
                        <tr class="bg-slate-50">
                            <td>
                                <div class="font-medium text-slate-600">{{ $jurusan->nama_jurusan }}</div>
                                <div class="text-xs text-slate-400">{{ $jurusan->kode_jurusan }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-slate">{{ $jurusan->kelas_count }} kelas</span>
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $jurusan->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    <form action="{{ route('jurusan.restore', $jurusan->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan jurusan ini beserta kelas dan konsentrasi?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    <form action="{{ route('jurusan.forceDelete', $jurusan->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN jurusan ini? Data tidak dapat dikembalikan!')">
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
            description="Tidak ada jurusan yang diarsipkan."
        />
    @endif
</div>
@endsection
