<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\SalesErpController;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.submit');
});

// Authenticated Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/', [SalesErpController::class, 'index'])->name('erp.dashboard');
    Route::post('/switch-user', [SalesErpController::class, 'switchUser'])->name('erp.switch-user');
    Route::get('/invoices/{id}/print', [SalesErpController::class, 'printInvoice'])->name('erp.invoices.print');
    Route::get('/quotes/{id}/print', [SalesErpController::class, 'printQuote'])->name('erp.quotes.print');
});
