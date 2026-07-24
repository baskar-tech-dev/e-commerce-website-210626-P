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
use App\Http\Controllers\Api\v1\Admin\BlogPostController;
use App\Http\Controllers\Api\v1\Admin\BlogCategoryController;
use App\Http\Controllers\Api\v1\Admin\BlogTagController;
use App\Http\Controllers\Api\v1\Admin\UserController;
use App\Http\Controllers\Api\v1\Admin\RoleController;
use App\Http\Controllers\Api\v1\Admin\PermissionController;
use App\Http\Controllers\Api\v1\Admin\SettingController;
use App\Http\Controllers\Api\v1\Admin\AuditLogController;
use App\Http\Controllers\Api\v1\Admin\MenuController;
use App\Http\Controllers\Api\v1\StorefrontProductController;
use App\Http\Controllers\Api\v1\StorefrontCheckoutController;
use App\Http\Controllers\Api\v1\CustomerProfileController;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\ThemeController;

Route::get('/theme/active', [ThemeController::class, 'getActiveTheme']);

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::get('/user', function (Request $request) {
    return $request->user()->load('roles');
})->middleware('auth:sanctum');

// Public Storefront routes
Route::middleware('throttle:public_api')->group(function () {
    Route::get('storefront/products', [StorefrontProductController::class, 'index']);
    Route::get('storefront/products/{id}', [StorefrontProductController::class, 'show']);
    Route::get('storefront/categories', [StorefrontProductController::class, 'categories']);
    Route::get('storefront/instagram-reels', [\App\Http\Controllers\Api\v1\StorefrontInstagramReelController::class, 'index']);
    Route::get('storefront/announcements', [\App\Http\Controllers\Api\v1\StorefrontAnnouncementController::class, 'index']);
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

// Customer Profile routes
Route::middleware('throttle:authenticated_api')->group(function () {
    Route::get('customer/profile', [CustomerProfileController::class, 'getProfile']);
    Route::post('customer/addresses', [CustomerProfileController::class, 'updateAddress']);
    Route::delete('customer/addresses/{id}', [CustomerProfileController::class, 'deleteAddress']);
});

// Payment Integration routes
Route::middleware(['auth:sanctum', 'throttle:payment_api'])->group(function () {
    Route::post('payment/create-order', [\App\Http\Controllers\Api\v1\PaymentController::class, 'createOrder']);
    Route::post('payment/verify', [\App\Http\Controllers\Api\v1\PaymentController::class, 'verify']);
    Route::post('payment/cancel', [\App\Http\Controllers\Api\v1\PaymentController::class, 'cancel']);
});

// Public Webhook route
Route::post('payment/webhook', [\App\Http\Controllers\Api\v1\PaymentWebhookController::class, 'handleWebhook']);

Route::prefix('admin')->middleware(['auth:sanctum', 'throttle:admin_api'])->group(function () {
    Route::get('menus', [MenuController::class, 'index']);
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    
    // Product Management (requires 'manage_products')
    Route::middleware('permission:manage_products')->group(function () {
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('tags', TagController::class);
        Route::apiResource('products', ProductController::class);
        
        // Media Management
        Route::post('media/upload', [\App\Http\Controllers\Api\v1\Admin\MediaController::class, 'upload']);
        Route::post('media/validate', [\App\Http\Controllers\Api\v1\Admin\MediaController::class, 'validateImage']);
        Route::delete('media/{id}', [\App\Http\Controllers\Api\v1\Admin\MediaController::class, 'destroy']);
        
        Route::get('inventory', [InventoryController::class, 'index']);
        Route::post('inventory/adjust', [InventoryController::class, 'adjust']);
        Route::get('inventory/ledger', [InventoryController::class, 'ledger']);
        
        Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        
        Route::apiResource('coupons', CouponController::class);
        
        Route::apiResource('blog/posts', BlogPostController::class);
        Route::apiResource('blog/categories', BlogCategoryController::class);
        Route::apiResource('blog/tags', BlogTagController::class);
        
        // Instagram Reels & YouTube Videos
        Route::apiResource('instagram-reels', \App\Http\Controllers\Api\v1\Admin\InstagramReelController::class);
        Route::post('instagram-reels/upload', [\App\Http\Controllers\Api\v1\Admin\InstagramReelController::class, 'uploadVideo']);
    });

    // Order Management (requires 'manage_orders')
    Route::middleware('permission:manage_orders')->group(function () {
        Route::put('orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('orders/{id}/shipping', [OrderController::class, 'updateShipping']);
        Route::post('orders/{id}/notes', [OrderController::class, 'addAdminNote']);
        Route::apiResource('orders', OrderController::class)->only(['index', 'show']);
        
        Route::put('returns/{id}/status', [ReturnController::class, 'updateStatus']);
        Route::post('returns/{id}/notes', [ReturnController::class, 'addAdminNote']);
        Route::post('returns/{id}/refund', [ReturnController::class, 'processRefund']);
        Route::apiResource('returns', ReturnController::class)->only(['index', 'show']);
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

    // Reports (requires 'manage_reports')
    Route::middleware('permission:manage_reports')->group(function () {
        Route::get('reports/sales', [ReportController::class, 'sales']);
        Route::get('reports/inventory', [ReportController::class, 'inventory']);
        Route::get('reports/customers', [ReportController::class, 'customers']);
    });

    // System Settings (requires 'manage_settings')
    Route::middleware('permission:manage_settings')->group(function () {
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings/batch', [SettingController::class, 'updateBatch']);
        Route::get('audit-logs', [AuditLogController::class, 'index']);
    });
});
