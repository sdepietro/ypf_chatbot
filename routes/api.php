<?php

use App\Http\Controllers\Api\AdvancedChatController;
use App\Http\Controllers\Api\AdvancedMessageController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\SpeechController;
use Illuminate\Support\Facades\Route;

// Protected routes
Route::middleware('master.auth')->group(function () {
    // Configs
    Route::get('/configs', [ConfigController::class, 'index']);
    Route::post('/configs', [ConfigController::class, 'store']);
    Route::get('/configs/{id}', [ConfigController::class, 'show']);
    Route::put('/configs/{id}', [ConfigController::class, 'update']);
    Route::delete('/configs/{id}', [ConfigController::class, 'destroy']);

    // Chats
    Route::get('/chats', [ChatController::class, 'index']);
    Route::post('/chats', [ChatController::class, 'store']);
    Route::get('/chats/{id}', [ChatController::class, 'show']);
    Route::delete('/chats/{id}', [ChatController::class, 'destroy']);

    // Messages
    Route::get('/chats/{chatId}/messages', [MessageController::class, 'index']);
    Route::post('/chats/{chatId}/messages', [MessageController::class, 'store']);

    // Evaluations
    Route::post('/chats/{chatId}/evaluate', [EvaluationController::class, 'evaluate']);
    Route::get('/chats/{chatId}/evaluation', [EvaluationController::class, 'show']);

    // Agents
    Route::get('/agents', [AgentController::class, 'index']);
    Route::post('/agents', [AgentController::class, 'store']);
    Route::get('/agents/{id}', [AgentController::class, 'show']);
    Route::put('/agents/{id}', [AgentController::class, 'update']);
    Route::delete('/agents/{id}', [AgentController::class, 'destroy']);

    // Speech (STT/TTS)
    Route::post('/speech/transcribe', [SpeechController::class, 'transcribe']);
    Route::post('/speech/synthesize', [SpeechController::class, 'synthesize']);
    Route::get('/speech/voices', [SpeechController::class, 'voices']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Advanced Chat
    Route::post('/advanced/chats', [AdvancedChatController::class, 'store']);
    Route::get('/advanced/chats/{id}', [AdvancedChatController::class, 'show']);
    Route::post('/advanced/chats/{chatId}/messages', [AdvancedMessageController::class, 'store']);
    Route::post('/advanced/chats/{chatId}/actions', [AdvancedMessageController::class, 'storeAction']);
});
