<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    echo "It's Working ffff";
});

Route::get('/notifications/test/{user}', [NotificationController::class, 'sendTest'])->middleware('auth:sanctum');