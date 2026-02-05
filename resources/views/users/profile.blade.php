@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('page-header')
    <x-page-header 
        title="Profil Pengguna" 
        subtitle="Kelola data diri dan keamanan akun Anda."
    />
@endsection

@section('content')
@php
    $userRoleName = $user->role->nama_role ?? 'User';
    $isTeacher = in_array($userRoleName, ['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah']);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start max-w-6xl">
    
    {{-- LEFT COLUMN: Profile Summary Card --}}
    <div class="lg:col-span-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header with dark gradient --}}
            <div class="h-24 bg-gradient-to-br from-slate-800 via-slate-700 to-slate-800 relative">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.05)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50"></div>
                {{-- Online indicator --}}
                <div class="absolute top-4 right-4">
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 backdrop-blur-sm">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Online</span>
                    </div>
                </div>
            </div>
            
            {{-- Avatar --}}
            <div class="relative -mt-12 px-6">
                <div class="w-24 h-24 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center shadow-lg border-4 border-white ring-4 ring-slate-100 overflow-hidden">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=475569&color=fff&size=256&bold=true" 
                         class="w-full h-full object-cover">
                </div>
            </div>
            
            {{-- User Info --}}
            <div class="px-6 pt-4 pb-6 text-center">
                <h3 class="text-xl font-bold text-slate-800">{{ $user->username }}</h3>
                <p class="text-slate-500 text-sm mt-1">{{ $user->email ?? 'Email belum diatur' }}</p>
                
                {{-- Role Badge --}}
                <div class="mt-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        <x-ui.icon name="shield-check" size="14" />
                        {{ $userRoleName }}
                    </span>
                </div>
            </div>
            
            {{-- Quick Info --}}
            <div class="px-6 pb-6 space-y-3">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600">
                        <x-ui.icon name="calendar" size="16" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Bergabung</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $user->created_at?->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600">
                        <x-ui.icon name="clock" size="16" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Login Terakhir</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $user->last_login_at?->diffForHumans() ?? 'Baru saja' }}</p>
                    </div>
                </div>
                
                @if($user->phone)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600">
                        <x-ui.icon name="phone" size="16" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">WhatsApp</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $user->phone }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- RIGHT COLUMN: Forms --}}
    <div class="lg:col-span-8 space-y-6">
        
        {{-- SECTION 1: Profile Information --}}
        <x-forms.section 
            title="Informasi Profil" 
            variant="card"
            icon="user"
        >
            <x-slot name="description">Kelola informasi dasar akun Anda.</x-slot>
            
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                
                {{-- Role Badge (Read-only) --}}
                <div class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="w-10 h-10 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600">
                        <x-ui.icon name="shield-check" size="20" />
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Peran Anda</p>
                        <p class="text-sm font-bold text-slate-700 mt-0.5">{{ $userRoleName }}</p>
                    </div>
                </div>
                
                <x-forms.grid :cols="2">
                    {{-- Username --}}
                    @if($userRoleName == 'Wali Murid')
                        <x-forms.input 
                            name="username" 
                            label="Nama Lengkap" 
                            :value="$user->username"
                            placeholder="Nama lengkap Anda"
                            required
                            help="Nama yang digunakan untuk login dan ditampilkan di sistem."
                        />
                    @else
                        <x-forms.input 
                            name="username_display" 
                            label="Nama Lengkap" 
                            :value="$user->username"
                            readonly
                            disabled
                            help="Nama Lengkap hanya dapat diubah oleh Operator Sekolah."
                        />
                    @endif
                    
                    {{-- Email --}}
                    <x-forms.input 
                        name="email" 
                        type="email"
                        label="Alamat Email" 
                        :value="$user->email"
                        placeholder="contoh@email.com"
                        icon="mail"
                        required
                    />
                </x-forms.grid>
                
                <x-forms.grid :cols="2">
                    {{-- Phone --}}
                    <x-forms.input 
                        name="phone" 
                        label="Nomor WhatsApp" 
                        :value="$user->phone"
                        placeholder="08..."
                        icon="phone"
                        :readonly="$userRoleName == 'Wali Murid'"
                        :help="$userRoleName == 'Wali Murid' ? 'Nomor telepon disinkronisasi dari data siswa.' : null"
                    />
                    
                    {{-- Empty for alignment --}}
                    <div></div>
                </x-forms.grid>
                
                {{-- NIP/NI PPPK/NUPTK (for teachers) --}}
                @if($isTeacher)
                <div class="pt-5 mt-5 border-t border-slate-100">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                            <x-ui.icon name="id-card" size="16" />
                        </div>
                        <h4 class="text-sm font-bold text-slate-700">Identitas Kepegawaian</h4>
                    </div>
                    <x-forms.grid :cols="3">
                        <x-forms.input 
                            name="nip" 
                            label="NIP" 
                            :value="$user->nip ?? ''"
                            placeholder="Nomor Induk Pegawai"
                            help="Untuk PNS"
                        />
                        
                        <x-forms.input 
                            name="ni_pppk" 
                            label="NI PPPK" 
                            :value="$user->ni_pppk ?? ''"
                            placeholder="Nomor Induk PPPK"
                            help="Untuk PPPK"
                        />
                        
                        <x-forms.input 
                            name="nuptk" 
                            label="NUPTK" 
                            :value="$user->nuptk ?? ''"
                            placeholder="Nomor Unik Pendidik"
                            help="Untuk Non-ASN"
                        />
                    </x-forms.grid>
                </div>
                @endif
                
                {{-- Wali Murid Info --}}
                @if($userRoleName == 'Wali Murid')
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-200 flex items-center justify-center text-slate-600">
                            <x-ui.icon name="info" size="16" />
                        </div>
                        <div>
                            <h5 class="text-sm font-semibold text-slate-700">Informasi Wali Murid</h5>
                            <p class="mt-1 text-xs text-slate-600 leading-relaxed">
                                Data Wali Murid disinkronisasi dari data siswa. Hubungi admin jika ada kesalahan.
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold bg-slate-800 hover:bg-slate-700 shadow-sm hover:shadow transition-all active:scale-95">
                        <x-ui.icon name="save" size="16" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </x-forms.section>
        
        {{-- SECTION 2: Security / Password --}}
        <x-forms.section 
            title="Keamanan Akun" 
            variant="card"
            icon="lock"
        >
            <x-slot name="description">Kelola password dan keamanan akun.</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Change Password --}}
                <a href="{{ route('profile.change-password.form') }}" 
                   class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-slate-200 flex items-center justify-center text-slate-600 group-hover:bg-slate-300 transition-colors">
                        <x-ui.icon name="key" size="20" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-700">Ganti Password</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Ubah password dengan memasukkan password lama</p>
                    </div>
                    <x-ui.icon name="chevron-right" size="18" class="text-slate-400 group-hover:text-slate-600 transition-colors" />
                </a>
                
                {{-- Forgot Password --}}
                <a href="{{ route('password.request') }}" 
                   class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-200 transition-colors">
                        <x-ui.icon name="mail" size="20" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-700">Reset Password via Email</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Kirim link reset password ke email Anda</p>
                    </div>
                    <x-ui.icon name="chevron-right" size="18" class="text-slate-400 group-hover:text-slate-600 transition-colors" />
                </a>
            </div>
        </x-forms.section>
        
    </div>
</div>
@endsection
