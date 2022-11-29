<?php

namespace Rikkei\Api\Models;

use Rikkei\Core\Model\CoreModel;

class ApiToken extends CoreModel
{
    protected $tbl = 'api_tokens';
    protected $fillable = ['route', 'token', 'expired_at'];

    /**
     * get all data
     * @return collection
     */
    public static function getData()
    {
        return self::all();
    }

    /**
     * filter set expired_at
     *
     * @param string $value
     */
    public function setExpiredAtAttribute($value)
    {
        if (!$value) {
            $value = null;
        }
        if ($value == '0000-00-00 00:00:00') {
            $value = null;
        }
        $this->attributes['expired_at'] = $value;
    }
}

