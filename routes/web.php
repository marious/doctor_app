<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentProfileController;

Route::get('/', function () {
    echo "It's Working ffff";
});




Route::prefix('payment')->name('payment.profile.')->group(function () {

    Route::get('/',              [PaymentProfileController::class, 'index'])     ->name('index');
    Route::get('/add',           [PaymentProfileController::class, 'add'])       ->name('add');
    Route::match(['get','post'], '/return', [PaymentProfileController::class, 'return'])->name('return');
    Route::post('/{profile}/default', [PaymentProfileController::class, 'setDefault'])->name('default');
    Route::delete('/{profile}',  [PaymentProfileController::class, 'destroy'])  ->name('destroy');

});

Route::get('/iframe-communicator', function () {
    return view('payment.communicator');
});