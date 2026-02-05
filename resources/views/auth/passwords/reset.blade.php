<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - IDEAL</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3">
                <picture>
                    <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/logo_smk.png') }}" alt="Logo" class="w-12 h-12 object-contain">
                </picture>
                <div class="text-left">
                    <div class="text-white text-lg font-semibold">IDEAL</div>
                    <div class="text-slate-400 text-xs">SMK Negeri 1 Lubuk Dalam</div>
                </div>
            </a>
        </div>
        
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="px-8 pt-8 pb-6 text-center border-b border-slate-100">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-slate-800">Reset Password</h1>
                <p class="text-slate-500 text-sm mt-2">Buat password baru untuk akun Anda.</p>
            </div>
            
            {{-- Form --}}
            <div class="p-8">
                {{-- Error Messages --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Terjadi Kesalahan</p>
                                <ul class="mt-1 text-xs text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ $email ?? old('email') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-slate-400 focus:ring-2 focus:ring-slate-200 transition-all text-slate-800 placeholder-slate-400 bg-slate-50"
                            placeholder="Masukkan email terdaftar"
                            required
                            readonly
                        >
                    </div>
                    
                    {{-- New Password --}}
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
                        <div class="relative">
                            <input 
                                :type="show ? 'text' : 'password'" 
                                id="password" 
                                name="password" 
                                class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 focus:border-slate-400 focus:ring-2 focus:ring-slate-200 transition-all text-slate-800 placeholder-slate-400"
                                placeholder="Minimal 8 karakter"
                                required
                                autofocus
                            >
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Confirm Password --}}
                    <div x-data="{ show: false }">
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <input 
                                :type="show ? 'text' : 'password'" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 focus:border-slate-400 focus:ring-2 focus:ring-slate-200 transition-all text-slate-800 placeholder-slate-400"
                                placeholder="Ulangi password baru"
                                required
                            >
                            <button type="button" @click="show = !show" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Submit --}}
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition-all active:scale-[0.98] shadow-lg shadow-slate-900/20">
                        Reset Password
                    </button>
                </form>
                
                {{-- Back to Login --}}
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
                        </svg>
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Footer --}}
        <div class="mt-8 text-center text-slate-500 text-xs">
            &copy; {{ date('Y') }} IDEAL - SMK Negeri 1 Lubuk Dalam
        </div>
    </div>
    
</body>
</html>
