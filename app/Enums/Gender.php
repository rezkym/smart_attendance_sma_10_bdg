<?php

declare(strict_types=1);

namespace App\Enums;

enum Gender: string
{
    case MALE = 'L';
    case FEMALE = 'P';

    /**
     * Get the label for display purposes.
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }

    /**
     * Get all values as array for validation.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
