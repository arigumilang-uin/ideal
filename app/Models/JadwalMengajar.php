<?php

namespace App\Models;

use App\Enums\Hari;
use App\Enums\Semester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Jadwal Mengajar
 * 
 * Menghubungkan Guru → Mata Pelajaran → Kelas → Hari/Jam
 * Fleksibel: 1 guru bisa mengajar banyak kelas dengan mata pelajaran berbeda
 */
class JadwalMengajar extends Model
{
    use HasFactory;

    protected $table = 'jadwal_mengajar';

    protected $fillable = [
        'user_id',
        'mata_pelajaran_id',
        'kelas_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'semester',
        'tahun_ajaran',
        'is_active',
    ];

    protected $casts = [
        'hari' => Hari::class,
        'semester' => Semester::class,
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Guru yang mengajar
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mata pelajaran yang diajarkan
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    /**
     * Kelas yang diajar
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Absensi yang tercatat untuk jadwal ini
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'jadwal_mengajar_id');
    }

    /**
     * Pertemuan yang ter-generate dari jadwal ini
     */
    public function pertemuan(): HasMany
    {
        return $this->hasMany(Pertemuan::class, 'jadwal_mengajar_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by semester and tahun ajaran
     */
    public function scopeForPeriod($query, Semester $semester, string $tahunAjaran)
    {
        return $query->where('semester', $semester)
                     ->where('tahun_ajaran', $tahunAjaran);
    }

    /**
     * Scope: Current active period
     */
    public function scopeCurrentPeriod($query)
    {
        return $query->forPeriod(Semester::current(), Semester::currentTahunAjaran());
    }

    /**
     * Scope: Filter by hari
     */
    public function scopeForHari($query, Hari $hari)
    {
        return $query->where('hari', $hari);
    }

    /**
     * Scope: Today's schedules
     */
    public function scopeToday($query)
    {
        $today = Hari::today();
        if (!$today) return $query->whereRaw('1 = 0'); // No results if Sunday
        
        return $query->forHari($today);
    }

    /**
     * Scope: Filter by guru
     */
    public function scopeForGuru($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by kelas
     */
    public function scopeForKelas($query, int $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Scope: Order by time
     */
    public function scopeOrderByTime($query)
    {
        return $query->orderBy('jam_mulai');
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
     * Get full display name
     */
    public function getDisplayNameAttribute(): string
    {
        $mapel = $this->mataPelajaran->nama_mapel ?? 'Unknown';
        $kelas = $this->kelas->nama_kelas ?? 'Unknown';
        return "{$mapel} - {$kelas}";
    }

    /**
     * Check if this schedule is for today
     */
    public function isToday(): bool
    {
        return $this->hari === Hari::today();
    }

    /**
     * Check if this jadwal sudah diabsen hari ini
     */
    public function sudahDiabsenHariIni(): bool
    {
        return $this->absensi()
            ->whereDate('tanggal', today())
            ->exists();
    }

    /**
     * Hitung jumlah siswa yang harus diabsen (dari kelas)
     */
    public function jumlahSiswa(): int
    {
        return $this->kelas->siswa()->count();
    }
}
