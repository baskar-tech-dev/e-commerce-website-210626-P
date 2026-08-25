<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    /**
     * Get details for the customer storefront profile.
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Load addresses and previous orders with full item details & courier
        $addresses = Address::where('user_id', $user->id)->get();
        $orders = Order::with(['items.product.images', 'items.variant', 'courier', 'payments'])->where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $reviews = ProductReview::with(['product' => function ($q) {
            $q->select('id', 'uuid', 'name', 'selling_price');
        }])
        ->where('user_id', $user->id)
        ->latest()
        ->get();

        $avatarUrl = $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset($user->avatar)) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $avatarUrl,
                'addresses' => $addresses,
                'orders' => $orders,
                'reviews' => $reviews,
            ],
        ]);
    }

    /**
     * Update customer profile details.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_avatar' => 'nullable|boolean',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($validated['current_password']) || !empty($validated['new_password'])) {
            if (empty($validated['current_password']) || !Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your current password is incorrect.',
                    'errors' => ['current_password' => ['Your current password is incorrect.']],
                ], 422);
            }

            if (empty($validated['new_password'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a new password.',
                    'errors' => ['new_password' => ['Please enter a new password.']],
                ], 422);
            }

            $user->password = Hash::make($validated['new_password']);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = $file->store('avatars', 'public');
            $user->avatar = '/storage/' . $path;
        } elseif ($request->boolean('remove_avatar')) {
            $user->avatar = null;
        }

        $user->first_name = trim($validated['first_name']);
        $user->last_name = isset($validated['last_name']) ? trim($validated['last_name']) : '';
        $user->name = trim("{$user->first_name} {$user->last_name}");
        $user->email = strtolower(trim($validated['email']));
        $user->phone = isset($validated['phone']) ? trim($validated['phone']) : null;
        $user->save();

        $avatarUrl = $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset($user->avatar)) : null;

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $avatarUrl,
            ],
        ]);
    }

    /**
     * Add or update an address in the customer's address book.
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

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

        // If setting this address as default, unset other addresses for this user
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
     * Delete an address from customer address book.
     */
    public function deleteAddress(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $address = Address::where('user_id', $user->id)->findOrFail($id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * Get orders for the authenticated customer.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $orders = Order::with(['items.product.images', 'items.variant.product.images', 'items.variant.images'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get specific order detail for authenticated customer.
     */
    public function getOrderDetails(string $orderId, Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $order = Order::with(['items.product.images', 'items.variant.product.images', 'items.variant.images'])
            ->where('user_id', $user->id)
            ->where(function ($q) use ($orderId) {
                $q->where('id', $orderId)
                  ->orWhere('order_number', $orderId)
                  ->orWhere('uuid', $orderId);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}
