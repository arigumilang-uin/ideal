<?php

namespace App\Models;

use App\Enums\Semester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Model Periode Semester
 * 
 * Konfigurasi tanggal awal/akhir semester yang diinput oleh operator.
 * Sekarang juga container untuk tingkat_kurikulum dan template_jam.
 */
class PeriodeSemester extends Model
{
    use HasFactory;

    protected $table = 'periode_semester';

    protected $fillable = [
        'nama_periode',
        'semester',
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'semester' => Semester::class,
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Assignment kurikulum per tingkat untuk periode ini
     */
    public function tingkatKurikulum(): HasMany
    {
        return $this->hasMany(TingkatKurikulum::class, 'periode_semester_id');
    }

    /**
     * Template jam pelajaran untuk periode ini
     */
    public function templateJam(): HasMany
    {
        return $this->hasMany(TemplateJam::class, 'periode_semester_id');
    }

    /**
     * Jadwal mengajar untuk periode ini
     */
    public function jadwalMengajar(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class, 'periode_semester_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active period
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get current active period
     */
    public static function current(): ?self
    {
        return self::active()->first();
    }

    // =====================================================================
    // ----------------------- KURIKULUM HELPERS -----------------------
    // =====================================================================

    /**
     * Get kurikulum for a specific tingkat
     */
    public function getKurikulumFor(string $tingkat): ?Kurikulum
    {
        return TingkatKurikulum::getKurikulumFor($this->id, $tingkat);
    }

    /**
     * Get all tingkat-kurikulum assignments
     */
    public function getTingkatKurikulumMap(): array
    {
        return $this->tingkatKurikulum()
            ->with('kurikulum')
            ->get()
            ->mapWithKeys(fn($tk) => [$tk->tingkat => $tk->kurikulum])
            ->toArray();
    }

    /**
     * Check if all tingkat have kurikulum assigned
     */
    public function hasCompleteKurikulumConfig(): bool
    {
        $assigned = $this->tingkatKurikulum()->count();
        return $assigned >= 3; // X, XI, XII
    }

    // =====================================================================
    // ----------------------- TEMPLATE JAM HELPERS -----------------------
    // =====================================================================

    /**
     * Get template jam for a specific hari
     */
    public function getTemplateJamFor(string $hari): \Illuminate\Database\Eloquent\Collection
    {
        return TemplateJam::getSlotsFor($this->id, $hari);
    }

    /**
     * Get only pelajaran slots for a specific hari
     */
    public function getPelajaranSlotsFor(string $hari): \Illuminate\Database\Eloquent\Collection
    {
        return TemplateJam::getPelajaranSlotsFor($this->id, $hari);
    }

    /**
     * Get list of days that have template configured
     */
    public function getConfiguredDays(): array
    {
        return TemplateJam::getConfiguredDays($this->id);
    }

    /**
     * Check if template jam has been configured
     */
    public function hasTemplateJamConfig(): bool
    {
        return $this->templateJam()->active()->exists();
    }

    /**
     * Copy template jam from another periode
     */
    public function copyTemplateJamFrom(int $fromPeriodeId): int
    {
        return TemplateJam::copyFromPeriode($fromPeriodeId, $this->id);
    }

    // =====================================================================
    // ----------------------- DATE HELPERS -----------------------
    // =====================================================================

    /**
     * Get all dates of a specific day within this period
     * 
     * @param int $dayOfWeek 1=Monday, 6=Saturday
     * @return array<Carbon>
     */
    public function getDatesForDay(int $dayOfWeek): array
    {
        $dates = [];
        $current = $this->tanggal_mulai->copy();
        
        // Find first occurrence of the day
        while ($current->dayOfWeekIso !== $dayOfWeek) {
            $current->addDay();
        }
        
        // Collect all occurrences until end date
        while ($current->lte($this->tanggal_selesai)) {
            $dates[] = $current->copy();
            $current->addWeek();
        }
        
        return $dates;
    }

    /**
     * Count total occurrences of a day in this period
     */
    public function countDayOccurrences(int $dayOfWeek): int
    {
        return count($this->getDatesForDay($dayOfWeek));
    }

    /**
     * Check if a date is within this period
     */
    public function containsDate(Carbon $date): bool
    {
        return $date->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    // =====================================================================
    // ----------------------- DISPLAY HELPERS -----------------------
    // =====================================================================

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->semester->value} {$this->tahun_ajaran}";
    }

    /**
     * Set this period as active (and deactivate others)
     */
    public function setAsActive(): void
    {
        // Deactivate all other periods
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this one
        $this->update(['is_active' => true]);
    }

    /**
     * Get configuration status for display
     */
    public function getConfigStatusAttribute(): array
    {
        return [
            'kurikulum' => $this->hasCompleteKurikulumConfig(),
            'template_jam' => $this->hasTemplateJamConfig(),
            'jadwal' => $this->jadwalMengajar()->exists(),
        ];
    }
}
