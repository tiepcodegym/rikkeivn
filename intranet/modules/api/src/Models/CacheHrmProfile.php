<?php

namespace Rikkei\Api\Models;

use Rikkei\Core\Model\CoreModel;


/**
 * cronjob call api
 *
 * @author lamnv
 */
class CacheHrmProfile extends CoreModel
{
    protected $table = 'cache_hrm_profiles';
    protected $fillable = ['key', 'value_serialize'];
}
