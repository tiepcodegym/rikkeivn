<?php

namespace Rikkei\Core;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Queue;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Rikkei\Core\Jobs\PushNotifyToDevices;
use Rikkei\Notify\Model\NotifyMobile;
use Rikkei\Resource\View\getOptions;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        Queue::after(function (JobProcessed $event) {
            if ($event->job->getQueue() == 'mobile') {
                NotifyMobile::where('job_id', $event->job->getJobId())->update(['status' => getOptions::STATUS_INPROGRESS]);
            }
        });
        Queue::failing(function (JobFailed $event) {
            if ($event->job->getQueue() == 'mobile') {
                NotifyMobile::where('job_id', $event->job->getJobId())->update(['status' => getOptions::STATUS_CLOSE]);
            }
        });
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'core');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'core');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_CORE_PATH')) {
            define('RIKKEI_CORE_PATH', __DIR__ . '/../');
        }
        
        $providers = [
            \Rikkei\Core\Providers\AuthServiceProvider::class,
            \Rikkei\Core\Providers\EventServiceProvider::class,
            \Rikkei\Core\Providers\RouteServiceProvider::class,
            \Rikkei\Core\Providers\ThemeServiceProvider::class,
            \Rikkei\Core\Providers\DatabaseServiceProvider::class,
            \Rikkei\Core\Providers\SessionServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        //custom service provider
        $this->app->singleton('db', function ($app) {
            return new Services\CoreDB\CoreDatabaseManager($app, $app['db.factory']);
        });
        //custom excel service provider
        // Bind css parser
        $this->app->singleton('excel.parsers.css', function () {
            return new Services\CoreExcel\CoreCssParser(
                new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles()
            );
        });
    }
}