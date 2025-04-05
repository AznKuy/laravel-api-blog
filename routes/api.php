<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // user routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Post Routes
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('posts.index');
        Route::post('/', [PostController::class, 'store'])->name('posts.store');
        Route::get('/{post}', [PostController::class, 'show'])->name('posts.show');
        Route::put('/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/{posts}', [PostController::class, 'destroy'])->name('posts.destroy');

        // Like routes
        Route::post('/{post}/like', [LikeController::class, 'like'])->name('posts.like');
        Route::delete('/{post}/unlike', [LikeController::class, 'unlike'])->name('posts.unlike');

        // Comment routes
        Route::post('/{post}/comment', [CommentController::class, 'store'])->name('comments.store');
    });



    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
