<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\TripMemberController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TripStatisticsController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth
Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Trips
    Route::get('/trips', [TripController::class, 'index']);
    Route::post('/trips', [TripController::class, 'store']);
    Route::get('/trips/{id}', [TripController::class, 'show']);
    Route::put('/trips/{id}', [TripController::class, 'update']);
    Route::delete('/trips/{id}', [TripController::class, 'destroy']);

    // Trip Members
    Route::post('/trips/join', [TripMemberController::class, 'joinByCode']);
    Route::post('/trips/{tripId}/members', [TripMemberController::class, 'addMember']);
    Route::put('/trips/{tripId}/members/{userId}/role', [TripMemberController::class, 'updateRole']);
    Route::delete('/trips/{tripId}/members/{userId}', [TripMemberController::class, 'removeMember']);

    // Expenses
    Route::get('/trips/{tripId}/expenses', [ExpenseController::class, 'index']);
    Route::post('/trips/{tripId}/expenses', [ExpenseController::class, 'store']);
    Route::get('/expenses/{expenseId}', [ExpenseController::class, 'show']);
    Route::put('/expenses/{expenseId}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expenseId}', [ExpenseController::class, 'destroy']);

    // Trip Statistics
    Route::get('/trips/{tripId}/statistics', [TripStatisticsController::class, 'getTripStatistics']);
    Route::get('/trips/{tripId}/user-statistics', [TripStatisticsController::class, 'getUserStatistics']);
    Route::get('/trips/{tripId}/balance', [TripStatisticsController::class, 'getBalanceSheet']);

    // Friends
    Route::get('/friends', [FriendController::class, 'index']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::post('/friends/accept/{id}', [FriendController::class, 'acceptRequest']);
    Route::post('/friends/reject/{id}', [FriendController::class, 'rejectRequest']);
    Route::get('/friends/pending', [FriendController::class, 'pendingRequests']);
    Route::delete('/friends/{friendId}', [FriendController::class, 'removeFriend']);
    Route::get('/users/search', [FriendController::class, 'searchUsers']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});



Route::middleware(['auth:sanctum', 'trip.member'])->group(function () {
    Route::get('/trips/{tripId}/expenses', [ExpenseController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'trip.admin'])->group(function () {
    Route::post('/trips/{tripId}/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{expenseId}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expenseId}', [ExpenseController::class, 'destroy']);
});
