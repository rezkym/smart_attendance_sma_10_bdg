<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users
     *
     * @return Collection<int, User>
     */
    public function getAllUsers(): Collection;

    /**
     * Get all users with roles eager loaded
     *
     * @return Collection<int, User>
     */
    public function getAllUsersWithRoles(): Collection;

    /**
     * Find user by ID
     */
    public function findById(int $userId): ?User;

    /**
     * Find user by ID with roles eager loaded
     */
    public function findByIdWithRoles(int $userId): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user
     *
     * @param array{name: string, email: string, password: string} $data
     */
    public function create(array $data): User;

    /**
     * Update user
     *
     * @param array{name?: string, email?: string, password?: string} $data
     */
    public function update(User $user, array $data): User;

    /**
     * Delete user
     */
    public function delete(User $user): bool;

    /**
     * Sync user roles
     *
     * @param array<int, string> $roleNames
     */
    public function syncRoles(User $user, array $roleNames): User;

    /**
     * Get total users count
     */
    public function getTotalUsersCount(): int;

    /**
     * Get query builder for DataTables
     */
    public function getDataTableQuery(): Builder;
}
