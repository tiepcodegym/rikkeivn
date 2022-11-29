<?php


namespace Rikkei\HomeMessage\Model;


use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\Team\Model\Team;

class HomeMessage extends CoreModel
{
    protected $table = 'home_messages';
    use SoftDeletes;


    protected static $instance;

    protected $fillable = [
        'message_vi',
        'message_en',
        'message_jp',
        'group_id',
        'start_at',
        'end_at',
        'priority',
        'icon_url',
        'created_id',
        'type_scheduler',
        'is_random'
    ];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function group()
    {
        return $this->hasOne(HomeMessageGroup::class, 'id', 'group_id');
    }

    /**
     * @param $arrTeamReceive
     * @return bool
     */
    public function updateTeamReceive($arrTeamReceive)
    {
        if (!is_array($arrTeamReceive) || count($arrTeamReceive) == 0) {
            return false;
        }
        HomeMessageReceiver::where('home_message_id', $this->id)->delete();
        foreach ($arrTeamReceive as $team_id) {
            HomeMessageReceiver::makeInstance()->insert([
                ['home_message_id' => $this->id, 'team_id' => $team_id, 'send_at' => null]
            ]);
        }
        return true;
    }

    public function getTeamId()
    {
        $arrTeams = HomeMessageReceiver::where('home_message_id', $this->id)->pluck('team_id')->toArray();
        $arrTeams = is_array($arrTeams) ? $arrTeams : [];
        return $arrTeams;
    }

    public function getAllBranch()
    {
        return DB::select(DB::raw("SELECT 
                              * 
                            FROM
                              teams 
                            WHERE `code` IN 
                              (SELECT 
                                IF(
                                  ISNULL(teams.code),
                                  'hanoi',
                                  SUBSTRING_INDEX(teams.code, '_', 1)
                                ) AS team_code 
                              FROM
                                `team_members` 
                                LEFT JOIN teams 
                                  ON teams.id = `team_members`.`team_id` 
                              GROUP BY team_code)")
        );
    }

    public function getBranch()
    {
        if ($this->group && $this->group->team_id)
            return Team::find($this->group->team_id)->name;
        return '';
    }

    public function homeMessageDay()
    {
        return $this->hasOne(HomeMessageDay::class);
    }

    public function homeMessageDayOfWeek()
    {
        $res = [];
        $data = $this->homeMessageDay;
        if ($data) {
            if ($data->is_sun) {
                $res[] = Constant::SUNDAY;
            }
            if ($data->is_mon) {
                $res[] = Constant::MONDAY;
            }
            if ($data->is_tues) {
                $res[] = Constant::TUESDAY;
            }
            if ($data->is_wed) {
                $res[] = Constant::WEDNESDAY;
            }
            if ($data->is_thur) {
                $res[] = Constant::THURSDAY;
            }
            if ($data->is_fri) {
                $res[] = Constant::FRIDAY;
            }
            if ($data->is_sar) {
                $res[] = Constant::SATURDAY;
            }
        }
        return $res;
    }

}
