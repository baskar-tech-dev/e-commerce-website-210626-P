<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewHelpfulVote;
use App\Models\ProductReviewImage;
use App\Models\Setting;
use App\Services\ReviewEligibilityService;
use App\Services\ReviewImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductReviewController extends Controller
{
    protected ReviewEligibilityService $eligibilityService;
    protected ReviewImageService $imageService;

    public function __construct(
        ReviewEligibilityService $eligibilityService,
        ReviewImageService $imageService
    ) {
        $this->eligibilityService = $eligibilityService;
        $this->imageService = $imageService;
    }

    /**
     * Display listing of approved reviews for a product with summary metrics.
     */
    public function index(string $productId, Request $request): JsonResponse
    {
        try {
            $product = Product::where('id', $productId)
                ->orWhere('uuid', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            // Summary metrics calculated from approved reviews
            $approvedQuery = ProductReview::where('product_id', $product->id)->approved();
            $totalReviews = (int) $approvedQuery->count();
            $avgRating = $totalReviews > 0 ? round((float) $approvedQuery->avg('rating'), 2) : 0.00;

            // Distribution breakdown 1-5 stars
            $distributionRaw = ProductReview::where('product_id', $product->id)
                ->approved()
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray();

            $distribution = [];
            for ($star = 5; $star >= 1; $star--) {
                $count = $distributionRaw[$star] ?? 0;
                $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                $distribution[$star] = [
                    'count' => $count,
                    'percentage' => (int) $pct,
                ];
            }

            // Fetch paginated reviews
            $perPage = (int) $request->input('per_page', 10);
            $reviews = ProductReview::with(['user:id,first_name,last_name,email', 'images'])
                ->where('product_id', $product->id)
                ->approved()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add user vote status if authenticated
            $currentUserId = auth('sanctum')->id();
            $userVotes = [];
            if ($currentUserId && $reviews->isNotEmpty()) {
                $reviewIds = $reviews->pluck('id')->toArray();
                $userVotes = ProductReviewHelpfulVote::whereIn('product_review_id', $reviewIds)
                    ->where('user_id', $currentUserId)
                    ->pluck('product_review_id')
                    ->toArray();
            }

            $formattedReviews = collect($reviews->items())->map(function ($rev) use ($userVotes) {
                $revArray = $rev->toArray();
                // Format user display name (e.g. Priya S.)
                $firstName = $rev->user?->first_name ?? 'Customer';
                $lastNameInitial = $rev->user?->last_name ? ' ' . mb_substr($rev->user->last_name, 0, 1) . '.' : '';
                $revArray['user_display_name'] = trim($firstName . $lastNameInitial);
                $revArray['is_voted_helpful'] = in_array($rev->id, $userVotes);
                return $revArray;
            });

            return response()->json([
                'success' => true,
                'data' => $formattedReviews,
                'summary' => [
                    'avg_rating' => $avgRating,
                    'total_reviews' => $totalReviews,
                    'rating_distribution' => $distribution,
                    'reviews_enabled' => (bool) $product->reviews_enabled,
                ],
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ]);
        } catch (Exception $e) {
            Log::error('ProductReviewController@index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reviews',
            ], 500);
        }
    }

    /**
     * Check customer review eligibility for a product.
     */
    public function eligibility(string $productId): JsonResponse
    {
        try {
            $product = Product::where('id', $productId)
                ->orWhere('uuid', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $user = auth('sanctum')->user();
            $eligibility = $this->eligibilityService->checkEligibility($user, $product);

            return response()->json([
                'success' => true,
                'data' => $eligibility,
            ]);
        } catch (Exception $e) {
            Log::error('ProductReviewController@eligibility failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check review eligibility',
            ], 500);
        }
    }

    /**
     * Submit a new customer review.
     */
    public function store(string $productId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:5|max:2000',
            'images' => 'nullable|array|max:4',
            'images.*' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        try {
            $product = Product::where('id', $productId)
                ->orWhere('uuid', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $user = auth()->user() ?? auth('sanctum')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please sign in to submit a review.',
                ], 401);
            }
            $eligibility = $this->eligibilityService->checkEligibility($user, $product);

            if (!$eligibility['can_review']) {
                return response()->json([
                    'success' => false,
                    'message' => $eligibility['message'] ?? 'You are not eligible to review this product.',
                    'reason' => $eligibility['reason'],
                ], 403);
            }

            $approvalRequired = Setting::get('admin_approval_required', 'reviews', true);
            $status = $approvalRequired ? 'pending' : 'approved';

            return DB::transaction(function () use ($request, $validated, $product, $user, $eligibility, $status) {
                $review = ProductReview::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'order_id' => $eligibility['order_id'] ?? null,
                    'rating' => $validated['rating'],
                    'review' => trim($validated['review']),
                    'status' => $status,
                    'is_verified_purchase' => $eligibility['is_verified_purchase'] ?? false,
                    'helpful_count' => 0,
                ]);

                // Handle images
                if ($request->hasFile('images') && Setting::get('customer_images_allowed', 'reviews', true)) {
                    $files = $request->file('images');
                    $savedPaths = $this->imageService->processAndSaveImages($files, $product->id, $review->id);

                    foreach ($savedPaths as $index => $path) {
                        ProductReviewImage::create([
                            'product_review_id' => $review->id,
                            'image_path' => $path,
                            'sort_order' => $index,
                        ]);
                    }
                }

                // If review is auto-approved, recalculate rating
                if ($status === 'approved') {
                    $product->recalculateRating();
                }

                $review->load(['user:id,first_name,last_name,email', 'images']);

                $msg = $status === 'pending'
                    ? 'Your review has been submitted and is awaiting admin approval.'
                    : 'Thank you for sharing your experience!';

                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data' => $review,
                ], 201);
            });
        } catch (Exception $e) {
            Log::error('ProductReviewController@store failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Something went wrong while submitting your review.',
            ], 422);
        }
    }

    /**
     * Update customer's existing review.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $editingAllowed = Setting::get('customer_editing_allowed', 'reviews', true);
        if (!$editingAllowed) {
            return response()->json([
                'success' => false,
                'message' => 'Review editing is currently disabled.',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|min:5|max:2000',
            'removed_image_ids' => 'nullable|array',
            'removed_image_ids.*' => 'integer',
            'new_images' => 'nullable|array|max:4',
            'new_images.*' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);

        try {
            $review = ProductReview::with('images')->find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            if ($review->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own review.',
                ], 403);
            }

            return DB::transaction(function () use ($request, $validated, $review) {
                // Remove requested images
                if (!empty($validated['removed_image_ids'])) {
                    $imagesToRemove = ProductReviewImage::whereIn('id', $validated['removed_image_ids'])
                        ->where('product_review_id', $review->id)
                        ->get();

                    foreach ($imagesToRemove as $img) {
                        $this->imageService->deleteSingleImageFile($img->image_path);
                        $img->delete();
                    }
                }

                // Check remaining image count + new images
                $remainingCount = ProductReviewImage::where('product_review_id', $review->id)->count();
                $newFiles = $request->file('new_images') ?? [];
                $maxImages = (int) Setting::get('max_images_per_review', 'reviews', 4);

                if (($remainingCount + count($newFiles)) > $maxImages) {
                    throw new Exception("Total images cannot exceed {$maxImages} photos per review.");
                }

                // Process new images
                if (!empty($newFiles) && Setting::get('customer_images_allowed', 'reviews', true)) {
                    $savedPaths = $this->imageService->processAndSaveImages($newFiles, $review->product_id, $review->id);

                    foreach ($savedPaths as $index => $path) {
                        ProductReviewImage::create([
                            'product_review_id' => $review->id,
                            'image_path' => $path,
                            'sort_order' => $remainingCount + $index,
                        ]);
                    }
                }

                $approvalRequired = Setting::get('admin_approval_required', 'reviews', true);
                $review->rating = $validated['rating'];
                $review->review = trim($validated['review']);
                // If moderation is required, edited reviews return to pending status
                if ($approvalRequired) {
                    $review->status = 'pending';
                }
                $review->save();

                $review->product->recalculateRating();
                $review->load(['user:id,first_name,last_name,email', 'images']);

                $msg = $review->status === 'pending'
                    ? 'Your review has been updated and is awaiting admin approval.'
                    : 'Your review has been updated successfully!';

                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data' => $review,
                ]);
            });
        } catch (Exception $e) {
            Log::error('ProductReviewController@update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to update review.',
            ], 422);
        }
    }

    /**
     * Customer deletes their review.
     */
    public function destroy(int $id): JsonResponse
    {
        $deletionAllowed = Setting::get('customer_deletion_allowed', 'reviews', true);
        if (!$deletionAllowed) {
            return response()->json([
                'success' => false,
                'message' => 'Review deletion is currently disabled.',
            ], 403);
        }

        try {
            $review = ProductReview::with('images')->find($id);

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            if ($review->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own review.',
                ], 403);
            }

            $product = $review->product;
            $this->imageService->deleteReviewImages($review);
            $review->delete();

            if ($product) {
                $product->recalculateRating();
            }

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('ProductReviewController@destroy failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review.',
            ], 500);
        }
    }

    /**
     * Toggle helpful vote for a review.
     */
    public function toggleHelpful(int $id): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Please sign in to vote.',
            ], 401);
        }

        try {
            $review = ProductReview::find($id);
            if (!$review || $review->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                ], 404);
            }

            $existingVote = ProductReviewHelpfulVote::where('product_review_id', $review->id)
                ->where('user_id', $userId)
                ->first();

            if ($existingVote) {
                $existingVote->delete();
                $isVoted = false;
            } else {
                ProductReviewHelpfulVote::create([
                    'product_review_id' => $review->id,
                    'user_id' => $userId,
                ]);
                $isVoted = true;
            }

            $count = ProductReviewHelpfulVote::where('product_review_id', $review->id)->count();
            $review->helpful_count = $count;
            $review->save();

            return response()->json([
                'success' => true,
                'helpful_count' => $count,
                'is_voted' => $isVoted,
            ]);
        } catch (Exception $e) {
            Log::error('ProductReviewController@toggleHelpful failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register helpful vote.',
            ], 500);
        }
    }
}
