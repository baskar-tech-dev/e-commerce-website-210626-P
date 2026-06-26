<?php

namespace App\Services;

use App\Repositories\CustomerRepositoryInterface;
use App\Models\User;
use App\Models\Address;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get paginated customers list with filters.
     */
    public function getPaginatedCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->all($filters, $perPage);
    }

    /**
     * Get customer by ID with profile and addresses.
     */
    public function getCustomerById(int $id): ?User
    {
        return $this->customerRepository->findWithProfileAndAddresses($id);
    }

    /**
     * Update customer profile and details.
     */
    public function updateCustomer(int $id, array $data): ?User
    {
        return $this->customerRepository->updateProfile($id, $data);
    }

    /**
     * Update customer status (is_active) and admin notes.
     */
    public function updateCustomerStatusAndNotes(int $id, bool $isActive, ?string $notes): ?User
    {
        return $this->customerRepository->updateStatusAndNotes($id, $isActive, $notes);
    }

    /**
     * Add address for customer with limit of 5.
     */
    public function addAddress(int $userId, array $data): Address
    {
        return DB::transaction(function () use ($userId, $data) {
            // Count existing active addresses
            $count = Address::where('user_id', $userId)->count();
            if ($count >= 5) {
                throw ValidationException::withMessages([
                    'address' => ['Customers are limited to a maximum of 5 addresses.']
                ]);
            }

            $isDefaultShipping = filter_var($data['is_default_shipping'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $isDefaultBilling = filter_var($data['is_default_billing'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($isDefaultShipping) {
                Address::where('user_id', $userId)->update(['is_default_shipping' => false]);
            }

            if ($isDefaultBilling) {
                Address::where('user_id', $userId)->update(['is_default_billing' => false]);
            }

            $data['user_id'] = $userId;
            $data['is_default_shipping'] = $isDefaultShipping;
            $data['is_default_billing'] = $isDefaultBilling;

            return Address::create($data);
        });
    }

    /**
     * Update address for customer.
     */
    public function updateAddress(int $userId, int $addressId, array $data): Address
    {
        return DB::transaction(function () use ($userId, $addressId, $data) {
            $address = Address::where('user_id', $userId)->findOrFail($addressId);

            $isDefaultShipping = isset($data['is_default_shipping']) ? filter_var($data['is_default_shipping'], FILTER_VALIDATE_BOOLEAN) : null;
            $isDefaultBilling = isset($data['is_default_billing']) ? filter_var($data['is_default_billing'], FILTER_VALIDATE_BOOLEAN) : null;

            if ($isDefaultShipping === true) {
                Address::where('user_id', $userId)->where('id', '!=', $addressId)->update(['is_default_shipping' => false]);
            }

            if ($isDefaultBilling === true) {
                Address::where('user_id', $userId)->where('id', '!=', $addressId)->update(['is_default_billing' => false]);
            }

            $address->update(array_filter([
                'label' => $data['label'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address_line_1' => $data['address_line_1'] ?? null,
                'address_line_2' => $data['address_line_2'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'is_default_shipping' => $isDefaultShipping,
                'is_default_billing' => $isDefaultBilling,
            ], fn($v) => $v !== null));

            return $address->refresh();
        });
    }

    /**
     * Delete customer address (soft delete).
     */
    public function deleteAddress(int $userId, int $addressId): bool
    {
        $address = Address::where('user_id', $userId)->findOrFail($addressId);
        return $address->delete();
    }
}
