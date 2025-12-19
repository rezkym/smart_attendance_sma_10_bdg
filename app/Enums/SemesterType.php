<?php

declare(strict_types=1);

namespace App\Enums;

enum SemesterType: int
{
    case ODD = 1;
    case EVEN = 2;

    /**
     * Get the label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::ODD => 'Ganjil',
            self::EVEN => 'Genap',
        };
    }

    /**
     * Get the label in English.
     */
    public function labelEnglish(): string
    {
        return match ($this) {
            self::ODD => 'Odd',
            self::EVEN => 'Even',
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
     * Get all types as array for dropdown options.
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
