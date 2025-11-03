<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;

Route::redirect('/', '/dashboard');
require __DIR__.'/auth.php';

Route::middleware(['web','auth'])->group(function () {
    Route::get('/pending', [AccountController::class, 'pending'])->name('pending');

    Route::middleware(['approved'])->group(function () {

        // Seleção de conta
        Route::get('/select-account',  [AccountController::class, 'selectIndex'])->name('select-account');
        Route::post('/select-account', [AccountController::class, 'selectStore'])->name('select-account.store');

        // Dashboard principal
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Endpoints de suporte (AJAX)
        Route::get('/dashboard/transactions', [DashboardController::class, 'transactions'])->name('dashboard.transactions');
        Route::get('/dashboard/daily',        [DashboardController::class, 'daily'])->name('dashboard.daily');

        // Relatório completo (tela dedicada)
        Route::get('/reports/daily-transactions', [ReportsController::class, 'dailyTransactions'])
            ->name('reports.daily-transactions');
    });
});
