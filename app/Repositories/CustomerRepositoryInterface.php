<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    /**
     * Get paginated customers with filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find user by ID.
     */
    public function find(int $id): ?User;

    /**
     * Find user with customer profile and addresses.
     */
    public function findWithProfileAndAddresses(int $id): ?User;

    /**
     * Update customer profile data.
     */
    public function updateProfile(int $id, array $data): ?User;

    /**
     * Update customer status and admin notes.
     */
    public function updateStatusAndNotes(int $id, bool $isActive, ?string $notes): ?User;
}
