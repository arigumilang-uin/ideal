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
    // ----------------------- HELPER METHODS -----------------------
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
}
