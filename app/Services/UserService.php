<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Get all users with roles
     *
     * @return Collection<int, User>
     */
    public function getAllUsersWithRoles(): Collection
    {
        return $this->userRepository->getAllUsersWithRoles();
    }

    /**
     * Get user by ID with roles
     */
    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->findByIdWithRoles($userId);
    }

    /**
     * Create a new user with roles
     *
     * @param array{name: string, email: string, password: string} $userData
     * @param array<int, string> $roleNames
     */
    public function createUser(array $userData, array $roleNames = []): User
    {
        return DB::transaction(function () use ($userData, $roleNames) {
            // Password is hashed automatically via User model's password cast
            $user = $this->userRepository->create($userData);

            if (filled($roleNames)) {
                $this->userRepository->syncRoles($user, $roleNames);
            }

            return $user->fresh()->load('roles');
        });
    }

    /**
     * Update user and sync roles
     *
     * @param array{name?: string, email?: string, password?: string} $userData
     * @param array<int, string> $roleNames
     *
     * @throws \InvalidArgumentException
     */
    public function updateUser(int $userId, array $userData, array $roleNames = []): User
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new \InvalidArgumentException("User with ID {$userId} not found.");
        }

        return DB::transaction(function () use ($user, $userData, $roleNames) {
            // Only update password if provided and not empty
            if (blank($userData['password'] ?? null)) {
                unset($userData['password']);
            }

            if (filled($userData)) {
                $this->userRepository->update($user, $userData);
            }

            $this->userRepository->syncRoles($user, $roleNames);

            return $user->fresh()->load('roles');
        });
    }

    /**
     * Delete a user
     *
     * @throws \InvalidArgumentException
     */
    public function deleteUser(int $userId): bool
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            throw new \InvalidArgumentException("User with ID {$userId} not found.");
        }

        // Prevent deleting yourself
        if (Auth::id() === $user->id) {
            throw new \InvalidArgumentException('You cannot delete your own account.');
        }

        // Prevent deleting if user is the last admin
        if ($user->hasRole('admin')) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                throw new \InvalidArgumentException('Cannot delete the last admin user.');
            }
        }

        return $this->userRepository->delete($user);
    }

    /**
     * Get user statistics for dashboard cards
     *
     * @return array{total_users: int}
     */
    public function getUserStats(): array
    {
        return [
            'total_users' => $this->userRepository->getTotalUsersCount(),
        ];
    }

    /**
     * Get DataTables query builder for users
     */
    public function getDataTableQuery(): Builder
    {
        return $this->userRepository->getDataTableQuery();
    }
}
