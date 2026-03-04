<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\Widget;
use App\Http\Middleware\RateLimitChat;
use App\Http\Middleware\ValidateSiteKey;
use App\Http\Middleware\ValidateVisitorToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Widget API Routes (public, authenticated via X-Site-Key header)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(ValidateSiteKey::class)->group(function () {

    // Get widget configuration (colors, greeting, etc.)
    Route::get('site/config', [Widget\ConfigController::class, 'show']);

    // Start a new conversation
    Route::post('conversations', [Widget\ConversationController::class, 'store']);

    // Conversation-scoped routes (requires X-Visitor-Token)
    Route::prefix('conversations/{conversation}')
        ->middleware(ValidateVisitorToken::class)
        ->group(function () {
            Route::get('messages', [Widget\MessageController::class, 'index']);
            Route::post('messages', [Widget\MessageController::class, 'store'])
                ->middleware(RateLimitChat::class);
        });
});

/*
|--------------------------------------------------------------------------
| Admin Auth Routes (public, no auth required)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')->group(function () {
    Route::post('login', [Admin\AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Admin API Routes (protected by Sanctum token auth)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [Admin\AuthController::class, 'logout']);
    Route::get('auth/user', [Admin\AuthController::class, 'user']);

    // Admin conversation management
    Route::prefix('admin')->group(function () {
        Route::post('presence/heartbeat', [Admin\PresenceController::class, 'heartbeat']);
        Route::post('presence/offline', [Admin\PresenceController::class, 'offline']);
        Route::get('conversations', [Admin\ConversationController::class, 'index']);
        Route::get('conversations/{conversation}', [Admin\ConversationController::class, 'show']);
        Route::patch('conversations/{conversation}', [Admin\ConversationController::class, 'update']);
        Route::post('conversations/{conversation}/messages', [Admin\ConversationController::class, 'sendMessage']);
        Route::post('conversations/{conversation}/read', [Admin\ConversationController::class, 'markAsRead']);

        // Site management
        Route::get('sites', [Admin\SiteController::class, 'index']);
        Route::get('sites/settings-schema', [Admin\SiteController::class, 'settingsSchema']);
        Route::post('sites', [Admin\SiteController::class, 'store']);
        Route::patch('sites/{site}', [Admin\SiteController::class, 'update']);
        Route::post('sites/{site}/regenerate-key', [Admin\SiteController::class, 'regenerateKey']);

        // Analytics
        Route::get('stats', [Admin\StatsController::class, 'index']);
    });
});

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
*/
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => '1.0.0',
    ]);
});
