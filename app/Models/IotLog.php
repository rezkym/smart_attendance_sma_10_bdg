<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IotLogType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $iot_device_id
 * @property IotLogType $log_type
 * @property string $endpoint
 * @property string $method
 * @property array|null $request_payload
 * @property array|null $response_payload
 * @property int|null $response_code
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int|null $duration_ms
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read IotDevice $device
 */
class IotLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'iot_device_id',
        'log_type',
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'response_code',
        'ip_address',
        'user_agent',
        'duration_ms',
        'error_message',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'log_type' => IotLogType::class,
            'request_payload' => 'array',
            'response_payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the device that owns this log.
     *
     * @return BelongsTo<IotDevice, IotLog>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
    }

    /**
     * Scope to filter by device.
     *
     * @param Builder<IotLog> $query
     * @return Builder<IotLog>
     */
    public function scopeByDevice(Builder $query, int $deviceId): Builder
    {
        return $query->where('iot_device_id', $deviceId);
    }

    /**
     * Scope to filter by log type.
     *
     * @param Builder<IotLog> $query
     * @return Builder<IotLog>
     */
    public function scopeByType(Builder $query, IotLogType $type): Builder
    {
        return $query->where('log_type', $type);
    }

    /**
     * Scope to filter by date range.
     *
     * @param Builder<IotLog> $query
     * @return Builder<IotLog>
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by response code.
     *
     * @param Builder<IotLog> $query
     * @return Builder<IotLog>
     */
    public function scopeByResponseCode(Builder $query, int $responseCode): Builder
    {
        return $query->where('response_code', $responseCode);
    }

    /**
     * Check if this log represents an error.
     */
    public function isError(): bool
    {
        return $this->log_type === IotLogType::ERROR || ($this->response_code !== null && $this->response_code >= 400);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_ms === null) {
            return '-';
        }

        if ($this->duration_ms < 1000) {
            return $this->duration_ms . 'ms';
        }

        return round($this->duration_ms / 1000, 2) . 's';
    }
}
