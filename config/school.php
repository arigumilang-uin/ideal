<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Informasi Sekolah
    |--------------------------------------------------------------------------
    |
    | Konfigurasi data sekolah yang digunakan di seluruh aplikasi.
    | Perubahan di sini akan otomatis mempengaruhi semua views yang menggunakannya.
    |
    */

    'nama' => env('SCHOOL_NAME', 'SMKN 1 Lubuk Dalam'),
    
    'nama_lengkap' => env('SCHOOL_FULL_NAME', 'SMK Negeri 1 Lubuk Dalam'),
    
    'singkatan' => env('SCHOOL_ABBR', 'SMKN 1 LD'),
    
    'alamat' => env('SCHOOL_ADDRESS', 'Jl. Raya Lubuk Dalam, Kabupaten Siak, Riau'),
    
    'kabupaten' => env('SCHOOL_DISTRICT', 'Kabupaten Siak'),
    
    'provinsi' => env('SCHOOL_PROVINCE', 'Riau'),
    
    'telepon' => env('SCHOOL_PHONE', ''),
    
    'email' => env('SCHOOL_EMAIL', ''),
    
    'website' => env('SCHOOL_WEBSITE', ''),

    /*
    |--------------------------------------------------------------------------
    | Tahun Ajaran
    |--------------------------------------------------------------------------
    |
    | Tahun ajaran aktif. Format: "YYYY/YYYY" (tahun awal/tahun akhir)
    | Dapat diubah via .env atau langsung di sini.
    |
    */

    'tahun_ajaran' => env('SCHOOL_YEAR', '2026/2027'),

    /*
    |--------------------------------------------------------------------------
    | Kepala Sekolah
    |--------------------------------------------------------------------------
    */

    'kepala_sekolah' => [
        'nama' => env('PRINCIPAL_NAME', ''),
        'nip' => env('PRINCIPAL_NIP', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sistem Informasi
    |--------------------------------------------------------------------------
    */

    'sistem' => [
        'nama' => 'IDEAL',
        'nama_lengkap' => 'Integrated Discipline & Educational Achievement Log',
        'versi' => '1.0.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Jam Pelajaran (Time Slots)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi jam pelajaran untuk matrix jadwal mengajar.
    | Format: ['start' => 'HH:MM', 'end' => 'HH:MM', 'label' => 'Jam X']
    | Sesuaikan dengan jadwal jam pelajaran sekolah Anda.
    |
    */

    'jam_pelajaran' => [
        ['start' => '07:00', 'end' => '07:45', 'label' => 'Jam 1'],
        ['start' => '07:45', 'end' => '08:30', 'label' => 'Jam 2'],
        ['start' => '08:30', 'end' => '09:15', 'label' => 'Jam 3'],
        ['start' => '09:15', 'end' => '10:00', 'label' => 'Jam 4'],
        // Istirahat 10:00 - 10:15
        ['start' => '10:15', 'end' => '11:00', 'label' => 'Jam 5'],
        ['start' => '11:00', 'end' => '11:45', 'label' => 'Jam 6'],
        // Istirahat 11:45 - 12:30
        ['start' => '12:30', 'end' => '13:15', 'label' => 'Jam 7'],
        ['start' => '13:15', 'end' => '14:00', 'label' => 'Jam 8'],
    ],

];
