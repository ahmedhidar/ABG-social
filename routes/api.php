<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');

    // Posts
    Route::apiResource('posts', App\Http\Controllers\Api\PostController::class);

    // Comments
    Route::post('/posts/{post}/comments', [App\Http\Controllers\Api\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [App\Http\Controllers\Api\CommentController::class, 'destroy'])->name('comments.destroy');

    // Likes
    Route::post('/posts/{post}/like', [App\Http\Controllers\Api\LikeController::class, 'toggle'])->name('posts.like');

    // Friends
    Route::post('/friends/request/{user}', [App\Http\Controllers\Api\FriendController::class, 'sendRequest'])->name('friends.request');
    Route::post('/friends/accept/{user}', [App\Http\Controllers\Api\FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::post('/friends/reject/{user}', [App\Http\Controllers\Api\FriendController::class, 'rejectRequest'])->name('friends.reject');
    Route::delete('/friends/cancel/{user}', [App\Http\Controllers\Api\FriendController::class, 'cancelRequest'])->name('friends.cancel');
    Route::delete('/friends/{user}', [App\Http\Controllers\Api\FriendController::class, 'removeFriend'])->name('friends.remove');
    Route::get('/friends/requests', [App\Http\Controllers\Api\FriendController::class, 'getRequests'])->name('friends.requests');
    Route::get('/friends', [App\Http\Controllers\Api\FriendController::class, 'getFriends'])->name('friends.index');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\Api\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [App\Http\Controllers\Api\NotificationController::class, 'markAsRead'])->name('notifications.markRead');

    // Profile
    Route::get('/profile/{user}', [App\Http\Controllers\Api\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\Api\ProfileController::class, 'update'])->name('profile.update');
});
