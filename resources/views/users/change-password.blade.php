@extends('layouts.app')

@section('title', 'Ganti Password')

@section('page-header')
    <x-page-header 
        title="Ganti Password" 
        subtitle="Ubah password akun Anda dengan aman."
        :backUrl="route('profile.edit')"
    />
@endsection

@section('content')
<div class="max-w-2xl">
    <x-forms.section 
        title="Ganti Password" 
        variant="card"
        icon="key"
    >
        <x-slot name="description">Masukkan password lama dan password baru Anda.</x-slot>
        
        <form action="{{ route('profile.change-password') }}" method="POST" class="space-y-5">
            @csrf
            
            {{-- Current Password --}}
            <x-forms.password
                name="old_password"
                label="Password Saat Ini"
                required
                placeholder="Masukkan password lama"
                autocomplete="current-password"
            />
            
            <x-forms.grid :cols="2">
                {{-- New Password --}}
                <x-forms.password
                    name="password"
                    label="Password Baru"
                    required
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                />
                
                {{-- Confirm Password --}}
                <x-forms.password
                    name="password_confirmation"
                    label="Konfirmasi Password Baru"
                    required
                    placeholder="Ulangi password baru"
                    autocomplete="new-password"
                />
            </x-forms.grid>
            
            {{-- Password Tips --}}
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-100">
                <div class="flex gap-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <x-ui.icon name="info" size="16" />
                    </div>
                    <div>
                        <h5 class="text-sm font-semibold text-amber-800">Tips Password Aman</h5>
                        <ul class="mt-2 text-xs text-amber-700 space-y-1">
                            <li class="flex items-center gap-1.5">
                                <x-ui.icon name="check" size="12" />
                                Minimal 8 karakter
                            </li>
                            <li class="flex items-center gap-1.5">
                                <x-ui.icon name="check" size="12" />
                                Kombinasi huruf besar, huruf kecil, dan angka
                            </li>
                            <li class="flex items-center gap-1.5">
                                <x-ui.icon name="check" size="12" />
                                Hindari informasi pribadi yang mudah ditebak
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3 pt-5 border-t border-slate-100">
                <a href="{{ route('password.request') }}" class="text-sm text-slate-500 hover:text-slate-700 hover:underline">
                    Lupa password lama?
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold bg-slate-800 hover:bg-slate-700 shadow-sm hover:shadow transition-all active:scale-95">
                        <x-ui.icon name="shield-check" size="16" />
                        Ganti Password
                    </button>
                </div>
            </div>
        </form>
    </x-forms.section>
</div>
@endsection
