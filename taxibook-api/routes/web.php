<?php

use App\Http\Controllers\TipPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Tip payment pages (accessed via link/QR)
Route::prefix('tip')->group(function () {
    Route::get('/{token}', [TipPaymentController::class, 'show'])->name('tip.show');
    Route::get('/{token}/success', [TipPaymentController::class, 'success'])->name('tip.success');
});
