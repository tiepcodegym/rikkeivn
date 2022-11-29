<?php
namespace Rikkei\Contract;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ContractProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        require_once (__DIR__ . '/ContractConst.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'contract');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'contract');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(!defined('RIKKEI_CONTRACT_PATH')) {
            define('RIKKEI_CONTRACT_PATH', __DIR__ . '/../');
        }
        
        $providers = [
            \Rikkei\Contract\Providers\DatabaseServiceProvider::class,
            \Rikkei\Contract\Providers\RouteServiceProvider::class,
        ];
        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }
}