<?php

namespace Rikkei\Magazine\Providers;

use Illuminate\Support\ServiceProvider;
use Rikkei\Magazine\Services\CustomValidator;

class ValidatorServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        \Validator::resolver(
            function ($translator, $data, $rules, $messages) {
                return new CustomValidator($translator, $data, $rules, $messages);
            }
        );
    }

    public function register()
    {
        //
    }
}
