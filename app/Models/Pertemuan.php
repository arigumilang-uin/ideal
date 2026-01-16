<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model Pertemuan
 * 
 * Instance pertemuan per tanggal real.
 * Auto-generated dari template jadwal_mengajar berdasarkan periode semester.
 */
class Pertemuan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pertemuan';

    protected $fillable = [
        'jadwal_mengajar_id',
        'tanggal',
        'pertemuan_ke',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'pertemuan_ke' => 'integer',
    ];

    // Status constants
    const STATUS_AKTIF = 'aktif';
    const STATUS_SELESAI = 'selesai';
    const STATUS_KOSONG = 'kosong';

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Jadwal template untuk pertemuan ini
     */
    public function jadwalMengajar(): BelongsTo
    {
        return $this->belongsTo(JadwalMengajar::class, 'jadwal_mengajar_id');
    }

    /**
     * Absensi yang tercatat untuk pertemuan ini
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'pertemuan_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active meetings
     */
    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    /**
     * Scope: Filter by date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    /**
     * Scope: Today's meetings
     */
    public function scopeToday($query)
    {
        return $query->onDate(today());
    }

    /**
     * Scope: For specific jadwal
     */
    public function scopeForJadwal($query, int $jadwalId)
    {
        return $query->where('jadwal_mengajar_id', $jadwalId);
    }

    /**
     * Scope: Order by pertemuan_ke
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('pertemuan_ke');
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Check if this meeting is today
     */
    public function isToday(): bool
    {
        return $this->tanggal->isToday();
    }

    /**
     * Check if this meeting has been completed (absensi exists)
     */
    public function sudahDiabsen(): bool
    {
        return $this->absensi()->exists();
    }

    /**
     * Get total meetings for the same jadwal
     */
    public function getTotalPertemuanAttribute(): int
    {
        return self::forJadwal($this->jadwal_mengajar_id)->count();
    }

    /**
     * Mark as completed
     */
    public function markAsSelesai(): void
    {
        $this->update(['status' => self::STATUS_SELESAI]);
    }

    /**
     * Mark as empty/cancelled
     */
    public function markAsKosong(?string $keterangan = null): void
    {
        $this->update([
            'status' => self::STATUS_KOSONG,
            'keterangan' => $keterangan,
        ]);
    }
}
