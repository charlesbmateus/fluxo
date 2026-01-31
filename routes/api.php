<?php

use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->group(function () {

        // ───────── ME ─────────
        Route::get('/me', [MeController::class, 'show']);

        // ───────── BOOKINGS ─────────
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);

        // ───────── INVOICES ─────────
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
        Route::patch('/invoices/{invoice}/issue', [InvoiceController::class, 'issue']);
        Route::patch('/invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
        Route::patch('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);

        // ───────── NOTIFICATIONS ─────────
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

        // ───────── SERVICES ─────────
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{service}', [ServiceController::class, 'show']);
        Route::get('/services/{service}/availability', [ServiceController::class, 'availability']);

        // ───────── PROVIDERS ─────────
        Route::get('/providers/{provider}/services', [ServiceController::class, 'byProvider']);
    });
