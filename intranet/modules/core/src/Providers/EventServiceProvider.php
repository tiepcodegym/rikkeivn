<?php

namespace Rikkei\Core\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Exception;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Rikkei\Core\Events\DBEvent' => [
            'Rikkei\Core\Listeners\DBLogListener'
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
        /*
         * event clear cache => remove cache file
         */
        Event::listen('cache:cleared', function() {
            try {
                Cache::store('file')->flush();
            } catch (Exception $ex) {
                \Log::info($ex);
            }
        });
    }
}
