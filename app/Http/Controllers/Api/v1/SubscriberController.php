<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    /**
     * Storefront: Customer joins WhatsApp club or newsletter.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'source' => 'nullable|string|max:50',
        ]);

        if (empty($validated['phone']) && empty($validated['email'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid WhatsApp number or email address.',
            ], 422);
        }

        $subscriber = Subscriber::firstOrCreate(
            [
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
            ],
            [
                'source' => $validated['source'] ?? 'whatsapp_club',
                'status' => 'active',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Welcome to the Maya Sree WhatsApp Club! Your ₹150 OFF discount code is MSF150.',
            'coupon_code' => 'MSF150',
            'data' => $subscriber,
        ]);
    }

    /**
     * Admin: List all subscribers / WhatsApp club members.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Subscriber::query()->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        $subscribers = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $subscribers->items(),
            'meta' => [
                'current_page' => $subscribers->currentPage(),
                'last_page' => $subscribers->lastPage(),
                'per_page' => $subscribers->perPage(),
                'total' => $subscribers->total(),
            ]
        ]);
    }
}
