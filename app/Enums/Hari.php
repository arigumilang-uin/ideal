<?php

namespace App\Enums;

/**
 * Hari Enum
 * 
 * Hari-hari sekolah (Senin - Sabtu)
 */
enum Hari: string
{
    case Senin = 'Senin';
    case Selasa = 'Selasa';
    case Rabu = 'Rabu';
    case Kamis = 'Kamis';
    case Jumat = 'Jumat';
    case Sabtu = 'Sabtu';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Get short label (3 chars)
     */
    public function shortLabel(): string
    {
        return match($this) {
            self::Senin => 'Sen',
            self::Selasa => 'Sel',
            self::Rabu => 'Rab',
            self::Kamis => 'Kam',
            self::Jumat => 'Jum',
            self::Sabtu => 'Sab',
        };
    }

    /**
     * Get day number (1 = Senin, 6 = Sabtu)
     */
    public function dayNumber(): int
    {
        return match($this) {
            self::Senin => 1,
            self::Selasa => 2,
            self::Rabu => 3,
            self::Kamis => 4,
            self::Jumat => 5,
            self::Sabtu => 6,
        };
    }

    /**
     * Get Hari from PHP date('N') format (1 = Monday)
     */
    public static function fromDayNumber(int $dayNumber): ?self
    {
        return match($dayNumber) {
            1 => self::Senin,
            2 => self::Selasa,
            3 => self::Rabu,
            4 => self::Kamis,
            5 => self::Jumat,
            6 => self::Sabtu,
            default => null,
        };
    }

    /**
     * Get today's Hari
     */
    public static function today(): ?self
    {
        return self::fromDayNumber((int) date('N'));
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
