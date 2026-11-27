<?php

use App\Http\Controllers\Api\ParticipantController;
use Illuminate\Support\Facades\Route;

Route::get('/registrations', [ParticipantController::class, 'index']);
Route::get('/registrations/{registration}/payment/proof', [ParticipantController::class, 'proof']);
Route::get('/registrations/{registration}/payment', [ParticipantController::class, 'payment']);
Route::post('/registrations/{registration}/payment', [ParticipantController::class, 'storePayment'])
    ->middleware('throttle:10,1');
Route::get('/registrations/{registration}', [ParticipantController::class, 'show']);
Route::get('/registrations/{registration}/files/{file}', [ParticipantController::class, 'downloadFile']);
Route::get('/registrations/{registration}/results', [ParticipantController::class, 'results']);

Route::get('/events/{event}/registration-form', [App\Http\Controllers\Api\ParticipantController::class, 'registrationForm']);
Route::post('/events/{event}/register', [App\Http\Controllers\Api\ParticipantController::class, 'register'])->middleware('throttle:10,1');
