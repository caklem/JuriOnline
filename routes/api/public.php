<?php

use App\Http\Controllers\Api\PublicEventController;
use Illuminate\Support\Facades\Route;

// routes/api/public.php — included outside the auth:sanctum group (akses publik).
Route::get('/public/events', [PublicEventController::class, 'index']);
Route::get('/public/events/by-slug/{slug}', [PublicEventController::class, 'showBySlug']);
Route::get('/public/events/{event}', [PublicEventController::class, 'show']);
