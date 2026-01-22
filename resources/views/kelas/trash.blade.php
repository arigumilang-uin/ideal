@extends('layouts.app')

@section('title', 'Arsip Kelas')

@section('page-header')
    <x-page-header 
        title="Arsip Kelas" 
        subtitle="Kelas yang telah diarsipkan"
        :total="$kelasList->count()" 
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('kelas.index') }}" class="btn btn-white">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke Kelas</span>
        </a>
    </div>

    {{-- Table --}}
    @if($kelasList->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Jurusan</th>
                        <th class="w-24 text-center">Siswa</th>
                        <th class="w-40 text-center">Diarsipkan pada</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kelasList as $kelas)
                        <tr class="bg-slate-50">
                            <td>
                                <div class="font-medium text-slate-600">{{ $kelas->nama_kelas }}</div>
                                @if($kelas->waliKelas)
                                    <div class="text-xs text-slate-400">Wali: {{ $kelas->waliKelas->nama }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm text-slate-500">{{ $kelas->jurusan->nama_jurusan ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-slate">{{ $kelas->siswa_count }} siswa</span>
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $kelas->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    <form action="{{ route('kelas.restore', $kelas->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan kelas ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    <form action="{{ route('kelas.forceDelete', $kelas->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN kelas ini? Data tidak dapat dikembalikan!')">
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
            description="Tidak ada kelas yang diarsipkan."
        />
    @endif
</div>
@endsection
