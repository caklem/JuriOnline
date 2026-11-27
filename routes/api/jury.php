<?php

use App\Http\Controllers\Api\JuryController;
use Illuminate\Support\Facades\Route;

Route::prefix('jury')->group(function () {
    Route::get('/invitations', [JuryController::class, 'invitations']);
    Route::post('/invitations/{assignment}/accept', [JuryController::class, 'acceptInvitation']);
    Route::post('/invitations/{assignment}/decline', [JuryController::class, 'declineInvitation']);

    Route::get('/events', [JuryController::class, 'events']);
    Route::get('/events/{event}/participants', [JuryController::class, 'participants']);
    Route::get('/events/{event}/leaderboard', [JuryController::class, 'leaderboard']);
    Route::get('/events/{event}/form', [JuryController::class, 'form']);

    Route::get('/registrations/{registration}/form', [JuryController::class, 'registrationForm']);
    Route::post('/registrations/{registration}/score', [JuryController::class, 'storeScore']);

    Route::get('/revisions', [JuryController::class, 'revisions']);
    Route::post('/revisions/{revision}/reject', [JuryController::class, 'rejectRevision']);

    Route::get('/scores/{juryScore}', [JuryController::class, 'showScore']);
    Route::patch('/scores/{juryScore}', [JuryController::class, 'updateScore']);
});
