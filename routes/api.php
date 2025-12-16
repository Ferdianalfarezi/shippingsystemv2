<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokenController;

Route::prefix('token')->group(function () {
    Route::post('/validate', [TokenController::class, 'validateToken']);
    Route::get('/is-expired', [TokenController::class, 'isSystemExpired']);
    Route::get('/expiry', [TokenController::class, 'getSystemExpiry']);
});