<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model TemplateJam
 * 
 * Template slot waktu per periode semester dan per hari.
 * Menggantikan jam_pelajaran yang sebelumnya global.
 * 
 * Features:
 * - Format jam berbeda antar periode (tahun ajaran berbeda)
 * - Format jam berbeda antar hari (Senin 8 slot, Jumat 6 slot)
 * - Tracking slot non-pelajaran (istirahat, ishoma, upacara)
 */
class TemplateJam extends Model
{
    use HasFactory;

    protected $table = 'template_jam';

    protected $fillable = [
        'periode_semester_id',
        'hari',
        'urutan',
        'label',
        'jam_mulai',
        'jam_selesai',
        'tipe',
        'is_active',
    ];

    protected $casts = [
        'hari' => Hari::class,
        'urutan' => 'integer',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // Tipe slot constants
    const TIPE_PELAJARAN = 'pelajaran';
    const TIPE_ISTIRAHAT = 'istirahat';
    const TIPE_ISHOMA = 'ishoma';
    const TIPE_UPACARA = 'upacara';
    const TIPE_LAINNYA = 'lainnya';

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Periode semester untuk template ini
     */
    public function periodeSemester(): BelongsTo
    {
        return $this->belongsTo(PeriodeSemester::class, 'periode_semester_id');
    }

    /**
     * Jadwal mengajar yang menggunakan slot ini
     */
    public function jadwalMengajar(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class, 'template_jam_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active slots
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by periode
     */
    public function scopeForPeriode($query, int $periodeId)
    {
        return $query->where('periode_semester_id', $periodeId);
    }

    /**
     * Scope: Filter by hari
     */
    public function scopeForHari($query, $hari)
    {
        if ($hari instanceof Hari) {
            $hari = $hari->value;
        }
        return $query->where('hari', $hari);
    }

    /**
     * Scope: Only pelajaran slots (exclude breaks)
     */
    public function scopePelajaranOnly($query)
    {
        return $query->where('tipe', self::TIPE_PELAJARAN);
    }

    /**
     * Scope: Order by urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }

    // =====================================================================
    // ----------------------- STATIC METHODS -----------------------
    // =====================================================================

    /**
     * Get all slots for a periode and hari
     */
    public static function getSlotsFor(int $periodeId, string $hari): \Illuminate\Database\Eloquent\Collection
    {
        return self::forPeriode($periodeId)
            ->forHari($hari)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get only pelajaran slots (for jadwal assignment)
     */
    public static function getPelajaranSlotsFor(int $periodeId, string $hari): \Illuminate\Database\Eloquent\Collection
    {
        return self::forPeriode($periodeId)
            ->forHari($hari)
            ->pelajaranOnly()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get all hari that have slots configured for a periode
     */
    public static function getConfiguredDays(int $periodeId): array
    {
        return self::forPeriode($periodeId)
            ->active()
            ->distinct()
            ->pluck('hari')
            ->toArray();
    }

    /**
     * Copy template from one periode to another
     */
    public static function copyFromPeriode(int $fromPeriodeId, int $toPeriodeId): int
    {
        $slots = self::forPeriode($fromPeriodeId)->get();
        $count = 0;
        
        foreach ($slots as $slot) {
            self::create([
                'periode_semester_id' => $toPeriodeId,
                'hari' => $slot->hari,
                'urutan' => $slot->urutan,
                'label' => $slot->label,
                'jam_mulai' => $slot->jam_mulai,
                'jam_selesai' => $slot->jam_selesai,
                'tipe' => $slot->tipe,
                'is_active' => $slot->is_active,
            ]);
            $count++;
        }
        
        return $count;
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Get formatted time range
     */
    public function getWaktuAttribute(): string
    {
        $mulai = $this->jam_mulai instanceof \DateTime 
            ? $this->jam_mulai->format('H:i') 
            : $this->jam_mulai;
        $selesai = $this->jam_selesai instanceof \DateTime 
            ? $this->jam_selesai->format('H:i') 
            : $this->jam_selesai;
            
        return "{$mulai} - {$selesai}";
    }

    /**
     * Get display label with time
     */
    public function getDisplayLabelAttribute(): string
    {
        return "{$this->label} ({$this->waktu})";
    }

    /**
     * Check if this is a teaching slot
     */
    public function isPelajaran(): bool
    {
        return $this->tipe === self::TIPE_PELAJARAN;
    }

    /**
     * Check if this is a break slot
     */
    public function isBreak(): bool
    {
        return in_array($this->tipe, [
            self::TIPE_ISTIRAHAT,
            self::TIPE_ISHOMA,
        ]);
    }

    /**
     * Get duration in minutes
     */
    public function getDurasiAttribute(): int
    {
        $mulai = $this->jam_mulai instanceof \DateTime ? $this->jam_mulai : new \DateTime($this->jam_mulai);
        $selesai = $this->jam_selesai instanceof \DateTime ? $this->jam_selesai : new \DateTime($this->jam_selesai);
        
        return ($selesai->getTimestamp() - $mulai->getTimestamp()) / 60;
    }

    /**
     * Get tipe badge color for UI
     */
    public function getTipeBadgeColorAttribute(): string
    {
        return match($this->tipe) {
            self::TIPE_PELAJARAN => 'emerald',
            self::TIPE_ISTIRAHAT => 'amber',
            self::TIPE_ISHOMA => 'orange',
            self::TIPE_UPACARA => 'blue',
            self::TIPE_LAINNYA => 'slate',
            default => 'slate',
        };
    }
}
