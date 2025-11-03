<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Habilita paginação com template do Bootstrap 5
        Paginator::useBootstrapFive();
        // Se preferir o v4: Paginator::useBootstrapFour();
    }
}
