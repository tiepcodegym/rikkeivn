<?php

namespace Rikkei\Project\Providers;

use Illuminate\Support\ServiceProvider;
use Rikkei\Project\Services\CustomValidator;
use Illuminate\Support\Facades\Validator;

class ValidatorServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::resolver(function($translator, $data, $rules, $messages) {
            return new CustomValidator($translator, $data, $rules, $messages);
        });
    }

    public function register()
    {
    }
}
