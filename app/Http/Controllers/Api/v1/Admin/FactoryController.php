<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class FactoryController extends Controller
{
    /**
     * Display a listing of factories.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Factory::orderBy('name', 'asc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%");
                });
            }

            if ($request->boolean('active_only')) {
                $query->where('is_active', true);
            }

            $perPage = (int) $request->input('per_page', 25);
            $factories = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $factories->items(),
                'meta' => [
                    'current_page' => $factories->currentPage(),
                    'last_page' => $factories->lastPage(),
                    'per_page' => $factories->perPage(),
                    'total' => $factories->total(),
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve factories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return simple active list for dropdowns.
     */
    public function activeList(): JsonResponse
    {
        $factories = Factory::where('is_active', true)
            ->select('id', 'name', 'code', 'city', 'state', 'phone')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $factories
        ]);
    }

    /**
     * Store a newly created factory.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:factories,code',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:80',
            'state' => 'nullable|string|max:80',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $factory = Factory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Factory created successfully.',
                'data' => $factory
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create factory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified factory.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $factory = Factory::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $factory
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factory not found.'
            ], 404);
        }
    }

    /**
     * Update the specified factory.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $factory = Factory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:50|unique:factories,code,' . $id,
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:80',
            'state' => 'nullable|string|max:80',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $factory->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Factory updated successfully.',
                'data' => $factory
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update factory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the specified factory.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $factory = Factory::findOrFail($id);
            $factory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Factory deleted successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete factory: ' . $e->getMessage()
            ], 500);
        }
    }
}
