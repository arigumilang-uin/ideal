<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - IDEAL</title>
    
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
            <div class="inline-flex items-center gap-3">
                <picture>
                    <source srcset="{{ asset('assets/images/logo_smk.webp') }}" type="image/webp">
                    <img src="{{ asset('assets/images/logo_smk.png') }}" alt="Logo" class="w-12 h-12 object-contain">
                </picture>
                <div class="text-left">
                    <div class="text-white text-lg font-semibold">IDEAL</div>
                    <div class="text-slate-400 text-xs">SMK Negeri 1 Lubuk Dalam</div>
                </div>
            </div>
        </div>
        
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="px-8 pt-8 pb-6 text-center border-b border-slate-100">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-slate-800">Verifikasi Email Anda</h1>
                <p class="text-slate-500 text-sm mt-2">Kami telah mengirim link verifikasi ke email Anda.</p>
            </div>
            
            {{-- Content --}}
            <div class="p-8">
                {{-- Success Message --}}
                @if (session('status') == 'verification-link-sent' || session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-800">Link Terkirim!</p>
                                <p class="text-xs text-emerald-700 mt-1">{{ session('status') ?: 'Link verifikasi baru telah dikirim ke email Anda.' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                {{-- Instructions --}}
                <div class="space-y-4 text-sm text-slate-600">
                    <p>Sebelum melanjutkan, silakan periksa email Anda untuk link verifikasi.</p>
                    <p>Jika Anda tidak menerima email dalam beberapa menit:</p>
                    <ul class="list-disc list-inside text-slate-500 space-y-1 pl-2">
                        <li>Periksa folder <strong>Spam</strong> atau <strong>Junk</strong></li>
                        <li>Pastikan email <strong>{{ auth()->user()->email }}</strong> benar</li>
                        <li>Klik tombol di bawah untuk mengirim ulang</li>
                    </ul>
                </div>
                
                {{-- Resend Form --}}
                <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition-all active:scale-[0.98] shadow-lg shadow-slate-900/20">
                        Kirim Ulang Link Verifikasi
                    </button>
                </form>
                
                {{-- Logout Link --}}
                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-slate-700 transition-colors">
                            Logout dan gunakan akun lain
                        </button>
                    </form>
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
