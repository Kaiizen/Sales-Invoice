<?php

namespace App\Providers;

use App\FlagDetail;
use App\InventoryMovement;
use App\Observers\FlagDetailObserver;
use App\Observers\InventoryObserver;
use App\Observers\ProductObserver;
use App\Product;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register observers
        Product::observe(ProductObserver::class);
        InventoryMovement::observe(InventoryObserver::class);
        FlagDetail::observe(FlagDetailObserver::class);
    }
}
