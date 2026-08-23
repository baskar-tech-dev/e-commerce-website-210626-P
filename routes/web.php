<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Direct storage fallback route to serve uploaded media on live production 
| environments where symlinks might be disabled or broken.
|
*/
Route::get('storage/{path}', function (string $path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (file_exists($filePath)) {
        $mime = File::mimeType($filePath) ?: 'application/octet-stream';
        return Response::file($filePath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
    
    abort(404);
})->where('path', '.*');

use App\Http\Controllers\Api\v1\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
