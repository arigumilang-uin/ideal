@extends('layouts.app')

@section('title', 'Arsip Periode Semester')

@section('page-header')
    <x-page-header 
        title="Arsip Periode Semester" 
        subtitle="Periode semester yang telah diarsipkan"
        :total="$periodes->count()" 
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.periode-semester.index') }}" class="btn btn-white">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke Periode</span>
        </a>
    </div>

    {{-- Table --}}
    @if($periodes->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Periode</th>
                        <th class="text-center w-32">Semester</th>
                        <th class="w-40 text-center">Diarsipkan pada</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periodes as $p)
                        <tr class="bg-slate-50">
                            <td>
                                <div class="font-medium text-slate-600">{{ $p->nama_periode }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">T.A. {{ $p->tahun_ajaran }}</div>
                            </td>
                            <td class="text-center">
                                @if($p->semester->value === 'Ganjil')
                                    <span class="badge badge-slate">Ganjil</span>
                                @else
                                    <span class="badge badge-slate">Genap</span>
                                @endif
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $p->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Restore Button --}}
                                    <form action="{{ route('admin.periode-semester.restore', $p->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan periode ini beserta template jam dan jadwal?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    {{-- Force Delete Button --}}
                                    <form action="{{ route('admin.periode-semester.forceDelete', $p->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN periode ini beserta semua data akademik? Data tidak dapat dikembalikan!')">
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
            description="Tidak ada periode semester yang diarsipkan."
        />
    @endif
</div>
@endsection
