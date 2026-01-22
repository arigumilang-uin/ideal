@extends('layouts.app')

@section('title', 'Arsip User')

@section('page-header')
    <x-page-header 
        title="Arsip User" 
        subtitle="User yang telah diarsipkan"
        :total="$users->count()" 
        icon="archive"
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('users.index') }}" class="btn btn-white">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke User</span>
        </a>
    </div>

    {{-- Table --}}
    @if($users->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th class="w-32 text-center">Peran</th>
                        <th class="w-40 text-center">Diarsipkan pada</th>
                        <th class="w-36 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="bg-slate-50">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center">
                                        <span class="text-slate-500 text-sm font-medium">
                                            {{ strtoupper(substr($user->nama ?? 'U', 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-600">{{ $user->nama }}</div>
                                        <div class="text-xs text-slate-400">
                                            {{ preg_replace('/_deleted_\d+$/', '', $user->username) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-slate">{{ $user->role->nama_role ?? '-' }}</span>
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $user->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Restore Button --}}
                                    <form action="{{ route('users.restore', $user->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan user ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    {{-- Force Delete Button --}}
                                    <form action="{{ route('users.forceDelete', $user->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN user ini? Data tidak dapat dikembalikan!')">
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
            description="Tidak ada user yang diarsipkan."
        />
    @endif
</div>
@endsection
