<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\StripeController;

use App\Http\Controllers\Webhooks\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| API V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH
    |--------------------------------------------------------------------------
    */

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | PUBLIC
    |--------------------------------------------------------------------------
    */

    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{service}', [ServiceController::class, 'show']);
    Route::get('/services/{service}/availability', [ServiceController::class, 'availability']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | PROTECTED
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // ───────── AUTH ─────────
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);

        // ───────── ME ─────────
        Route::get('/me', [MeController::class, 'show']);

        // ───────── DASHBOARD ─────────
        Route::get('/dashboard/provider', [DashboardController::class, 'provider']);

        // ───────── CHAT ─────────
        Route::get('/conversations', [ChatController::class, 'index']);
        Route::get('/conversations/{conversation}', [ChatController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [ChatController::class, 'store']);
        Route::patch('/conversations/{conversation}/read', [ChatController::class, 'markAsRead']);

        // ───────── BOOKINGS ─────────
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);

        // Stripe payment intent
        Route::post('/bookings/{booking}/payment-intent', [BookingController::class, 'createPaymentIntent']);

        // ───────── INVOICES ─────────
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::patch('/invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
        Route::patch('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
        Route::patch('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);

        // ───────── NOTIFICATIONS ─────────
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

        // ───────── PROVIDERS ─────────
        Route::get('/providers/{provider}/services', [ServiceController::class, 'byProvider']);

        // ───────── STRIPE CONNECT ─────────
        Route::post('/stripe/connect', [StripeController::class, 'createAccount']);

    });

});

/*
|--------------------------------------------------------------------------
| STRIPE WEBHOOK (NO AUTH)
|--------------------------------------------------------------------------
*/

Route::post('/v1/stripe/webhook', [StripeWebhookController::class, 'handle']);
