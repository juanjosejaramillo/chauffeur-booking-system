<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;

Route::get('/', function () {
    return view('welcome');
});

// Receipt routes
Route::get('/booking/{bookingNumber}/receipt', [ReceiptController::class, 'show'])->name('booking.receipt.show');
Route::get('/booking/{bookingNumber}/receipt/download', [ReceiptController::class, 'download'])->name('booking.receipt.download');
Route::get('/booking/{bookingNumber}/receipt/pdf', [ReceiptController::class, 'stream'])->name('booking.receipt.pdf');

// Tip payment pages are now handled by React frontend
