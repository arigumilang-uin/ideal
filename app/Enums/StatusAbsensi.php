<?php

namespace App\Enums;

/**
 * Status Absensi Enum
 * 
 * Mendefinisikan status kehadiran siswa:
 * - Hadir: Siswa hadir di kelas
 * - Sakit: Siswa tidak hadir karena sakit
 * - Izin: Siswa tidak hadir dengan izin (ada surat/persetujuan)
 * - Alfa: Siswa tidak hadir tanpa keterangan (AKAN TRIGGER PELANGGARAN)
 */
enum StatusAbsensi: string
{
    case Hadir = 'Hadir';
    case Sakit = 'Sakit';
    case Izin = 'Izin';
    case Alfa = 'Alfa';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::Hadir => 'Hadir',
            self::Sakit => 'Sakit',
            self::Izin => 'Izin',
            self::Alfa => 'Alfa (Tanpa Keterangan)',
        };
    }

    /**
     * Get color for UI badge
     */
    public function color(): string
    {
        return match($this) {
            self::Hadir => 'green',
            self::Sakit => 'yellow',
            self::Izin => 'blue',
            self::Alfa => 'red',
        };
    }

    /**
     * Get Tailwind CSS classes for badge
     */
    public function badgeClasses(): string
    {
        return match($this) {
            self::Hadir => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            self::Sakit => 'bg-amber-100 text-amber-700 border-amber-200',
            self::Izin => 'bg-blue-100 text-blue-700 border-blue-200',
            self::Alfa => 'bg-rose-100 text-rose-700 border-rose-200',
        };
    }

    /**
     * Get icon for UI
     */
    public function icon(): string
    {
        return match($this) {
            self::Hadir => 'check-circle',
            self::Sakit => 'heart',
            self::Izin => 'document-text',
            self::Alfa => 'x-circle',
        };
    }

    /**
     * Check if this status triggers pelanggaran
     */
    public function triggersPelanggaran(): bool
    {
        return $this === self::Alfa;
    }

    /**
     * Get all values for dropdown/select
     */
    public static function forSelect(): array
    {
        return array_map(
            fn($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        );
    }
}
