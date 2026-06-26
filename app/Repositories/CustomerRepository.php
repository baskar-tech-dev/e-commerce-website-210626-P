<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\CustomerProfile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Get paginated customers with filters.
     */
    public function all(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['customerProfile']);

        // Filter by active status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $isActive = $filters['status'] === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by registration date
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        // Search name, email, phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('addresses', function ($sub) use ($search) {
                      $sub->where('phone', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        switch ($sortBy) {
            case 'total_spent':
                $query->orderBy(
                    CustomerProfile::select('total_spent')
                        ->whereColumn('customer_profiles.user_id', 'users.id')
                        ->limit(1),
                    'desc'
                );
                break;
            case 'total_orders':
                $query->orderBy(
                    CustomerProfile::select('total_orders')
                        ->whereColumn('customer_profiles.user_id', 'users.id')
                        ->limit(1),
                    'desc'
                );
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * Find user by ID.
     */
    public function find(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find user with customer profile and addresses.
     */
    public function findWithProfileAndAddresses(int $id): ?User
    {
        return User::with(['customerProfile', 'addresses'])->find($id);
    }

    /**
     * Update customer profile data.
     */
    public function updateProfile(int $id, array $data): ?User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::find($id);
            if (!$user) {
                return null;
            }

            // Update user details
            $user->update(array_filter([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'avatar' => $data['avatar'] ?? null,
                'is_active' => isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : null,
            ], fn($v) => $v !== null));

            // Update profile details
            $profileData = array_filter([
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'notes' => $data['notes'] ?? null,
                'email_subscribed' => isset($data['email_subscribed']) ? filter_var($data['email_subscribed'], FILTER_VALIDATE_BOOLEAN) : null,
                'sms_subscribed' => isset($data['sms_subscribed']) ? filter_var($data['sms_subscribed'], FILTER_VALIDATE_BOOLEAN) : null,
            ], fn($v) => $v !== null);

            if (!empty($profileData)) {
                $user->customerProfile()->updateOrCreate([], $profileData);
            }

            return $user->load(['customerProfile', 'addresses']);
        });
    }

    /**
     * Update customer status and admin notes.
     */
    public function updateStatusAndNotes(int $id, bool $isActive, ?string $notes): ?User
    {
        return DB::transaction(function () use ($id, $isActive, $notes) {
            $user = User::find($id);
            if (!$user) {
                return null;
            }

            $user->update(['is_active' => $isActive]);

            $user->customerProfile()->updateOrCreate([], [
                'notes' => $notes,
            ]);

            return $user->load(['customerProfile', 'addresses']);
        });
    }
}
