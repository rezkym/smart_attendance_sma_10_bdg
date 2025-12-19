<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CardStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $card_uid
 * @property int|null $user_id
 * @property CardStatus $status
 * @property \Illuminate\Support\Carbon $issued_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 */
class RfidCard extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "RFID Card {$eventName}")
            ->useLogName('rfid-card');
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'card_uid',
        'user_id',
        'status',
        'issued_at',
        'expires_at',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CardStatus::class,
            'issued_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    /**
     * Get the user that owns this card.
     *
     * @return BelongsTo<User, RfidCard>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active cards.
     *
     * @param Builder<RfidCard> $query
     * @return Builder<RfidCard>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CardStatus::ACTIVE);
    }

    /**
     * Scope to filter by status.
     *
     * @param Builder<RfidCard> $query
     * @return Builder<RfidCard>
     */
    public function scopeByStatus(Builder $query, CardStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get only assigned cards (has user).
     *
     * @param Builder<RfidCard> $query
     * @return Builder<RfidCard>
     */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope to get only unassigned cards (no user).
     *
     * @param Builder<RfidCard> $query
     * @return Builder<RfidCard>
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Check if the card is expired based on expires_at date.
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the card can be used for attendance.
     */
    public function isUsable(): bool
    {
        return $this->status->isUsable() && !$this->isExpired();
    }
}
