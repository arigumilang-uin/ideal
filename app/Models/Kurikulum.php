<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Kurikulum
 * 
 * Master data kurikulum yang digunakan di sekolah.
 * Contoh: Kurikulum 2013, Kurikulum Merdeka, Kurikulum Merdeka SMK
 */
class Kurikulum extends Model
{
    use HasFactory;

    protected $table = 'kurikulum';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'tahun_berlaku',
        'is_active',
    ];

    protected $casts = [
        'tahun_berlaku' => 'integer',
        'is_active' => 'boolean',
    ];

    // =====================================================================
    // ----------------------- RELATIONSHIPS -----------------------
    // =====================================================================

    /**
     * Mata pelajaran yang termasuk dalam kurikulum ini
     */
    public function mataPelajaran(): HasMany
    {
        return $this->hasMany(MataPelajaran::class, 'kurikulum_id');
    }

    /**
     * Assignment tingkat-kurikulum per periode
     */
    public function tingkatKurikulum(): HasMany
    {
        return $this->hasMany(TingkatKurikulum::class, 'kurikulum_id');
    }

    // =====================================================================
    // ----------------------- QUERY SCOPES -----------------------
    // =====================================================================

    /**
     * Scope: Only active curricula
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
            $q->where('nama', 'like', "%{$keyword}%")
              ->orWhere('kode', 'like', "%{$keyword}%");
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
        return "{$this->kode} - {$this->nama}";
    }

    /**
     * Get count of active mata pelajaran
     */
    public function getMapelCountAttribute(): int
    {
        return $this->mataPelajaran()->active()->count();
    }
}
