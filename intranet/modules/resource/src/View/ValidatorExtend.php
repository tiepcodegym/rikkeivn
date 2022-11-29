<?php

namespace Rikkei\Resource\View;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class ValidatorExtend
{
    /**
     * add validator url
     */
    public static function addUrl()
    {
        Validator::extend('check_url', function($attribute, $value, $parameters) {
            $url = $parameters[0];
            //Your pattern, not pretending to be super precise, so it's up to you
            $pattern = '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i';
            if(  preg_match( $pattern,$value)) {
                return true;
            }
            return false;
        });
    }
}