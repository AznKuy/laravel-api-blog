<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // user routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Post Routes
    Route::prefix('posts')->group(function () {
        Route::post('/', [PostController::class, 'store']);
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

    // get all notifications
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
