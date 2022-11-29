<?php

namespace Rikkei\Notify\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Rikkei\Notify\Event\NotifyEvent' => [
            'Rikkei\Notify\Listeners\NotifyCreated'
        ]
    ];
}
