<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class LeaveDayTeam extends CoreModel
{
    protected $table = 'leave_day_teams';

    public static function getTeams($registerId)
    {
    	$registerTable = LeaveDayRegister::getTableName();
        $registerTeamTable = self::getTableName();
        $teamTable = Team::getTableName();

        return self::join("{$registerTable}", "{$registerTable}.id", "=", "{$registerTeamTable}.register_id")
        	->join("{$teamTable}", "{$teamTable}.id", "=", "{$registerTeamTable}.team_id")
            ->where("{$registerTeamTable}.register_id", $registerId)
            ->select("{$teamTable}.id as team_id", "{$teamTable}.name as team_name")
            ->get();
    }
}