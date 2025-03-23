<?php

namespace App\Providers;

use App\Services\Implementations\AuthService;
use App\Services\Implementations\ResponseService;
use App\Services\Implementations\TicketService;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\Interfaces\ResponseServiceInterface;
use App\Services\Interfaces\TicketServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(TicketServiceInterface::class, TicketService::class);
        $this->app->bind(ResponseServiceInterface::class, ResponseService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
