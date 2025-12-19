<?php

declare(strict_types=1);

namespace App\Enums;

enum EnrollmentStatus: int
{
    case ACTIVE = 1;
    case GRADUATED = 2;
    case TRANSFERRED = 3;
    case DROPPED = 4;

    /**
     * Get the label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::GRADUATED => 'Lulus',
            self::TRANSFERRED => 'Pindah',
            self::DROPPED => 'Keluar',
        };
    }

    /**
     * Get the label in English.
     */
    public function labelEnglish(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::GRADUATED => 'Graduated',
            self::TRANSFERRED => 'Transferred',
            self::DROPPED => 'Dropped',
        };
    }

    /**
     * Get all values as array for validation.
     *
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all statuses as array for dropdown options.
     *
     * @return array<int, string>
     */
    public static function toArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }
        return $result;
    }

    /**
     * Get badge color for UI display.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::GRADUATED => 'primary',
            self::TRANSFERRED => 'warning',
            self::DROPPED => 'danger',
        };
    }
}
