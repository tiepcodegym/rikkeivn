<?php


namespace Rikkei\Core\View;


use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Rikkei\Core\Jobs\CacheRoleJob;

class PublishQueueToJob
{
    use DispatchesJobs;

    protected static $instance;

    public function __construct()
    {
    }


    public static function makeInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param int $employeeId
     * @param int $specialRoleId
     * @param int $teamId
     */
    public function cacheRole($employeeId = 0, $specialRoleId = 0, $teamId = 0)
    {
        dispatch((new CacheRoleJob((int)$employeeId, (int)$specialRoleId, (int) $teamId)));
    }
}