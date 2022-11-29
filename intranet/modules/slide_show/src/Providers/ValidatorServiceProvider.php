<?php

namespace Rikkei\SlideShow\Providers;

use Illuminate\Support\ServiceProvider;
use Rikkei\SlideShow\Services\CustomValidator;

class ValidatorServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        \Validator::resolver(function($translator, $data, $rules, $messages) {
            return new CustomValidator($translator, $data, $rules, $messages);
        });
    }

    public function register()
    {
        //
    }
}
