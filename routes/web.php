<?php

use Illuminate\Support\Facades\Route;

/** Controllers */
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TransactionController;

Route::redirect('/', '/dashboard');
require __DIR__ . '/auth.php';

Route::middleware(['web', 'auth'])->group(function () {

    // Página para usuários ainda não aprovados
    Route::get('/pending', [AccountController::class, 'pending'])->name('pending');

    // Rotas protegidas por aprovação
    Route::middleware(['approved'])->group(function () {

        // Seleção de conta (pode ser acessada sem digital definida)
        Route::get('/select-account',  [AccountController::class, 'selectIndex'])
            ->name('select-account');
        Route::post('/select-account', [AccountController::class, 'selectStore'])
            ->name('select-account.store');

        // A partir daqui, exige acesso à conta (query ?digital= ou sessão)
        Route::middleware([\App\Http\Middleware\EnsureDigitalAccess::class])->group(function () {

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            // Endpoints AJAX usados no dashboard (gráficos/listas)
            Route::get('/dashboard/transactions', [DashboardController::class, 'transactions'])
                ->name('dashboard.transactions');
            Route::get('/dashboard/daily', [DashboardController::class, 'daily'])
                ->name('dashboard.daily');

            // Relatórios
            Route::get('/reports/daily-transactions', [ReportsController::class, 'dailyTransactions'])
                ->name('reports.daily-transactions');

            // Transações (tela dedicada)
            // Aceita parâmetros: ?digital=1292&initial=YYYY-MM-DD&final=YYYY-MM-DD&limit=100
            Route::get('/transactions', [TransactionController::class, 'index'])
                ->name('transactions.index');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Healthcheck simples
|--------------------------------------------------------------------------
*/
Route::get('/health', fn () => response()->json(['ok' => true], 200))->name('health');
