<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Mata Pelajaran
 * 
 * Daftar mata pelajaran yang diajarkan di sekolah.
 */
class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama_mapel',
        'kode_mapel',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Mata pelajaran memiliki banyak jadwal mengajar.
     */
    public function jadwalMengajar(): HasMany
    {
        return $this->hasMany(JadwalMengajar::class, 'mata_pelajaran_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Search by name or code
     */
    public function scopeSearch($query, ?string $keyword)
    {
        if (!$keyword) return $query;
        
        return $query->where(function($q) use ($keyword) {
            $q->where('nama_mapel', 'like', "%{$keyword}%")
              ->orWhere('kode_mapel', 'like', "%{$keyword}%");
        });
    }

    // =====================================================================
    // ----------------------- HELPER METHODS -----------------------
    // =====================================================================

    /**
     * Get display name with code
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->kode_mapel) {
            return "{$this->kode_mapel} - {$this->nama_mapel}";
        }
        return $this->nama_mapel;
    }
}
