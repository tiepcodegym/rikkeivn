<?php


namespace Rikkei\HomeMessage\Model;


use Illuminate\Database\Eloquent\Model;

class HomeMessageReceiver extends Model
{
    protected $table ='home_message_receivers';
    public $timestamps = false;

    protected $fillable = [
        'home_message_id',
        'employee_id',
        'team_id',
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