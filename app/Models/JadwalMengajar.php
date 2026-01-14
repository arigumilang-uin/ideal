<?php

namespace App\Models;

use App\Enums\Hari;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Jadwal Mengajar
 * 
 * Menghubungkan Guru → Mata Pelajaran → Kelas → Slot Waktu
 * 
 * Perubahan dari versi sebelumnya:
 * - Tidak lagi menyimpan hari/jam langsung, tapi reference ke template_jam
 * - Terhubung ke periode_semester secara eksplisit
 */
class JadwalMengajar extends Model
{
    use HasFactory;

    protected $table = 'jadwal_mengajar';

    protected $fillable = [
        'periode_semester_id',
        'template_jam_id',
        'kelas_id',
        'mata_pelajaran_id',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Periode semester
     */
    public function periodeSemester(): BelongsTo
    {
        return $this->belongsTo(PeriodeSemester::class, 'periode_semester_id');
    }

    /**
     * Template jam (slot waktu)
     */
    public function templateJam(): BelongsTo
    {
        return $this->belongsTo(TemplateJam::class, 'template_jam_id');
    }

    /**
     * Guru yang mengajar
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias for guru (backward compatibility)
     */
    public function user(): BelongsTo
    {
        return $this->guru();
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
        return $query->where('jadwal_mengajar.is_active', true);
    }

    /**
     * Scope: Filter by periode
     */
    public function scopeForPeriode($query, int $periodeId)
    {
        return $query->where('jadwal_mengajar.periode_semester_id', $periodeId);
    }

    /**
     * Scope: Current active period
     */
    public function scopeCurrentPeriod($query)
    {
        $currentPeriode = PeriodeSemester::current();
        if (!$currentPeriode) return $query->whereRaw('1 = 0');
        
        return $query->where('jadwal_mengajar.periode_semester_id', $currentPeriode->id);
    }

    /**
     * Scope: Filter by hari (via template_jam)
     */
    public function scopeForHari($query, $hari)
    {
        if ($hari instanceof Hari) {
            $hari = $hari->value;
        }
        return $query->whereHas('templateJam', function($q) use ($hari) {
            $q->where('hari', $hari);
        });
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
        return $query->where('jadwal_mengajar.user_id', $userId);
    }

    /**
     * Scope: Filter by kelas
     */
    public function scopeForKelas($query, int $kelasId)
    {
        return $query->where('jadwal_mengajar.kelas_id', $kelasId);
    }

    /**
     * Scope: Order by time (via template_jam)
     */
    public function scopeOrderByTime($query)
    {
        return $query->join('template_jam', 'jadwal_mengajar.template_jam_id', '=', 'template_jam.id')
                     ->orderBy('template_jam.urutan')
                     ->select('jadwal_mengajar.*');
    }

    /**
     * Scope: With all related data
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['templateJam', 'kelas.jurusan', 'mataPelajaran', 'guru']);
    }

    // =====================================================================
    // ----------------------- ACCESSORS (Derived from templateJam) -----------------------
    // =====================================================================

    /**
     * Get hari from template_jam
     */
    public function getHariAttribute(): ?Hari
    {
        return $this->templateJam?->hari;
    }

    /**
     * Get jam_mulai from template_jam
     */
    public function getJamMulaiAttribute()
    {
        return $this->templateJam?->jam_mulai;
    }

    /**
     * Get jam_selesai from template_jam
     */
    public function getJamSelesaiAttribute()
    {
        return $this->templateJam?->jam_selesai;
    }

    /**
     * Get formatted time range
     */
    public function getWaktuAttribute(): string
    {
        return $this->templateJam?->waktu ?? '-';
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

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

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

    /**
     * Get semester from periode
     */
    public function getSemesterAttribute()
    {
        return $this->periodeSemester?->semester;
    }

    /**
     * Get tahun ajaran from periode
     */
    public function getTahunAjaranAttribute(): ?string
    {
        return $this->periodeSemester?->tahun_ajaran;
    }
}
