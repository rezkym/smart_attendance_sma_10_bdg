<?php

declare(strict_types=1);

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case EXCUSED = 'excused';
    case SICK = 'sick';
    case ABSENT = 'absent';

    /**
     * Get the label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Hadir',
            self::LATE => 'Terlambat',
            self::EXCUSED => 'Izin',
            self::SICK => 'Sakit',
            self::ABSENT => 'Alpha',
        };
    }

    /**
     * Get the badge color for UI display.
     */
    public function color(): string
    {
        return match ($this) {
            self::PRESENT => 'success',
            self::LATE => 'warning',
            self::EXCUSED => 'info',
            self::SICK => 'primary',
            self::ABSENT => 'danger',
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
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }
        return $result;
    }

    /**
     * Get all statuses with colors for UI.
     *
     * @return array<string, array{label: string, color: string}>
     */
    public static function toArrayWithColors(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = [
                'label' => $case->label(),
                'color' => $case->color(),
            ];
        }
        return $result;
    }
}
