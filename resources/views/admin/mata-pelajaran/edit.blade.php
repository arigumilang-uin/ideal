@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Edit Mata Pelajaran" 
        subtitle="Perbarui data mata pelajaran: {{ $mataPelajaran->nama_mapel }}"
    />

    {{-- Form Card --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.mata-pelajaran.update', $mataPelajaran->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Kurikulum --}}
                <div class="form-group">
                    <label for="kurikulum_id" class="form-label">Kurikulum <span class="text-red-500">*</span></label>
                    <select name="kurikulum_id" 
                            id="kurikulum_id" 
                            class="form-input @error('kurikulum_id') border-red-500 @enderror" 
                            required>
                        <option value="">Pilih Kurikulum</option>
                        @foreach($kurikulums as $kur)
                            <option value="{{ $kur->id }}" {{ old('kurikulum_id', $mataPelajaran->kurikulum_id) == $kur->id ? 'selected' : '' }}>
                                {{ $kur->kode }} - {{ $kur->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kurikulum_id')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Nama --}}
                    <div class="form-group">
                        <label for="nama_mapel" class="form-label">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nama_mapel" 
                               id="nama_mapel" 
                               value="{{ old('nama_mapel', $mataPelajaran->nama_mapel) }}"
                               class="form-input @error('nama_mapel') border-red-500 @enderror" 
                               placeholder="Contoh: Matematika"
                               required>
                        @error('nama_mapel')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kode --}}
                    <div class="form-group">
                        <label for="kode_mapel" class="form-label">Kode Mapel</label>
                        <input type="text" 
                               name="kode_mapel" 
                               id="kode_mapel" 
                               value="{{ old('kode_mapel', $mataPelajaran->kode_mapel) }}"
                               class="form-input @error('kode_mapel') border-red-500 @enderror" 
                               placeholder="Contoh: MTK">
                        @error('kode_mapel')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Kelompok --}}
                <div class="form-group">
                    <label for="kelompok" class="form-label">Kelompok Mapel</label>
                    <select name="kelompok" id="kelompok" class="form-input @error('kelompok') border-red-500 @enderror">
                        <option value="">Tidak Ditentukan</option>
                        <option value="A" {{ old('kelompok', $mataPelajaran->kelompok) == 'A' ? 'selected' : '' }}>A - Umum</option>
                        <option value="B" {{ old('kelompok', $mataPelajaran->kelompok) == 'B' ? 'selected' : '' }}>B - Kejuruan</option>
                        <option value="C" {{ old('kelompok', $mataPelajaran->kelompok) == 'C' ? 'selected' : '' }}>C - Pilihan/Muatan Lokal</option>
                    </select>
                    @error('kelompok')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="form-group">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" 
                              id="deskripsi" 
                              rows="3"
                              class="form-input @error('deskripsi') border-red-500 @enderror" 
                              placeholder="Deskripsi singkat tentang mata pelajaran ini (opsional)">{{ old('deskripsi', $mataPelajaran->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $mataPelajaran->is_active) ? 'checked' : '' }}
                               class="form-checkbox">
                        <span class="text-sm text-slate-700">Mata Pelajaran Aktif</span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary">
                        <x-ui.icon name="x" size="16" />
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="16" />
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
