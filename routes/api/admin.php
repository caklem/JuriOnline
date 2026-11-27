<?php

use App\Http\Controllers\Api\AdminController;
use Illuminate\Support\Facades\Route;

// Semua rute di file ini berada di dalam grup auth:sanctum (lihat routes/api.php).
// Otorisasi per peran/cakupan event ditangani AdminController.

Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
Route::get('/admin/organizer-profile', [App\Http\Controllers\Api\OrganizerProfileController::class, 'show']);
Route::put('/admin/organizer-profile', [App\Http\Controllers\Api\OrganizerProfileController::class, 'update']);
Route::post('/admin/organizer-profile', [App\Http\Controllers\Api\OrganizerProfileController::class, 'update']);
Route::get('/admin/registrations/{registration}', [AdminController::class, 'registrationDetail']);

Route::get('/admin/categories', [AdminController::class, 'categories']);

Route::get('/admin/events', [AdminController::class, 'index']);
Route::post('/admin/events', [AdminController::class, 'store']);
Route::get('/admin/events/{event}', [AdminController::class, 'show']);
Route::put('/admin/events/{event}', [AdminController::class, 'update']);
// Form edit Next.js mengirim multipart (banner), jadi POST dipakai untuk update juga.
Route::post('/admin/events/{event}', [AdminController::class, 'update']);
Route::post('/admin/events/{event}/duplicate', [AdminController::class, 'duplicate']);
Route::post('/admin/events/{event}/status', [AdminController::class, 'updateStatus']);
Route::post('/admin/events/{event}/scoring', [AdminController::class, 'updateScoring']);

Route::get('/admin/events/{event}/participants/export', [AdminController::class, 'exportParticipants']);
Route::get('/admin/events/{event}/participants', [AdminController::class, 'participants']);
Route::patch('/admin/registrations/{registration}/status', [AdminController::class, 'updateRegistrationStatus']);

Route::get('/admin/payments/all', [AdminController::class, 'allPayments']);
Route::get('/admin/payments', [AdminController::class, 'pendingPayments']);
Route::get('/admin/payments/{payment}/proof', [AdminController::class, 'proof']);
Route::post('/admin/payments/{payment}/verify', [AdminController::class, 'verifyPayment']);

Route::get('/admin/events/{event}/recap', [AdminController::class, 'recap']);
Route::get('/admin/events/{event}/evaluations', [AdminController::class, 'evaluations']);
Route::post('/admin/events/{event}/evaluations/publish', [AdminController::class, 'publishAllEvaluations']);
Route::post('/admin/scores/{juryScore}/review', [AdminController::class, 'reviewEvaluation']);
Route::post('/admin/scores/{juryScore}/publish', [AdminController::class, 'publishEvaluation']);
Route::get('/admin/events/{event}/score-revisions', [AdminController::class, 'scoreRevisions']);
Route::post('/admin/scores/{juryScore}/revision-request', [AdminController::class, 'requestScoreRevision']);

Route::get('/admin/events/{event}/jury-assignments', [AdminController::class, 'juryAssignments']);
Route::post('/admin/events/{event}/jury-assignments', [AdminController::class, 'assignJury']);
Route::delete('/admin/events/{event}/jury-assignments/{assignment}', [AdminController::class, 'destroyAssignment']);

Route::get('/admin/events/{event}/registration-form', [App\Http\Controllers\Api\AdminController::class, 'registrationForm']);
Route::put('/admin/events/{event}/registration-form', [App\Http\Controllers\Api\AdminController::class, 'saveRegistrationForm']);
Route::get('/admin/events/{event}/jury-forms', [App\Http\Controllers\Api\AdminController::class, 'juryForms']);
Route::post('/admin/events/{event}/jury-forms', [App\Http\Controllers\Api\AdminController::class, 'saveJuryForm']);
Route::post('/admin/events/{event}/jury-forms/save-template', [App\Http\Controllers\Api\AdminController::class, 'saveJuryFormAsTemplate']);
Route::post('/admin/events/{event}/jury-forms/quick', [App\Http\Controllers\Api\AdminController::class, 'quickCreateJuryForm']);
Route::post('/admin/events/{event}/jury-accounts', [App\Http\Controllers\Api\AdminController::class, 'createJuryAccount']);
Route::post('/admin/events/{event}/jury-assignments/{assignment}/reset-password', [App\Http\Controllers\Api\AdminController::class, 'resetJuryPassword']);
