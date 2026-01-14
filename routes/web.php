<?php

use App\Http\Controllers\Web\CommentController;
use App\Http\Controllers\Web\FriendController;
use App\Http\Controllers\Web\LikeController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // redirect dashboard to posts feed
    Route::get('/dashboard', [PostController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/users/{user}', [ProfileController::class, 'show'])->name('profile.show');

    // Posts
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Comments
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Likes
    Route::post('/posts/{post}/like', [LikeController::class, 'togglePostLike'])->name('posts.like');

    // Friends
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::get('/friends/search', [FriendController::class, 'search'])->name('friends.search');
    Route::post('/friends/request', [FriendController::class, 'sendRequest'])->name('friends.send');
    Route::patch('/friends/request/{friendRequest}/accept', [FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::patch('/friends/request/{friendRequest}/accept', [FriendController::class, 'acceptRequest'])->name('friends.accept');
    Route::delete('/friends/request/{friendRequest}/reject', [FriendController::class, 'rejectRequest'])->name('friends.reject');
    Route::delete('/friends/cancel/{user}', [FriendController::class, 'cancelRequest'])->name('friends.cancel');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

require __DIR__ . '/auth.php';
