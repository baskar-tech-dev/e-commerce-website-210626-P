<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\InstagramReel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontInstagramReelController extends Controller
{
    /**
     * Display a listing of active reels/videos for the storefront.
     */
    public function index(): JsonResponse
    {
        try {
            $reels = InstagramReel::where('is_active', true)
                                  ->orderBy('sort_order', 'asc')
                                  ->orderBy('id', 'desc')
                                  ->get();

            return response()->json([
                'success' => true,
                'data' => $reels,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load social videos',
            ], 500);
        }
    }
}
