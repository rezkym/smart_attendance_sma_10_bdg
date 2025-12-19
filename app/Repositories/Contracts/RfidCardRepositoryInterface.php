<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\CardStatus;
use App\Models\RfidCard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface RfidCardRepositoryInterface
{
    /**
     * Get all RFID cards with user relationship.
     *
     * @return Collection<int, RfidCard>
     */
    public function getAll(): Collection;

    /**
     * Find RFID card by ID with relationships.
     */
    public function findById(int $cardId): ?RfidCard;

    /**
     * Find RFID card by card UID.
     */
    public function findByCardUid(string $cardUid): ?RfidCard;

    /**
     * Get all cards assigned to a specific user.
     *
     * @return Collection<int, RfidCard>
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get all cards with a specific status.
     *
     * @return Collection<int, RfidCard>
     */
    public function getByStatus(CardStatus $status): Collection;

    /**
     * Create a new RFID card.
     *
     * @param array{card_uid: string, user_id?: int|null, status: int, issued_at: string, expires_at?: string|null, notes?: string|null} $data
     */
    public function create(array $data): RfidCard;

    /**
     * Update an RFID card.
     *
     * @param array{card_uid?: string, user_id?: int|null, status?: int, issued_at?: string, expires_at?: string|null, notes?: string|null} $data
     */
    public function update(RfidCard $card, array $data): RfidCard;

    /**
     * Delete an RFID card.
     */
    public function delete(RfidCard $card): bool;

    /**
     * Get query builder for DataTables.
     *
     * @return Builder<RfidCard>
     */
    public function getDataTableQuery(): Builder;

    /**
     * Get total count of all cards.
     */
    public function getTotalCount(): int;

    /**
     * Get count of active cards.
     */
    public function getActiveCount(): int;

    /**
     * Get count of assigned cards.
     */
    public function getAssignedCount(): int;

    /**
     * Check if card UID already exists.
     */
    public function existsByCardUid(string $cardUid, ?int $excludeId = null): bool;

    /**
     * Find active card by UID for attendance lookup.
     */
    public function findActiveByCardUid(string $cardUid): ?RfidCard;
}
