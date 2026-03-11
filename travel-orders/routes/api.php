<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TravelOrderController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});
Route::get('/notifications', function () {
    return response()->json(auth()->user()->notifications);
});
Route::patch('/notifications/{id}/read', function (string $id) {
    $notification = auth()->user()->notifications()->findOrFail($id);
    $notification->markAsRead();
    return response()->json(['message' => 'Notificacao marcada como lida!']);
});
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::apiResource('travel-orders', TravelOrderController::class);
    Route::patch('travel-orders/{id}/status', [TravelOrderController::class, 'updateStatus']);
});
