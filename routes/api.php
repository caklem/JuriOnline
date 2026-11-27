<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

require __DIR__ . '/api/public.php';
require __DIR__ . '/api/auth.php';

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [MeController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);

    require __DIR__ . '/api/profile.php';
    require __DIR__ . '/api/notifications.php';
    require __DIR__ . '/api/participant.php';
    require __DIR__ . '/api/jury.php';
    require __DIR__ . '/api/chat.php';
    require __DIR__ . '/api/admin.php';
    require __DIR__ . '/api/owner.php';
});
