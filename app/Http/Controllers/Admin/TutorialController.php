<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Tutorial Controller
 * 
 * Menyediakan halaman panduan/dokumentasi untuk admin.
 */
class TutorialController extends Controller
{
    /**
     * Panduan Kurikulum & Jadwal
     */
    public function kurikulumJadwal(): View
    {
        return view('admin.panduan.kurikulum-jadwal');
    }
}
