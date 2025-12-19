<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\CardStatus;
use App\Models\RfidCard;
use App\Repositories\Contracts\RfidCardRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RfidCardRepository implements RfidCardRepositoryInterface
{
    public function __construct(
        protected RfidCard $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->newQuery()
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById(int $cardId): ?RfidCard
    {
        return $this->model->newQuery()
            ->with('user')
            ->find($cardId);
    }

    public function findByCardUid(string $cardUid): ?RfidCard
    {
        return $this->model->newQuery()
            ->with('user')
            ->where('card_uid', $cardUid)
            ->first();
    }

    public function getByUserId(int $userId): Collection
    {
        return $this->model->newQuery()
            ->with('user')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getByStatus(CardStatus $status): Collection
    {
        return $this->model->newQuery()
            ->with('user')
            ->byStatus($status)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): RfidCard
    {
        $card = $this->model->newQuery()->create($data);

        return $card->load('user');
    }

    public function update(RfidCard $card, array $data): RfidCard
    {
        $card->update($data);

        return $card->fresh(['user']);
    }

    public function delete(RfidCard $card): bool
    {
        return (bool) $card->delete();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('user')
            ->orderByDesc('created_at');
    }

    public function getTotalCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getActiveCount(): int
    {
        return $this->model->newQuery()->active()->count();
    }

    public function getAssignedCount(): int
    {
        return $this->model->newQuery()->assigned()->count();
    }

    public function existsByCardUid(string $cardUid, ?int $excludeId = null): bool
    {
        $query = $this->model->newQuery()
            ->where('card_uid', $cardUid);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function findActiveByCardUid(string $cardUid): ?RfidCard
    {
        return $this->model->newQuery()
            ->with('user')
            ->where('card_uid', $cardUid)
            ->active()
            ->whereNotNull('user_id')
            ->first();
    }
}
