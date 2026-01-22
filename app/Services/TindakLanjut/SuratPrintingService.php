<?php

namespace App\Services\TindakLanjut;

use App\Models\TindakLanjut;
use App\Models\SuratPanggilanPrintLog;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Service untuk handle pencetakan surat (PDF Generation)
 * 
 * RESPONSIBILITY:
 * - Generate PDF dari view
 * - Handle resource assets (logo, etc)
 * - Log aktivitas pencetakan
 * 
 * @package App\Services\TindakLanjut
 */
class SuratPrintingService
{
    /**
     * Generate PDF stream untuk surat panggilan.
     * 
     * @param int $tindakLanjutId
     * @param int $userId logged in user id for logging
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateSuratPdf(int $tindakLanjutId, int $userId)
    {
        $kasus = TindakLanjut::with(['siswa.kelas.jurusan', 'siswa.waliMurid', 'suratPanggilan'])->findOrFail($tindakLanjutId);
        $surat = $kasus->suratPanggilan;

        // Log print activity
        SuratPanggilanPrintLog::create([
            'surat_panggilan_id' => $surat->id,
            'user_id' => $userId,
            'printed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Convert logo to Base64
        $logoBase64 = $this->getLogoBase64();

        // Generate PDF
        // Note: Template now reads pembina_roles directly from $surat->pembina_roles
        $pdf = Pdf::loadView('pdf.surat-panggilan', [
            'siswa' => $kasus->siswa,
            'surat' => $surat,
            'logoBase64' => $logoBase64,
        ]);

        // Set paper size (Custom Legal/F4 size mostly used in schools)
        // 21.5 cm x 33 cm = 609.4488 pt x 935.433 pt
        $pdf->setPaper([0, 0, 609.4488, 935.433], 'portrait');
        
        return [
            'pdf_stream' => $pdf,
            'filename' => 'Surat_Panggilan_' . $kasus->siswa->nisn . '.pdf'
        ];
    }

    /**
     * Get Logo image as Base64 string.
     */
    private function getLogoBase64(): ?string
    {
        $path = public_path('assets/images/logo_riau.png');
        
        if (!File::exists($path)) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = File::get($path);
        
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
