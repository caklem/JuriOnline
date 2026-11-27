<?php

use App\Http\Controllers\Api\OwnerController;
use Illuminate\Support\Facades\Route;

Route::get('/owner/users', [OwnerController::class, 'users']);
Route::post('/owner/users', [OwnerController::class, 'storeUser']);
Route::patch('/owner/users/{user}/role', [OwnerController::class, 'updateRole']);
Route::patch('/owner/users/{user}/scope', [OwnerController::class, 'updateScope']);
Route::patch('/owner/users/{user}/status', [OwnerController::class, 'toggleStatus']);

Route::get('/owner/scores', [OwnerController::class, 'scores']);
