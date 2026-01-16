@extends('layouts.app')

@section('title', 'Arsip Konsentrasi')

@section('page-header')
    <x-page-header 
        title="Arsip Konsentrasi" 
        subtitle="Konsentrasi keahlian yang telah diarsipkan"
        :total="$konsentrasiList->count()" 
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('konsentrasi.index') }}" class="btn btn-white">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke Konsentrasi</span>
        </a>
    </div>

    {{-- Table --}}
    @if($konsentrasiList->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Konsentrasi</th>
                        <th>Jurusan</th>
                        <th class="w-24 text-center">Kelas</th>
                        <th class="w-40 text-center">Diarsipkan pada</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($konsentrasiList as $konsentrasi)
                        <tr class="bg-slate-50">
                            <td>
                                <div class="font-medium text-slate-600">{{ $konsentrasi->nama_konsentrasi }}</div>
                                @if($konsentrasi->kode_konsentrasi)
                                    <div class="text-xs text-slate-400">{{ $konsentrasi->kode_konsentrasi }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm text-slate-500">{{ $konsentrasi->jurusan->nama_jurusan ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-slate">{{ $konsentrasi->kelas_count }} kelas</span>
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $konsentrasi->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    <form action="{{ route('konsentrasi.restore', $konsentrasi->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan konsentrasi ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    <form action="{{ route('konsentrasi.forceDelete', $konsentrasi->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN konsentrasi ini? Data tidak dapat dikembalikan!')">
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
            description="Tidak ada konsentrasi yang diarsipkan."
        />
    @endif
</div>
@endsection
