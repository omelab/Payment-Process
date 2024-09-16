<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/square-payment', [PaymentController::class, 'squarePaymentForm'])->name('square_payment');
Route::post('/square-payment', [PaymentController::class, 'squarePaymentProcess'])->name('square_process');