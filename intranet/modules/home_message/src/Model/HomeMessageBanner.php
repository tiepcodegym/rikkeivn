<?php


namespace Rikkei\HomeMessage\Model;


use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class HomeMessageBanner extends CoreModel
{
    use SoftDeletes;

    protected $table = 'home_message_banners';

    protected $fillable = [
        'display_name',
        'image',
        'link',
        'begin_at',
        'end_at',
        'status',
        'type',
        'action_id',
        'event_id',
        'status',
        'gender_target'
    ];

    protected $hidden = ['image', 'pivot'];

    protected $appends = ['image_url'];

    protected static $instance;

    /**
     * @return HomeMessageBanner
     */
    public static function makeInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'home_message_banner_team', 'banner_id', 'team_id');
    }
}
