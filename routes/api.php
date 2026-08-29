<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\Admin\CategoryController;
use App\Http\Controllers\Api\v1\Admin\TagController;
use App\Http\Controllers\Api\v1\Admin\ProductController;
use App\Http\Controllers\Api\v1\Admin\InventoryController;
use App\Http\Controllers\Api\v1\Admin\PurchaseOrderController;
use App\Http\Controllers\Api\v1\Admin\CustomerController;
use App\Http\Controllers\Api\v1\Admin\DashboardController;
use App\Http\Controllers\Api\v1\Admin\OrderController;
use App\Http\Controllers\Api\v1\Admin\CouponController;
use App\Http\Controllers\Api\v1\Admin\ReturnController;
use App\Http\Controllers\Api\v1\Admin\ReportController;
use App\Http\Controllers\Api\v1\Admin\CashfreeReportController;
use App\Http\Controllers\Api\v1\Admin\BlogPostController;
use App\Http\Controllers\Api\v1\Admin\BlogCategoryController;
use App\Http\Controllers\Api\v1\Admin\BlogTagController;
use App\Http\Controllers\Api\v1\Admin\UserController;
use App\Http\Controllers\Api\v1\Admin\RoleController;
use App\Http\Controllers\Api\v1\Admin\PermissionController;
use App\Http\Controllers\Api\v1\Admin\SettingController;
use App\Http\Controllers\Api\v1\Admin\AuditLogController;
use App\Http\Controllers\Api\v1\Admin\MenuController;
use App\Http\Controllers\Api\v1\Admin\ColorController;
use App\Http\Controllers\Api\v1\Admin\SizeGroupController;
use App\Http\Controllers\Api\v1\Admin\SizeController;
use App\Http\Controllers\Api\v1\StorefrontProductController;
use App\Http\Controllers\Api\v1\ProductReviewController;
use App\Http\Controllers\Api\v1\StorefrontCheckoutController;
use App\Http\Controllers\Api\v1\CustomerProfileController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\WishlistController;
use App\Http\Controllers\Api\v1\ThemeController;

Route::get('/theme/active', [ThemeController::class, 'getActiveTheme']);

// Customer Auth Routes (Public)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Authenticated Customer & User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user()->load(['roles.permissions']);
    });
    Route::get('/auth/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()->load(['roles.permissions', 'customerProfile']),
        ]);
    });

    // Customer Account Profile & Addresses
    Route::get('customer/profile', [CustomerProfileController::class, 'getProfile']);
    Route::put('customer/profile', [CustomerProfileController::class, 'updateProfile']);
    Route::post('customer/addresses', [CustomerProfileController::class, 'updateAddress']);
    Route::delete('customer/addresses/{id}', [CustomerProfileController::class, 'deleteAddress']);

    // Customer Orders
    Route::get('customer/orders', [CustomerProfileController::class, 'getOrders']);
    Route::get('customer/orders/{id}', [CustomerProfileController::class, 'getOrderDetails']);

    // Customer Wishlist
    Route::get('customer/wishlist', [WishlistController::class, 'index']);
    Route::post('customer/wishlist', [WishlistController::class, 'store']);
    Route::delete('customer/wishlist/{product}', [WishlistController::class, 'destroy']);
    Route::post('customer/wishlist/merge', [WishlistController::class, 'merge']);
});

// Public Storefront routes
Route::middleware('throttle:public_api')->group(function () {
    Route::get('storefront/products', [StorefrontProductController::class, 'index']);
    Route::get('storefront/products/{id}', [StorefrontProductController::class, 'show']);
    Route::get('storefront/categories', [StorefrontProductController::class, 'categories']);
    Route::get('storefront/instagram-reels', [\App\Http\Controllers\Api\v1\StorefrontInstagramReelController::class, 'index']);
    Route::get('storefront/announcements', [\App\Http\Controllers\Api\v1\StorefrontAnnouncementController::class, 'index']);
    Route::get('storefront/welcome-gift', [\App\Http\Controllers\Api\v1\StorefrontWelcomeGiftController::class, 'index']);
    Route::get('storefront/indian-states', [StorefrontCheckoutController::class, 'getIndianStates']);
    Route::get('storefront/shipping-rates', [StorefrontCheckoutController::class, 'getShippingRates']);
    Route::get('storefront/hero-slides', [\App\Http\Controllers\Api\v1\StorefrontHeroSlideController::class, 'index']);
    Route::get('storefront/edit-badges', [StorefrontProductController::class, 'getEditBadges']);
    Route::get('storefront/occasions', [StorefrontProductController::class, 'getOccasions']);
    Route::post('storefront/subscribe', [\App\Http\Controllers\Api\v1\SubscriberController::class, 'subscribe']);

    // Public Product Reviews
    Route::get('products/{product}/reviews', [ProductReviewController::class, 'index']);
    Route::get('products/{product}/review-eligibility', [ProductReviewController::class, 'eligibility']);
});

// Authenticated Customer Reviews
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('products/{product}/reviews', [ProductReviewController::class, 'store']);
    Route::put('reviews/{review}', [ProductReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ProductReviewController::class, 'destroy']);
    Route::post('reviews/{review}/helpful', [ProductReviewController::class, 'toggleHelpful']);
});

Route::post('storefront/checkout', [StorefrontCheckoutController::class, 'placeOrder'])
    ->middleware('throttle:checkout_api');

Route::get('storefront/coupons/{code}', function ($code) {
    $coupon = \App\Models\Coupon::where('code', $code)
        ->where('is_active', true)
        ->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })
        ->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
        })
        ->first();

    if (!$coupon) {
        return response()->json([
            'success' => false,
            'message' => 'Coupon not found or inactive',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $coupon,
    ]);
})->middleware('throttle:coupon_api');

// Payment Integration routes (Cashfree Payments)
Route::middleware(['throttle:payment_api'])->group(function () {
    Route::post('payment/cashfree/create', [\App\Http\Controllers\Api\v1\PaymentController::class, 'createOrder']);
    Route::post('payment/create-order', [\App\Http\Controllers\Api\v1\PaymentController::class, 'createOrder']);

    Route::post('payment/cashfree/verify', [\App\Http\Controllers\Api\v1\PaymentController::class, 'verify']);
    Route::post('payment/verify', [\App\Http\Controllers\Api\v1\PaymentController::class, 'verify']);

    Route::post('payment/cashfree/cancel', [\App\Http\Controllers\Api\v1\PaymentController::class, 'cancel']);
    Route::post('payment/cancel', [\App\Http\Controllers\Api\v1\PaymentController::class, 'cancel']);

    Route::get('payment/cashfree/status/{order}', [\App\Http\Controllers\Api\v1\PaymentController::class, 'status']);
    Route::get('payment/status/{order}', [\App\Http\Controllers\Api\v1\PaymentController::class, 'status']);
});

// Public Cashfree Webhook routes
Route::post('payment/cashfree/webhook', [\App\Http\Controllers\Api\v1\PaymentWebhookController::class, 'handleWebhook']);
Route::post('payment/webhook', [\App\Http\Controllers\Api\v1\PaymentWebhookController::class, 'handleWebhook']);

Route::prefix('admin')->middleware(['auth:sanctum', 'role', 'throttle:admin_api'])->group(function () {
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    
    // Product Management (requires 'manage_products')
    Route::middleware('permission:manage_products')->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('tags', TagController::class);
        Route::apiResource('products', ProductController::class);

        // Color & Size Masters
        Route::get('colors/active', [ColorController::class, 'activeList']);
        Route::apiResource('colors', ColorController::class);

        Route::get('size-groups/active', [SizeGroupController::class, 'activeList']);
        Route::apiResource('size-groups', SizeGroupController::class);

        Route::apiResource('sizes', SizeController::class);
        
        // Media Management
        Route::post('media/upload', [\App\Http\Controllers\Api\v1\Admin\MediaController::class, 'upload']);
        Route::post('media/validate', [\App\Http\Controllers\Api\v1\Admin\MediaController::class, 'validateImage']);
        Route::delete('media/{id}', [\App\Http\Controllers\Api\v1\Admin\MediaController::class, 'destroy']);
        
        Route::get('inventory', [InventoryController::class, 'index']);
        Route::get('inventory/overview', [InventoryController::class, 'overview']);
        Route::post('inventory/quick-adjust', [InventoryController::class, 'quickAdjust']);
        Route::get('inventory/export-overview-csv', [InventoryController::class, 'exportOverviewCsv']);
        Route::post('inventory/adjust', [InventoryController::class, 'adjust']);
        Route::get('inventory/ledger', [InventoryController::class, 'ledger']);
        Route::get('inventory/product-matrix/{id}', [InventoryController::class, 'productMatrix']);
        Route::post('inventory/bulk-matrix-update', [InventoryController::class, 'bulkMatrixUpdate']);
        Route::get('inventory/export-template/{id}', [InventoryController::class, 'exportTemplate']);
        Route::post('inventory/import-csv', [InventoryController::class, 'importCsv']);
        
        // Stock Inward (Goods Receipt & Inventory Increment)
        Route::get('inward/form-data', [\App\Http\Controllers\Api\v1\Admin\StockInwardController::class, 'formData']);
        Route::apiResource('inward', \App\Http\Controllers\Api\v1\Admin\StockInwardController::class);

        // Factory Master
        Route::get('factories/active', [\App\Http\Controllers\Api\v1\Admin\FactoryController::class, 'activeList']);
        Route::apiResource('factories', \App\Http\Controllers\Api\v1\Admin\FactoryController::class);

        Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        
        Route::apiResource('coupons', CouponController::class);
        
        Route::apiResource('blog/posts', BlogPostController::class);
        Route::apiResource('blog/categories', BlogCategoryController::class);
        Route::apiResource('blog/tags', BlogTagController::class);
        
        // Instagram Reels & YouTube Videos
        Route::apiResource('instagram-reels', \App\Http\Controllers\Api\v1\Admin\InstagramReelController::class);
        Route::post('instagram-reels/upload', [\App\Http\Controllers\Api\v1\Admin\InstagramReelController::class, 'uploadVideo']);

        // Admin Review Moderation
        Route::get('reviews', [\App\Http\Controllers\Api\v1\Admin\ReviewController::class, 'index']);
        Route::get('reviews/{id}', [\App\Http\Controllers\Api\v1\Admin\ReviewController::class, 'show']);
        Route::patch('reviews/{id}/status', [\App\Http\Controllers\Api\v1\Admin\ReviewController::class, 'updateStatus']);
        Route::delete('reviews/{id}', [\App\Http\Controllers\Api\v1\Admin\ReviewController::class, 'destroy']);

        // WhatsApp Club & Subscribers Leads
        Route::get('subscribers', [\App\Http\Controllers\Api\v1\SubscriberController::class, 'index']);

        // Section Badges (The Maya Sree Edit)
        Route::get('section-badges', [\App\Http\Controllers\Api\v1\Admin\SectionBadgeController::class, 'index']);
        Route::post('section-badges', [\App\Http\Controllers\Api\v1\Admin\SectionBadgeController::class, 'store']);
        Route::put('section-badges/{id}', [\App\Http\Controllers\Api\v1\Admin\SectionBadgeController::class, 'update']);
        Route::patch('section-badges/{id}/toggle', [\App\Http\Controllers\Api\v1\Admin\SectionBadgeController::class, 'toggle']);
        Route::post('section-badges/reorder', [\App\Http\Controllers\Api\v1\Admin\SectionBadgeController::class, 'reorder']);
        Route::delete('section-badges/{id}', [\App\Http\Controllers\Api\v1\Admin\SectionBadgeController::class, 'destroy']);

        // Shop by Occasions Database Table
        Route::get('occasions', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'index']);
        Route::post('occasions', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'store']);
        Route::post('occasions/upload-image', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'uploadImage']);
        Route::put('occasions/{id}', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'update']);
        Route::patch('occasions/{id}/toggle', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'toggle']);
        Route::post('occasions/reorder', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'reorder']);
        Route::delete('occasions/{id}', [\App\Http\Controllers\Api\v1\Admin\OccasionController::class, 'destroy']);

        // Hero Banner Slides Management (Admin / Super Admin)
        Route::get('hero-slides', [\App\Http\Controllers\Api\v1\Admin\HeroSlideController::class, 'index']);
        Route::post('hero-slides', [\App\Http\Controllers\Api\v1\Admin\HeroSlideController::class, 'store']);
        Route::post('hero-slides/upload-image', [\App\Http\Controllers\Api\v1\Admin\HeroSlideController::class, 'uploadImage']);
        Route::put('hero-slides/{id}', [\App\Http\Controllers\Api\v1\Admin\HeroSlideController::class, 'update']);
        Route::post('hero-slides/reorder', [\App\Http\Controllers\Api\v1\Admin\HeroSlideController::class, 'reorder']);
        Route::delete('hero-slides/{id}', [\App\Http\Controllers\Api\v1\Admin\HeroSlideController::class, 'destroy']);
    });

    // Order Management (requires 'manage_orders')
    Route::middleware('permission:manage_orders')->group(function () {
        Route::get('orders/statuses', [OrderController::class, 'statuses']);
        Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('orders/{id}/shipping', [OrderController::class, 'updateShipping']);
        Route::post('orders/{id}/notes', [OrderController::class, 'addAdminNote']);
        Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
        
        Route::put('returns/{id}/status', [ReturnController::class, 'updateStatus']);
        Route::post('returns/{id}/notes', [ReturnController::class, 'addAdminNote']);
        Route::post('returns/{id}/refund', [ReturnController::class, 'processRefund']);
        Route::apiResource('returns', ReturnController::class)->only(['index', 'show']);

        // Courier Management
        Route::get('couriers/active', [\App\Http\Controllers\Api\v1\Admin\CourierController::class, 'activeList']);
        Route::patch('couriers/{id}/toggle', [\App\Http\Controllers\Api\v1\Admin\CourierController::class, 'toggleActive']);
        Route::apiResource('couriers', \App\Http\Controllers\Api\v1\Admin\CourierController::class);
    });

    // User & Customer Management (requires 'manage_users')
    Route::middleware('permission:manage_users')->group(function () {
        Route::post('customers/{id}/addresses', [CustomerController::class, 'storeAddress']);
        Route::put('customers/{id}/addresses/{addressId}', [CustomerController::class, 'updateAddress']);
        Route::delete('customers/{id}/addresses/{addressId}', [CustomerController::class, 'destroyAddress']);
        Route::apiResource('customers', CustomerController::class)->only(['index', 'show', 'update']);
        
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index']);
    });

    // Reports (Granular permission per report)
    Route::middleware('permission:reports_sales')->get('reports/sales', [ReportController::class, 'sales']);
    Route::middleware('permission:reports_inventory')->get('reports/inventory', [ReportController::class, 'inventory']);
    Route::middleware('permission:reports_customers')->get('reports/customers', [ReportController::class, 'customers']);
    Route::middleware('permission:payments')->group(function () {
        Route::get('reports/payments', [CashfreeReportController::class, 'payments']);
        Route::get('reports/payments/summary', [CashfreeReportController::class, 'paymentSummary']);
        Route::post('reports/payments/{payment}/verify-cashfree', [CashfreeReportController::class, 'verifyPaymentWithCashfree']);
    });
    Route::middleware('permission:settlements')->group(function () {
        Route::get('reports/settlements', [CashfreeReportController::class, 'settlements']);
        Route::get('reports/settlements/summary', [CashfreeReportController::class, 'settlementSummary']);
    });

    // System Settings (requires 'manage_settings')
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings/batch', [SettingController::class, 'updateBatch']);
        Route::post('settings/test-email', [SettingController::class, 'testEmail']);
        Route::get('audit-logs', [AuditLogController::class, 'index']);
        
        // Announcement Management
        Route::patch('announcements/{id}/toggle', [\App\Http\Controllers\Api\v1\Admin\AnnouncementController::class, 'toggleActive']);
        Route::post('announcements/reorder', [\App\Http\Controllers\Api\v1\Admin\AnnouncementController::class, 'reorder']);
        Route::apiResource('announcements', \App\Http\Controllers\Api\v1\Admin\AnnouncementController::class);
    });
});
