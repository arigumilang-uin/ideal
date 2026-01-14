<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model TingkatKurikulum
 * 
 * Menentukan kurikulum yang digunakan oleh setiap tingkat (X, XI, XII)
 * pada periode semester tertentu.
 * 
 * Contoh:
 * - Periode Ganjil 2026/2027: Tingkat X → Kurikulum Merdeka
 * - Periode Ganjil 2026/2027: Tingkat XII → Kurikulum 2013
 */
class TingkatKurikulum extends Model
{
    use HasFactory;

    protected $table = 'tingkat_kurikulum';

    protected $fillable = [
        'periode_semester_id',
        'tingkat',
        'kurikulum_id',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Periode semester untuk assignment ini
     */
    public function periodeSemester(): BelongsTo
    {
        return $this->belongsTo(PeriodeSemester::class, 'periode_semester_id');
    }

    /**
     * Kurikulum yang di-assign
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter by periode
     */
    public function scopeForPeriode($query, int $periodeId)
    {
        return $query->where('periode_semester_id', $periodeId);
    }

    /**
     * Scope: Filter by tingkat
     */
    public function scopeForTingkat($query, string $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    // =====================================================================
    // ----------------------- STATIC METHODS -----------------------
    // =====================================================================

    /**
     * Get kurikulum for a specific tingkat in a periode
     */
    public static function getKurikulumFor(int $periodeId, string $tingkat): ?Kurikulum
    {
        $assignment = self::forPeriode($periodeId)
            ->forTingkat($tingkat)
            ->with('kurikulum')
            ->first();
        
        return $assignment?->kurikulum;
    }

    /**
     * Get kurikulum ID for a specific tingkat in a periode
     */
    public static function getKurikulumIdFor(int $periodeId, string $tingkat): ?int
    {
        return self::forPeriode($periodeId)
            ->forTingkat($tingkat)
            ->value('kurikulum_id');
    }
}
