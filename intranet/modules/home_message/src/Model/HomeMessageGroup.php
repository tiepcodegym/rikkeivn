<?php


namespace Rikkei\HomeMessage\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class HomeMessageGroup extends CoreModel
{
    protected $table = 'm_home_message_groups';
    use SoftDeletes;

    protected $fillable = [
        'name_vi',
        'name_en',
        'name_jp',
        'priority',
        'created_id',
    ];
    protected static $instance;

    /**
     * @return HomeMessageGroup
     */
    public static function makeInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
