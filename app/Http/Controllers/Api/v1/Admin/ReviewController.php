<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Services\ReviewImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class ReviewController extends Controller
{
    protected ReviewImageService $imageService;

    public function __construct(ReviewImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Admin listing of customer reviews with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ProductReview::with([
                'user:id,first_name,last_name,email',
                'product:id,name,slug,avg_rating,total_reviews',
                'order:id,order_number',
                'images'
            ]);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('rating')) {
                $query->where('rating', (int) $request->input('rating'));
            }

            if ($request->filled('product_id')) {
                $query->where('product_id', (int) $request->input('product_id'));
            }

            if ($request->has('is_verified_purchase')) {
                $query->where('is_verified_purchase', filter_var($request->input('is_verified_purchase'), FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('review', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('email', 'like', "%{$search}%")
                             ->orWhere('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('product', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            $perPage = (int) $request->input('per_page', 15);
            $reviews = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $reviews->items(),
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Admin\ReviewController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews',
            ], 500);
        }
    }

    /**
     * Show single review details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $review = ProductReview::with([
                'user:id,first_name,last_name,email,phone',
                'product:id,name,slug,uuid,avg_rating,total_reviews',
                'order:id,order_number,status,delivered_at',
                'images'
            ])->find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $review,
            ]);
        } catch (Exception $e) {
            Log::error('Admin\ReviewController@show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve review details',
            ], 500);
        }
    }

    /**
     * Approve or reject a review.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        try {
            $review = ProductReview::find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            $review->status = $validated['status'];
            $review->save();

            // Recalculate rating for associated product
            if ($review->product) {
                $review->product->recalculateRating();
            }

            return response()->json([
                'success' => true,
                'message' => "Review status updated to {$validated['status']} successfully",
                'data' => $review->fresh(['product', 'images', 'user']),
            ]);
        } catch (Exception $e) {
            Log::error('Admin\ReviewController@updateStatus failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update review status',
            ], 500);
        }
    }

    /**
     * Admin delete a review.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $review = ProductReview::with(['product', 'images'])->find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            $product = $review->product;
            $this->imageService->deleteReviewImages($review);
            $review->delete();

            if ($product) {
                $product->recalculateRating();
            }

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Admin\ReviewController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review',
            ], 500);
        }
    }
}
