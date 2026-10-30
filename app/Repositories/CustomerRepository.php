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
        $query = User::with(['customerProfile'])
            ->withCount([
                'orders as total_orders_count',
                'orders as paid_orders_count' => function ($q) {
                    $q->where('payment_status', 'paid');
                }
            ])
            ->withSum([
                'orders as paid_spent' => function ($q) {
                    $q->where('payment_status', 'paid')->where('status', '!=', 'cancelled');
                }
            ], 'grand_total');

        // Default or explicit: filter to only customers who actually purchased
        // 'purchased' (default): only customers with placed orders
        // 'paid': only customers with paid orders
        // 'all': all registered user accounts
        $purchaseFilter = $filters['purchase_filter'] ?? 'purchased';
        if ($purchaseFilter === 'purchased') {
            $query->whereHas('orders');
        } elseif ($purchaseFilter === 'paid') {
            $query->whereHas('orders', function ($q) {
                $q->where('payment_status', 'paid');
            });
        }

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
        $sortBy = $filters['sort_by'] ?? 'total_spent';
        switch ($sortBy) {
            case 'total_spent':
            case 'paid_spent':
                $query->orderBy('paid_spent', 'desc');
                break;
            case 'paid_orders':
            case 'total_orders':
                $query->orderBy('paid_orders_count', 'desc');
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
     * Find user with customer profile, addresses, and order history.
     */
    public function findWithProfileAndAddresses(int $id): ?User
    {
        return User::with([
            'customerProfile',
            'addresses',
            'orders' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])->find($id);
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

    /**
     * Get aggregate customer summary statistics.
     */
    public function getCustomerSummary(): array
    {
        $totalCustomers = User::count();
        $purchasedCustomers = User::whereHas('orders')->count();
        $paidCustomers = User::whereHas('orders', function ($q) {
            $q->where('payment_status', 'paid');
        })->count();

        $paidRevenue = (float) \App\Models\Order::where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        $totalRevenue = (float) \App\Models\Order::where('status', '!=', 'cancelled')
            ->sum('grand_total');

        return [
            'total_customers' => $totalCustomers,
            'purchased_customers' => $purchasedCustomers,
            'paid_customers' => $paidCustomers,
            'paid_revenue' => round($paidRevenue, 2),
            'total_revenue' => round($totalRevenue, 2),
        ];
    }
}
