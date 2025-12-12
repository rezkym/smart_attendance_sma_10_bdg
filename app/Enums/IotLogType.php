<?php

declare(strict_types=1);

namespace App\Enums;

enum IotLogType: string
{
    case REQUEST = 'request';
    case RESPONSE = 'response';
    case ERROR = 'error';

    /**
     * Get the label for display purposes.
     */
    public function label(): string
    {
        return match ($this) {
            self::REQUEST => 'Request',
            self::RESPONSE => 'Response',
            self::ERROR => 'Error',
        };
    }

    /**
     * Get the badge color for UI display.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::REQUEST => 'info',
            self::RESPONSE => 'success',
            self::ERROR => 'danger',
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
     * Get all types as array for dropdown options.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::REQUEST->value => self::REQUEST->label(),
            self::RESPONSE->value => self::RESPONSE->label(),
            self::ERROR->value => self::ERROR->label(),
        ];
    }
}
