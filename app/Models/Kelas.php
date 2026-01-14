<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    /**
     * Beri tahu Laravel bahwa tabel 'kelas' tidak punya kolom timestamps.
     */
    public $timestamps = false;

    protected $table = 'kelas';

    protected $fillable = [
        'jurusan_id',
        'konsentrasi_id',
        'wali_kelas_user_id',
        'nama_kelas',
        'tingkat',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Relasi Wajib: SATU Kelas DIMILIKI OLEH SATU Jurusan.
     */
    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Relasi Opsional: SATU Kelas DIMILIKI OLEH SATU Konsentrasi.
     * Nullable: Kelas X umumnya belum masuk konsentrasi.
     */
    public function konsentrasi(): BelongsTo
    {
        return $this->belongsTo(Konsentrasi::class, 'konsentrasi_id');
    }

    /**
     * Relasi Wajib: SATU Kelas DIMILIKI OLEH SATU Wali Kelas (User).
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_user_id');
    }

    /**
     * Relasi Wajib: SATU Kelas MEMILIKI BANYAK Siswa.
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    /**
     * Jadwal mengajar untuk kelas ini
     */
    public function jadwalMengajar(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class, 'kelas_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Filter by tingkat
     */
    public function scopeForTingkat($query, string $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    /**
     * Scope: Filter by jurusan
     */
    public function scopeForJurusan($query, int $jurusanId)
    {
        return $query->where('jurusan_id', $jurusanId);
    }

    // =====================================================================
    // ----------------------- KURIKULUM HELPERS -----------------------
    // =====================================================================

    /**
     * Get kurikulum for this kelas in a specific periode
     * 
     * Kurikulum ditentukan berdasarkan tingkat kelas, bukan kelas individual.
     * Semua kelas dengan tingkat yang sama punya kurikulum yang sama.
     */
    public function getKurikulumFor(int $periodeId): ?Kurikulum
    {
        return TingkatKurikulum::getKurikulumFor($periodeId, $this->tingkat);
    }

    /**
     * Get kurikulum ID for this kelas in a specific periode
     */
    public function getKurikulumIdFor(int $periodeId): ?int
    {
        return TingkatKurikulum::getKurikulumIdFor($periodeId, $this->tingkat);
    }

    /**
     * Get available mata pelajaran for this kelas in a specific periode
     * 
     * Returns mata pelajaran yang sesuai dengan kurikulum tingkat ini.
     */
    public function getAvailableMapel(int $periodeId): \Illuminate\Database\Eloquent\Collection
    {
        return MataPelajaran::getForKelas($this->id, $periodeId);
    }

    /**
     * Check if kelas has kurikulum assigned for a periode
     */
    public function hasKurikulumFor(int $periodeId): bool
    {
        return $this->getKurikulumIdFor($periodeId) !== null;
    }

    // =====================================================================
    // ----------------------- DISPLAY HELPERS -----------------------
    // =====================================================================

    /**
     * Get display name with jurusan
     */
    public function getDisplayNameAttribute(): string
    {
        $jurusan = $this->jurusan?->singkatan ?? $this->jurusan?->nama_jurusan ?? '';
        return $jurusan ? "{$this->nama_kelas} ({$jurusan})" : $this->nama_kelas;
    }

    /**
     * Get tingkat label (for display)
     */
    public function getTingkatLabelAttribute(): string
    {
        return "Kelas {$this->tingkat}";
    }
}