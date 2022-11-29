<?php

namespace Rikkei\Notify\Model;

use Rikkei\Core\Model\CoreModel;

class NotifyMobile extends CoreModel
{
    protected $table = 'notify_mobile';
    protected $guarded = [];
    protected static $instance;

    const NOT_SENT = 0;
    const SENT = 1;
    const FAILURE = 2;

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'notify_team_receiver_mobile', 'notify_mobile_id', 'team_id');
    }

    /**
     * @return NotifyMobile
     */
    public static function makeInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return array
     */
    public function getAllType()
    {
        return [
            self::NOT_SENT => trans('notify::view.not_send'),
            self::SENT => trans('notify::view.sent'),
            self::FAILURE => trans('notify::view.failure'),
        ];
    }
}
