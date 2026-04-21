<?php

namespace App\Console;

use App\Services\InvoiceService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // 毎朝9時：期限切れ請求書を overdue に更新
        $schedule->call(function () {
            $count = app(InvoiceService::class)->markOverdue();
            logger()->info("入金遅延チェック完了: {$count}件を overdue に更新");
        })->dailyAt('09:00')->name('mark-overdue-invoices');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
