<?php

namespace App\Console;

use App\Services\GlobalScmClient;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                app(GlobalScmClient::class)->refreshToken();
            } catch (\Throwable $e) {
                report($e);
            }
        })->hourly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
