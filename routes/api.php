<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // user routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Post Routes
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{post}', [PostController::class, 'show']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{posts}', [PostController::class, 'destroy']);

        // Like routes
        Route::post('/{post}/like', [LikeController::class, 'like']);
        Route::delete('/{post}/unlike', [LikeController::class, 'unlike']);

        // Comment routes
        Route::get('/{post}/comments', [CommentController::class, 'index']);
        Route::post('/{post}/comment', [CommentController::class, 'store']);
    });

    Route::prefix('comments')->group(function () {
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
    });

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'showProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);


    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
