<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    /**
     * Get details for the customer storefront profile.
     */
    public function getProfile(): JsonResponse
    {
        $user = auth()->user() ?? User::first();

        // Load addresses and previous orders
        $addresses = Address::where('user_id', $user->id)->get();
        $orders = Order::with('items')->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'addresses' => $addresses,
                'orders' => $orders,
            ],
        ]);
    }

    /**
     * Add or update an address in the address book.
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $user = auth()->user() ?? User::first();

        $validated = $request->validate([
            'id' => 'nullable|exists:addresses,id',
            'label' => 'required|string|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'is_default_shipping' => 'nullable|boolean',
        ]);

        $isDefault = $validated['is_default_shipping'] ?? false;

        // If setting this as default, unset others first
        if ($isDefault) {
            Address::where('user_id', $user->id)->update(['is_default_shipping' => false]);
        }

        $addressData = [
            'user_id' => $user->id,
            'label' => $validated['label'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'is_default_shipping' => $isDefault,
        ];

        if (!empty($validated['id'])) {
            $address = Address::where('user_id', $user->id)->findOrFail($validated['id']);
            $address->update($addressData);
            $message = 'Address updated successfully';
        } else {
            $address = Address::create($addressData);
            $message = 'Address created successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $address,
        ]);
    }

    /**
     * Delete address.
     */
    public function deleteAddress(int $id): JsonResponse
    {
        $user = auth()->user() ?? User::first();
        $address = Address::where('user_id', $user->id)->findOrFail($id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }
}
