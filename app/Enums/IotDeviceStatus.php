<?php

declare(strict_types=1);

namespace App\Enums;

enum IotDeviceStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case MAINTENANCE = 'maintenance';

    /**
     * Get the label for display purposes.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Non-Aktif',
            self::MAINTENANCE => 'Maintenance',
        };
    }

    /**
     * Get the badge color for UI display.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::MAINTENANCE => 'warning',
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

    /**
     * Get all statuses as array for dropdown options.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::ACTIVE->value => self::ACTIVE->label(),
            self::INACTIVE->value => self::INACTIVE->label(),
            self::MAINTENANCE->value => self::MAINTENANCE->label(),
        ];
    }
}
