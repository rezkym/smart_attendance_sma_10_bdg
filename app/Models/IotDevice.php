<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IotDeviceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $device_code
 * @property string $api_key
 * @property string|null $description
 * @property string|null $location
 * @property int|null $classroom_id
 * @property IotDeviceStatus $status
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property string|null $ip_address
 * @property string|null $firmware_version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Classroom|null $classroom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, IotLog> $logs
 */
class IotDevice extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Get the activity log options for this model.
     * Excludes api_key for security.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'device_code', 'description', 'location', 'classroom_id', 'status', 'ip_address', 'firmware_version'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "IoT Device {$eventName}")
            ->useLogName('iot-device');
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'device_code',
        'api_key',
        'description',
        'location',
        'classroom_id',
        'status',
        'last_seen_at',
        'ip_address',
        'firmware_version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => IotDeviceStatus::class,
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the classroom this device is assigned to.
     *
     * @return BelongsTo<Classroom, IotDevice>
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the logs for this device.
     *
     * @return HasMany<IotLog>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(IotLog::class);
    }

    /**
     * Scope to get only active devices.
     *
     * @param Builder<IotDevice> $query
     * @return Builder<IotDevice>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', IotDeviceStatus::ACTIVE);
    }

    /**
     * Scope to filter by status.
     *
     * @param Builder<IotDevice> $query
     * @return Builder<IotDevice>
     */
    public function scopeByStatus(Builder $query, IotDeviceStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Check if device is active.
     */
    public function isActive(): bool
    {
        return $this->status === IotDeviceStatus::ACTIVE;
    }

    /**
     * Get masked API key for display (show only first 8 and last 4 characters).
     */
    public function getMaskedApiKeyAttribute(): string
    {
        if (strlen($this->api_key) <= 12) {
            return str_repeat('*', strlen($this->api_key));
        }

        return substr($this->api_key, 0, 8) . str_repeat('*', 48) . substr($this->api_key, -4);
    }
}
