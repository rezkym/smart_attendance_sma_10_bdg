<?php

declare(strict_types=1);

namespace App\Enums;

enum CardStatus: int
{
    case ACTIVE = 1;
    case LOST = 2;
    case BLOCKED = 3;
    case EXPIRED = 4;

    /**
     * Get the label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::LOST => 'Hilang',
            self::BLOCKED => 'Diblokir',
            self::EXPIRED => 'Kadaluarsa',
        };
    }

    /**
     * Get the label in English.
     */
    public function labelEnglish(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::LOST => 'Lost',
            self::BLOCKED => 'Blocked',
            self::EXPIRED => 'Expired',
        };
    }

    /**
     * Get CSS badge color for styling.
     * Returns just the color name for use with bg-label-X or bg-X classes.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::LOST => 'warning',
            self::BLOCKED => 'danger',
            self::EXPIRED => 'secondary',
        };
    }

    /**
     * Check if the card can be used for attendance.
     */
    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
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
}
