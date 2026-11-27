<?php

use App\Http\Controllers\Api\EventChatController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/events/{event}/chat-settings', [EventChatController::class, 'settings']);
Route::put('/admin/events/{event}/chat-settings', [EventChatController::class, 'updateSettings']);
Route::post('/admin/events/{event}/chat-settings', [EventChatController::class, 'updateSettings']);
Route::get('/admin/events/{event}/chat/messages', [EventChatController::class, 'adminMessages']);
Route::post('/admin/events/{event}/chat/messages', [EventChatController::class, 'sendAdmin']);
Route::post('/admin/messages/{message}/hide', [EventChatController::class, 'hide']);
Route::post('/admin/messages/{message}/unhide', [EventChatController::class, 'unhide']);

Route::get('/events/{event}/chat', [EventChatController::class, 'messages']);
Route::post('/events/{event}/chat', [EventChatController::class, 'send'])->middleware('throttle:30,1');
Route::post('/messages/{message}/report', [EventChatController::class, 'report'])->middleware('throttle:10,1');
