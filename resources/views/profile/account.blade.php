@extends('layouts.app')

@section('title', 'Profil Saya')

@section('page-header')
    <x-page-header 
        title="Profil Saya" 
        subtitle="Kelola informasi akun dan keamanan Anda."
    />
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
    
    {{-- LEFT COLUMN: Profile Summary Card --}}
    <div class="lg:col-span-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header with gradient background --}}
            <div class="h-24 bg-gradient-to-br from-indigo-500 via-indigo-600 to-purple-600 relative">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\"30\" height=\"30\" viewBox=\"0 0 30 30\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z\" fill=\"rgba(255,255,255,0.07)\"%3E%3C/path%3E%3C/svg%3E')] opacity-50"></div>
            </div>
            
            {{-- Avatar --}}
            <div class="relative -mt-12 px-6">
                <div class="w-24 h-24 mx-auto rounded-2xl bg-gradient-to-br from-white to-slate-50 flex items-center justify-center text-3xl font-bold shadow-lg border-4 border-white ring-4 ring-indigo-100">
                    <span class="bg-gradient-to-br from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                        {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
                    </span>
                </div>
            </div>
            
            {{-- User Info --}}
            <div class="px-6 pt-4 pb-6 text-center">
                <h3 class="text-xl font-bold text-slate-800">{{ Auth::user()->username }}</h3>
                <p class="text-slate-500 text-sm mt-1">{{ Auth::user()->email ?? 'Email belum diatur' }}</p>
                
                {{-- Role Badge --}}
                <div class="mt-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 border border-indigo-200">
                        <x-ui.icon name="shield-check" size="14" />
                        {{ Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role ?? 'User' }}
                    </span>
                </div>
            </div>
            
            {{-- Quick Info --}}
            <div class="px-6 pb-6 space-y-3">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <x-ui.icon name="calendar" size="16" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Bergabung</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ Auth::user()->created_at?->format('d M Y') ?? '-' }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <x-ui.icon name="clock" size="16" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Login Terakhir</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ Auth::user()->last_login_at?->diffForHumans() ?? 'Baru saja' }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <x-ui.icon name="hash" size="16" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Hari Bergabung</p>
                        <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ Auth::user()->created_at?->diffInDays(now()) ?? 0 }} hari</p>
                    </div>
                </div>
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
            
            <form action="{{ route('account.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                
                <x-forms.grid :cols="2">
                    {{-- Username --}}
                    @if(Auth::user()->hasRole('Wali Murid'))
                        <x-forms.input 
                            name="username" 
                            label="Nama Lengkap" 
                            :value="Auth::user()->username"
                            placeholder="Nama lengkap Anda"
                            help="Nama yang akan ditampilkan di sistem."
                        />
                    @else
                        <x-forms.input 
                            name="username_display" 
                            label="Nama Lengkap" 
                            :value="Auth::user()->username"
                            readonly
                            disabled
                            help="Nama Lengkap hanya dapat diubah oleh Operator Sekolah."
                            class="bg-slate-50"
                        />
                    @endif
                    
                    {{-- Email --}}
                    <x-forms.input 
                        name="email" 
                        type="email"
                        label="Email" 
                        :value="Auth::user()->email"
                        placeholder="contoh@email.com"
                        icon="mail"
                    />
                </x-forms.grid>
                
                {{-- NIP/NI PPPK/NUPTK (for teachers) --}}
                @if(Auth::user()->hasAnyRole(['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah']))
                <div class="pt-4 mt-4 border-t border-slate-100">
                    <h4 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
                        <x-ui.icon name="id-card" size="16" class="text-slate-400" />
                        Identitas Kepegawaian
                    </h4>
                    <x-forms.grid :cols="3">
                        <x-forms.input 
                            name="nip" 
                            label="NIP" 
                            :value="Auth::user()->nip"
                            placeholder="Nomor Induk Pegawai"
                            help="Untuk PNS"
                        />
                        
                        <x-forms.input 
                            name="ni_pppk" 
                            label="NI PPPK" 
                            :value="Auth::user()->ni_pppk"
                            placeholder="Nomor Induk PPPK"
                            help="Untuk PPPK"
                        />
                        
                        <x-forms.input 
                            name="nuptk" 
                            label="NUPTK" 
                            :value="Auth::user()->nuptk"
                            placeholder="Nomor Unik Pendidik"
                            help="Untuk Non-ASN"
                        />
                    </x-forms.grid>
                </div>
                @endif
                
                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 shadow-sm hover:shadow transition-all active:scale-95">
                        <x-ui.icon name="save" size="16" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </x-forms.section>
        
        {{-- SECTION 2: Change Password --}}
        <x-forms.section 
            title="Ubah Password" 
            variant="card"
            icon="lock"
        >
            <x-slot name="description">Pastikan akun Anda menggunakan password yang kuat.</x-slot>
            
            <form action="{{ route('account.password.update') }}" method="POST" class="space-y-5">
                @csrf
                
                {{-- Current Password --}}
                <x-forms.password
                    name="current_password"
                    label="Password Saat Ini"
                    required
                    placeholder="Masukkan password saat ini"
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
                        label="Konfirmasi Password"
                        required
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                    />
                </x-forms.grid>
                
                {{-- Password Requirements Info --}}
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
                
                <div class="flex justify-end pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 shadow-sm hover:shadow transition-all active:scale-95">
                        <x-ui.icon name="shield-check" size="16" />
                        Ubah Password
                    </button>
                </div>
            </form>
        </x-forms.section>
        
    </div>
</div>
@endsection
