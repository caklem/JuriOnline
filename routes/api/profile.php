<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// routes/api/profile.php — included inside auth:sanctum group.
Route::get('/profile', [ProfileController::class, 'show']);
Route::patch('/profile', [ProfileController::class, 'update']);
Route::put('/profile/jury', [ProfileController::class, 'updateJuryProfile']);
Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
Route::delete('/profile', [ProfileController::class, 'destroy']);
