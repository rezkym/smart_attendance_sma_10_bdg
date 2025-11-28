<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function datatableQuery(array $filters = []): Builder
    {
        return $this->userRepository->datatableQuery($filters);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $roles = Arr::pull($data, 'roles', []);

            $user = $this->userRepository->create($data);

            if (! empty($roles)) {
                $user->syncRoles($roles);
            }

            return $user->load('roles');
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $roles = Arr::pull($data, 'roles', null);

            if (array_key_exists('password', $data) && $data['password'] === null) {
                unset($data['password']);
            }

            $updatedUser = $this->userRepository->update($user, $data);

            if (is_array($roles)) {
                $updatedUser->syncRoles($roles);
            }

            return $updatedUser->load('roles');
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->syncRoles([]);
            $this->userRepository->delete($user);
        });
    }

    public function syncRoles(User $user, array $roles): User
    {
        return DB::transaction(function () use ($user, $roles) {
            $user->syncRoles($roles);

            return $user->load('roles');
        });
    }
}
