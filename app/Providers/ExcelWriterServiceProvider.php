<?php

namespace App\Providers;

use App\Services\ExcelWriter\ExcelWriterService;
use Illuminate\Support\ServiceProvider;

class ExcelWriterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExcelWriterService::class, function ($app) {
            return new ExcelWriterService();
        });
    }
}