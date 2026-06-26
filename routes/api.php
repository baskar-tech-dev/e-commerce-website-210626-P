<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\Admin\CategoryController;
use App\Http\Controllers\Api\v1\Admin\BrandController;
use App\Http\Controllers\Api\v1\Admin\TagController;
use App\Http\Controllers\Api\v1\Admin\ProductController;
use App\Http\Controllers\Api\v1\Admin\InventoryController;
use App\Http\Controllers\Api\v1\Admin\PurchaseOrderController;
use App\Http\Controllers\Api\v1\Admin\CustomerController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('products', ProductController::class);
    
    // Inventory routes
    Route::get('inventory', [InventoryController::class, 'index']);
    Route::post('inventory/adjust', [InventoryController::class, 'adjust']);
    Route::get('inventory/ledger', [InventoryController::class, 'ledger']);

    // Purchase Orders routes
    Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);

    // Customer routes
    Route::post('customers/{id}/addresses', [CustomerController::class, 'storeAddress']);
    Route::put('customers/{id}/addresses/{addressId}', [CustomerController::class, 'updateAddress']);
    Route::delete('customers/{id}/addresses/{addressId}', [CustomerController::class, 'destroyAddress']);
    Route::apiResource('customers', CustomerController::class)->only(['index', 'show', 'update']);
});
