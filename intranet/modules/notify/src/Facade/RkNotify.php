<?php

namespace Rikkei\Notify\Facade;

use Illuminate\Support\Facades\Facade;

class RkNotify extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'RkNotify';
    }
}

