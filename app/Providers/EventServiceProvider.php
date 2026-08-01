<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderPlacedNotification;
use App\Listeners\SendOrderStatusNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     */
    protected $listen = [
        OrderPlaced::class => [
            SendOrderPlacedNotification::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
