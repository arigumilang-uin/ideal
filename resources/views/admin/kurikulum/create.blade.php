@extends('layouts.app')

@section('title', 'Tambah Kurikulum')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Tambah Kurikulum" 
        subtitle="Tambahkan kurikulum baru ke sistem"
    />

    {{-- Form Card --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.kurikulum.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Kode --}}
                    <div class="form-group">
                        <label for="kode" class="form-label">Kode Kurikulum <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="kode" 
                               id="kode" 
                               value="{{ old('kode') }}"
                               class="form-input @error('kode') border-red-500 @enderror" 
                               placeholder="Contoh: K13, MERDEKA"
                               required>
                        @error('kode')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tahun Berlaku --}}
                    <div class="form-group">
                        <label for="tahun_berlaku" class="form-label">Tahun Berlaku</label>
                        <input type="number" 
                               name="tahun_berlaku" 
                               id="tahun_berlaku" 
                               value="{{ old('tahun_berlaku') }}"
                               class="form-input @error('tahun_berlaku') border-red-500 @enderror" 
                               placeholder="Contoh: 2022"
                               min="2000" max="2100">
                        @error('tahun_berlaku')
                            <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Nama --}}
                <div class="form-group">
                    <label for="nama" class="form-label">Nama Kurikulum <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="nama" 
                           id="nama" 
                           value="{{ old('nama') }}"
                           class="form-input @error('nama') border-red-500 @enderror" 
                           placeholder="Contoh: Kurikulum Merdeka"
                           required>
                    @error('nama')
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
                              placeholder="Deskripsi singkat tentang kurikulum ini (opsional)">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-secondary">
                        <x-ui.icon name="x" size="16" />
                        <span>Batal</span>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="16" />
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
