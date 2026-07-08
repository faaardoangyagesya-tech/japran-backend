<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;

// ─── Public Routes ───
Route::get('/games', [PublicController::class, 'games']);
Route::get('/accounts', [PublicController::class, 'accounts']);
Route::get('/accounts/best-sellers', [PublicController::class, 'bestSellers']);
Route::get('/accounts/most-expensive', [PublicController::class, 'mostExpensive']);
Route::get('/accounts/{code}', [PublicController::class, 'accountDetail']);
Route::get('/announcements/active', [PublicController::class, 'announcements']);

// ─── Auth Routes ───
Route::post('/admin/login', [AuthController::class, 'login']);

// ─── Admin Protected Routes ───
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/me', [AuthController::class, 'me']);
    Route::post('/admin/logout', [AuthController::class, 'logout']);

    Route::get('/admin/stats', [AdminController::class, 'stats']);

    Route::get('/admin/accounts', [AdminController::class, 'allAccounts']);
    Route::post('/admin/accounts', [AdminController::class, 'storeAccount']);
    Route::put('/admin/accounts/{id}', [AdminController::class, 'updateAccount']);
    Route::delete('/admin/accounts/{id}', [AdminController::class, 'deleteAccount']);
    Route::post('/admin/accounts/generate-codes', [AdminController::class, 'generateCodes']);

    Route::get('/admin/games', [AdminController::class, 'allGames']);
    Route::post('/admin/games', [AdminController::class, 'storeGame']);
    Route::put('/admin/games/{id}', [AdminController::class, 'updateGame']);
    Route::delete('/admin/games/{id}', [AdminController::class, 'deleteGame']);

    Route::get('/admin/orders', [AdminController::class, 'allOrders']);
    Route::post('/admin/orders', [AdminController::class, 'storeOrder']);

    Route::get('/admin/announcements', [AdminController::class, 'allAnnouncements']);
    Route::post('/admin/announcements', [AdminController::class, 'storeAnnouncement']);
    Route::put('/admin/announcements/{id}', [AdminController::class, 'updateAnnouncement']);
    Route::delete('/admin/announcements/{id}', [AdminController::class, 'deleteAnnouncement']);

    Route::get('/admin/admins', [AdminController::class, 'allAdmins']);
    Route::post('/admin/admins', [AdminController::class, 'storeAdmin']);
    Route::delete('/admin/admins/{id}', [AdminController::class, 'deleteAdmin']);
});
