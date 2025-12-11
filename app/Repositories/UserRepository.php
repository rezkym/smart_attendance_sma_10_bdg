<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model
    ) {}

    public function getAllUsers(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function getAllUsersWithRoles(): Collection
    {
        return $this->model->newQuery()
            ->with('roles')
            ->get();
    }

    public function findById(int $userId): ?User
    {
        return $this->model->newQuery()->find($userId);
    }

    public function findByIdWithRoles(int $userId): ?User
    {
        return $this->model->newQuery()
            ->with('roles')
            ->find($userId);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()
            ->where('email', $email)
            ->first();
    }

    public function create(array $data): User
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function syncRoles(User $user, array $roleNames): User
    {
        $user->syncRoles($roleNames);

        return $user->fresh()->load('roles');
    }

    public function getTotalUsersCount(): int
    {
        return $this->model->newQuery()->count();
    }

    public function getDataTableQuery(): Builder
    {
        return $this->model->newQuery()
            ->with('roles');
    }
}
