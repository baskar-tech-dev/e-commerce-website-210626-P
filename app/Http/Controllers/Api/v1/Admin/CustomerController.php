<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerDetailResource;
use App\Http\Resources\AddressResource;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of customers with stats.
     */
    public function index(Request $request)
    {
        $filters = $request->input('filter', []);
        if ($request->has('search')) {
            $filters['search'] = $request->input('search');
        }
        if ($request->has('sort_by')) {
            $filters['sort_by'] = $request->input('sort_by');
        }

        $perPage = $request->input('per_page', 15);
        $customers = $this->customerService->getPaginatedCustomers($filters, $perPage);

        return CustomerResource::collection($customers);
    }

    /**
     * Display the specified customer details.
     */
    public function show(int $id)
    {
        $customer = $this->customerService->getCustomerById($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        return new CustomerDetailResource($customer);
    }

    /**
     * Update the customer profile (including status and notes).
     */
    public function update(Request $request, int $id)
    {
        $customer = $this->customerService->getCustomerById($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
            'is_active' => 'nullable|boolean',
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'gender' => 'nullable|string|in:Male,Female,Other,Prefer not to say',
            'notes' => 'nullable|string',
            'email_subscribed' => 'nullable|boolean',
            'sms_subscribed' => 'nullable|boolean',
        ]);

        $updated = $this->customerService->updateCustomer($id, $validated);

        return new CustomerDetailResource($updated);
    }

    /**
     * Add a new address to a customer profile.
     */
    public function storeAddress(Request $request, int $id)
    {
        $customer = $this->customerService->getCustomerById($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'landmark' => 'nullable|string|max:200',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        $address = $this->customerService->addAddress($id, $validated);

        return response()->json(new AddressResource($address), Response::HTTP_CREATED);
    }

    /**
     * Update customer address details.
     */
    public function updateAddress(Request $request, int $id, int $addressId)
    {
        $customer = $this->customerService->getCustomerById($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:2',
            'landmark' => 'nullable|string|max:200',
            'is_default_shipping' => 'nullable|boolean',
            'is_default_billing' => 'nullable|boolean',
        ]);

        $address = $this->customerService->updateAddress($id, $addressId, $validated);

        return response()->json(new AddressResource($address));
    }

    /**
     * Remove customer address (soft delete).
     */
    public function destroyAddress(int $id, int $addressId)
    {
        $customer = $this->customerService->getCustomerById($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $this->customerService->deleteAddress($id, $addressId);

        return response()->json(['message' => 'Address removed successfully']);
    }
}
