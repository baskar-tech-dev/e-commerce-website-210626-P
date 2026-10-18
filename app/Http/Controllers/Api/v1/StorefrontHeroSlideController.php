<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\JsonResponse;

class StorefrontHeroSlideController extends Controller
{
    /**
     * Display a listing of active hero slides for the storefront homepage.
     */
    public function index(): JsonResponse
    {
        try {
            $slides = HeroSlide::active()->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('StorefrontHeroSlideController fallback: ' . $e->getMessage());
            $slides = [];
        }

        return response()->json([
            'success' => true,
            'data' => $slides,
        ]);
    }
}
