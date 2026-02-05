@extends('layouts.app')

@section('title', 'Panduan Kurikulum & Jadwal')

@section('page-header')
    <x-page-header 
        title="Panduan Kurikulum & Jadwal" 
        subtitle="Dokumentasi lengkap workflow akademik dan konfigurasi sistem."
    />
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-10 pb-20" x-data="{ activeSection: 'overview' }">

    {{-- Sticky Navigation --}}
    <div class="bg-white/80 backdrop-blur-md border-b border-slate-200 sticky top-16 z-20 -mx-4 px-4 py-2 md:rounded-xl md:border md:top-4 md:mx-0 shadow-sm">
        <nav class="flex items-center gap-1 overflow-x-auto no-scrollbar scroll-smooth">
            @foreach(['overview' => 'Overview', 'kurikulum' => 'Kurikulum', 'periode' => 'Periode', 'mapel' => 'Mapel', 'template-jam' => 'Template Jam', 'jadwal' => 'Jadwal'] as $id => $label)
                <a href="#{{ $id }}" 
                   @click.prevent="document.getElementById('{{ $id }}').scrollIntoView({behavior: 'smooth'}); activeSection = '{{ $id }}'"
                   :class="activeSection === '{{ $id }}' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                   class="px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all duration-200">
                   @if($id === 'overview') <x-ui.icon name="home" size="14" class="inline mr-1.5 -mt-0.5" /> @endif
                   {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ================================================================== --}}
    {{-- SECTION: OVERVIEW --}}
    {{-- ================================================================== --}}
    <section id="overview" class="bg-white ring-1 ring-slate-900/5 md:rounded-2xl shadow-sm overflow-hidden scroll-mt-28">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                <div class="p-2 bg-white rounded-lg shadow-sm border border-slate-100 text-indigo-600">
                    <x-ui.icon name="book-open" size="20" />
                </div>
                Alur Kerja Sistem Akademik
            </h2>
        </div>
        <div class="p-8 space-y-8">
            {{-- Workflow Diagram --}}
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-50/50 via-purple-50/50 to-blue-50/50 rounded-2xl -m-4"></div>
                <div class="relative z-10 text-center">
                    <p class="text-sm text-slate-500 font-medium mb-6">Wajib diikuti secara berurutan agar sistem berjalan normal:</p>
                    <div class="flex flex-col md:flex-row items-center justify-center gap-4">
                        @foreach([
                            ['label' => '1. Kurikulum', 'color' => 'bg-indigo-600'],
                            ['label' => '2. Periode', 'color' => 'bg-indigo-500'],
                            ['label' => '3. Mapel', 'color' => 'bg-purple-500'],
                            ['label' => '4. Template Jam', 'color' => 'bg-purple-600'],
                            ['label' => '5. Jadwal', 'color' => 'bg-blue-600']
                        ] as $index => $step)
                            <div class="{{ $step['color'] }} text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/10 transform transition hover:-translate-y-1">
                                {{ $step['label'] }}
                            </div>
                            @if(!$loop->last)
                                <x-ui.icon name="arrow-right" size="20" class="text-slate-300 hidden md:block" />
                                <x-ui.icon name="arrow-down" size="20" class="text-slate-300 md:hidden" />
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Summary Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $cards = [
                        ['id' => 'kurikulum', 'icon' => 'book', 'color' => 'indigo', 'title' => 'Kurikulum', 'desc' => 'Master data acuan akademik'],
                        ['id' => 'periode', 'icon' => 'calendar', 'color' => 'emerald', 'title' => 'Periode Semester', 'desc' => 'Tahun ajaran aktif & tanggal'],
                        ['id' => 'mapel', 'icon' => 'file-text', 'color' => 'amber', 'title' => 'Mata Pelajaran', 'desc' => 'Daftar pelajaran & guru'],
                        ['id' => 'template-jam', 'icon' => 'clock', 'color' => 'purple', 'title' => 'Template Jam', 'desc' => 'Slot waktu per hari'],
                        ['id' => 'jadwal', 'icon' => 'grid', 'color' => 'blue', 'title' => 'Jadwal Mengajar', 'desc' => 'Matriks KBM kelas'],
                    ];
                @endphp
                @foreach($cards as $card)
                    <a href="#{{ $card['id'] }}" 
                       @click.prevent="document.getElementById('{{ $card['id'] }}').scrollIntoView({behavior: 'smooth'}); activeSection = '{{ $card['id'] }}'"
                       class="group p-5 rounded-xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-{{ $card['color'] }}-200 transition-all duration-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                <x-ui.icon name="{{ $card['icon'] }}" size="24" />
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-{{ $card['color'] }}-700 transition-colors">{{ $card['title'] }}</h3>
                                <p class="text-sm text-slate-500 mt-1 leading-relaxed">{{ $card['desc'] }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- SECTION: KURIKULUM --}}
    {{-- ================================================================== --}}
    <section id="kurikulum" class="bg-white ring-1 ring-slate-900/5 md:rounded-2xl shadow-sm overflow-hidden scroll-mt-28">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-b from-indigo-50/30 to-white">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white shadow-sm ring-1 ring-indigo-500/50">1</span>
                Kurikulum
            </h2>
        </div>
        <div class="p-8 space-y-8">
            {{-- Mockup --}}
            <div class="space-y-4">
                <div class="bg-slate-900 rounded-xl p-6 shadow-2xl shadow-slate-200 overflow-hidden relative group">
                    {{-- UI Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="space-y-1.5">
                            <div class="h-5 w-32 bg-slate-700 rounded-md"></div>
                            <div class="h-3 w-48 bg-slate-800 rounded-md"></div>
                        </div>
                        <div class="relative z-10">
                            <div class="h-9 w-40 bg-indigo-500 rounded-lg shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2">
                                <div class="w-4 h-4 rounded bg-indigo-400"></div>
                                <div class="w-20 h-2 bg-indigo-100 rounded"></div>
                            </div>
                            <div class="absolute -right-3 -top-3 w-6 h-6 bg-rose-500 text-white text-xs font-bold rounded-full flex items-center justify-center ring-2 ring-slate-900">1</div>
                        </div>
                    </div>
                    
                    {{-- UI Table --}}
                    <div class="bg-slate-800/50 rounded-lg border border-slate-700/50 overflow-hidden">
                        <div class="grid grid-cols-4 gap-4 p-4 border-b border-slate-700/50 text-xs text-slate-400 font-medium uppercase tracking-wider">
                            <div>Nama</div>
                            <div>Tahun</div>
                            <div>Status</div>
                            <div class="text-right">Aksi</div>
                        </div>
                        <!-- Row 1 -->
                        <div class="grid grid-cols-4 gap-4 p-4 items-center group/row hover:bg-slate-700/30 transition-colors">
                            <div class="text-slate-200 font-medium">Kurikulum Merdeka</div>
                            <div class="text-slate-400">2024</div>
                            <div class="flex items-center gap-2">
                                <div class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/20">Aktif</div>
                                <div class="relative">
                                    <div class="absolute -left-3 -top-4 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-slate-900 z-10">2</div>
                                </div>
                            </div>
                            <div class="flex justify-end gap-2 relative">
                                <div class="w-8 h-8 rounded bg-slate-700 flex items-center justify-center text-slate-400">⋮</div>
                                <div class="absolute -right-2 -top-3 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-slate-900 z-10">3</div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="flex gap-3 items-start">
                        <span class="w-5 h-5 bg-rose-500 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">1</span>
                        <p class="text-slate-600"><strong class="text-slate-900">Tambah Kurikulum Baru</strong><br>Buat kurikulum untuk setiap perubahan tahun ajaran utama (misal: K13 ke Merdeka).</p>
                    </div>
                    <div class="flex gap-3 items-start">
                        <span class="w-5 h-5 bg-rose-500 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">2</span>
                        <p class="text-slate-600"><strong class="text-slate-900">Status Aktif</strong><br>Kurikulum aktif akan muncul sebagai default saat menambah mata pelajaran.</p>
                    </div>
                    <div class="flex gap-3 items-start">
                        <span class="w-5 h-5 bg-rose-500 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">3</span>
                        <p class="text-slate-600"><strong class="text-slate-900">Arsipkan/Hapus</strong><br>Jangan hapus kurikulum yang sudah digunakan di periode lama.</p>
                    </div>
                </div>
            </div>

            {{-- Warning Box --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex gap-4">
                <x-ui.icon name="alert-triangle" class="text-amber-600 shrink-0 mt-1" />
                <div>
                    <h4 class="font-bold text-amber-900 text-sm">Penting: Jangan Hapus Kurikulum Sembarangan</h4>
                    <p class="text-sm text-amber-700 mt-1">
                        Menghapus kurikulum akan <strong>menghapus permanen seluruh Mata Pelajaran</strong> yang ada di dalamnya. Jika ragu, cukup ubah status menjadi "Nonaktif".
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- SECTION: PERIODE SEMESTER --}}
    {{-- ================================================================== --}}
    <section id="periode" class="bg-white ring-1 ring-slate-900/5 md:rounded-2xl shadow-sm overflow-hidden scroll-mt-28">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-b from-emerald-50/30 to-white">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-sm font-bold text-white shadow-sm ring-1 ring-emerald-500/50">2</span>
                Periode Semester
            </h2>
        </div>
        <div class="p-8 space-y-8">
            {{-- Mockup --}}
            <div class="space-y-4">
                <div class="bg-white border rounded-xl p-6 shadow-sm relative overflow-hidden">
                    <div class="flex items-start justify-between gap-6">
                        <div class="w-full">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="text-lg font-bold text-slate-900">2024/2025 - Ganjil</div>
                                <div class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">Aktif</div>
                            </div>
                            <div class="h-2 w-48 bg-slate-100 rounded mb-4"></div>
                            
                            {{-- Action Buttons --}}
                            <div class="flex gap-2 mt-4 relative z-10">
                                <div class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-medium border border-indigo-100">
                                    Konfigurasi Kurikulum
                                </div>
                                <div class="px-3 py-1.5 rounded-lg bg-white overflow-hidden border border-slate-200 flex items-center gap-2 group shadow-sm ring-2 ring-rose-500 ring-offset-2">
                                    <div class="w-4 h-4 rounded bg-emerald-500"></div>
                                    <span class="text-sm font-bold text-slate-700">Generate Pertemuan</span>
                                    <div class="absolute -right-3 -top-3 w-6 h-6 bg-rose-500 text-white text-xs font-bold rounded-full flex items-center justify-center ring-2 ring-white z-20 shadow-lg">!</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-5 relative overflow-hidden">
                        <div class="absolute right-0 top-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
                            <x-ui.icon name="alert-octagon" size="120" />
                        </div>
                        <h4 class="font-bold text-rose-900 mb-2 relative z-10">Mengapa Absensi Tidak Muncul?</h4>
                        <p class="text-sm text-rose-800 leading-relaxed relative z-10">
                            Masalah paling umum terjadi: <strong>Anda belum melakukan "Generate Pertemuan"</strong>.
                            Tombol "Generate Pertemuan" (di menu tabel atau detail periode) wajib diklik setiap awal semester.
                        </p>
                        <ul class="mt-3 space-y-1.5 text-sm text-rose-800 relative z-10">
                            <li class="flex items-center gap-2">
                                <x-ui.icon name="x" size="14" class="text-rose-600" />
                                <span>Tanpa generate = Tidak ada data pertemuan</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-ui.icon name="x" size="14" class="text-rose-600" />
                                <span>Guru tidak bisa absen</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                        <h4 class="font-bold text-slate-800 mb-2">Detail Penting Lainnya:</h4>
                        <ul class="space-y-3 text-sm text-slate-600">
                            <li class="flex gap-2">
                                <div class="font-bold text-indigo-600">1.</div>
                                <div><strong>Set Aktif = Default.</strong> Periode yang diset "Aktif" akan menjadi default di semua halaman (Jadwal, Absensi, Laporan).</div>
                            </li>
                            <li class="flex gap-2">
                                <div class="font-bold text-indigo-600">2.</div>
                                <div><strong>Konfigurasi Tingkat.</strong> Anda wajib mengatur "Tingkat X pakai Kurikulum apa" di menu Konfigurasi Kurikulum. Jika tidak, dropdown mapel akan kosong.</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- SECTION: MATA PELAJARAN --}}
    {{-- ================================================================== --}}
    <section id="mapel" class="bg-white ring-1 ring-slate-900/5 md:rounded-2xl shadow-sm overflow-hidden scroll-mt-28">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-b from-amber-50/30 to-white">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-sm font-bold text-white shadow-sm ring-1 ring-amber-500/50">3</span>
                Mata Pelajaran
            </h2>
        </div>
        <div class="p-8 space-y-8">
            <div class="bg-white border rounded-xl shadow-sm p-6 overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    {{-- Visual Mockup --}}
                    <div class="relative group">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-3">
                            <div class="flex gap-2">
                                <div class="h-8 w-24 bg-indigo-600 rounded-md"></div>
                                <div class="h-8 w-24 bg-slate-200 rounded-md"></div>
                            </div>
                            <div class="border rounded bg-white p-3 space-y-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <div class="h-3 w-12 bg-slate-200 rounded"></div>
                                        <div class="h-8 w-full bg-slate-50 border rounded"></div>
                                    </div>
                                    <div class="space-y-1 relative">
                                        <div class="h-3 w-16 bg-slate-200 rounded"></div>
                                        <div class="h-8 w-full bg-white border border-indigo-200 ring-2 ring-indigo-500/20 rounded flex items-center px-2 text-xs text-slate-700">
                                            Pak Ahmad ⭐
                                        </div>
                                        <div class="absolute -right-2 -top-2 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white z-10">1</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Explanation --}}
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-5 h-5 bg-rose-500 text-white text-xs font-bold rounded-full flex items-center justify-center">1</span>
                                <h4 class="font-bold text-slate-800">Guru Utama (Tanda Bintang ⭐)</h4>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Saat menambahkan guru ke mapel, guru pertama yang Anda pilih akan otomatis menjadi <strong>Guru Utama</strong>. 
                            </p>
                            <p class="text-sm text-slate-600 mt-2">
                                <strong>Fungsinya:</strong> Di halaman Jadwal Mengajar, Guru Utama akan muncul paling atas di dropdown, mempercepat proses input jadwal.
                            </p>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-lg text-sm text-indigo-800">
                            <strong>Tip Pro:</strong> Gunakan Kelompok Mapel (A/B/C) yang sesuai dengan rapor agar urutan cetak jadwal rapi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- SECTION: TEMPLATE JAM (Combined with Jadwal for flow) --}}
    {{-- ================================================================== --}}
    <section id="template-jam" class="bg-white ring-1 ring-slate-900/5 md:rounded-2xl shadow-sm overflow-hidden scroll-mt-28">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-b from-purple-50/30 to-white">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-600 text-sm font-bold text-white shadow-sm ring-1 ring-purple-500/50">4</span>
                Template Jam
            </h2>
        </div>
        <div class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="font-bold text-slate-800 mb-3">Fitur Cerdas: Auto-Sync Waktu</h3>
                    <p class="text-sm text-slate-600 mb-4 leading-relaxed">
                        Sistem template jam memiliki fitur <strong>Auto-Sync</strong>. 
                        Ketika Anda mengubah "Jam Selesai" pada baris ke-1, sistem otomatis mengubah "Jam Mulai" pada baris ke-2 agar bersambung.
                    </p>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-500 font-mono">
                        07:00 -> 07:40 <span class="text-emerald-600 font-bold">✓ Diubah user</span><br>
                        <span class="text-emerald-600 font-bold">07:40</span> -> 08:20 <span class="text-indigo-600">✓ Auto-update sistem</span>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 mb-3">Copy Template</h3>
                    <p class="text-sm text-slate-600 mb-4 leading-relaxed">
                        Jangan buat template dari nol setiap semester! Gunakan fitur <strong>"Salin Template"</strong> untuk mengcopy seluruh struktur jam dari semester lalu ke semester ini.
                    </p>
                    <div class="bg-purple-50 p-3 rounded-lg flex items-center gap-2 text-purple-700 text-sm font-medium">
                        <x-ui.icon name="copy" size="16" />
                        Hemat waktu 90% dengan fitur Salin.
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- SECTION: JADWAL MENGAJAR --}}
    {{-- ================================================================== --}}
    <section id="jadwal" class="bg-white ring-1 ring-slate-900/5 md:rounded-2xl shadow-sm overflow-hidden scroll-mt-28">
        <div class="px-8 py-6 border-b border-slate-100 bg-gradient-to-b from-blue-50/30 to-white">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white shadow-sm ring-1 ring-blue-500/50">5</span>
                Jadwal Mengajar
            </h2>
        </div>
        <div class="p-8 space-y-8">
            {{-- Critical Warning about Dependencies --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-white rounded-lg shadow-sm text-blue-600">
                        <x-ui.icon name="check-square" size="20" />
                    </div>
                    <div>
                        <h4 class="font-bold text-blue-900">Sebelum Mengisi Jadwal...</h4>
                        <p class="text-sm text-blue-800 mt-1 mb-3">
                            Pastikan 3 hal ini sudah siap. Jika salah satu belum ada, dropdown atau matrix tidak akan muncul:
                        </p>
                        <ul class="space-y-2 text-sm text-blue-700">
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                Template Jam untuk hari tersebut sudah dibuat
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                Mata Pelajaran sudah ada di kurikulum yang dipakai kelas
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                Konfigurasi Tingkat (di menu Periode) sudah sesuai
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Mockup Matrix --}}
            <div>
                <p class="text-sm text-slate-500 mb-4 font-medium px-1">📸 Cara Input Cepat:</p>
                <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Jam</th>
                                <th class="px-4 py-3">Mapel</th>
                                <th class="px-4 py-3">Guru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-4 py-3 font-mono text-slate-500">07:00</td>
                                <td class="px-4 py-3">
                                    <div class="border border-emerald-500 ring-2 ring-emerald-500/20 rounded px-3 py-1.5 bg-white text-slate-800 relative">
                                        Matematika
                                        <div class="absolute -top-3 -right-3 bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm">1</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="border border-slate-200 rounded px-3 py-1.5 bg-slate-50 text-slate-400 relative">
                                        Pilih Guru...
                                        <div class="absolute -top-3 -right-3 bg-indigo-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm">2</div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex gap-6 mt-4 text-xs text-slate-500">
                    <div>
                        <span class="font-bold text-emerald-600">1. Pilih Mapel</span>
                        <br>Dropdown guru akan otomatis terfilter sesuai mapel.
                    </div>
                    <div>
                        <span class="font-bold text-indigo-600">2. Pilih Guru</span>
                        <br>Otomatis tersimpan (autosave) begitu guru dipilih.
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <div class="text-center pt-8 border-t border-slate-200">
        <a href="#overview" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors text-sm font-medium">
            <x-ui.icon name="arrow-up" size="16" />
            Kembali ke Atas
        </a>
    </div>

</div>
@endsection
