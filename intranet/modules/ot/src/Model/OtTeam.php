<?php

namespace Rikkei\Ot\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class OtTeam extends CoreModel
{
    protected $table = 'ot_teams';

    public static function getTeams($registerId)
    {
    	$registerTable = OtRegister::getTableName();
        $registerTeamTable = self::getTableName();
        $teamTable = Team::getTableName();

        return self::join("{$registerTable}", "{$registerTable}.id", "=", "{$registerTeamTable}.register_id")
        	->join("{$teamTable}", "{$teamTable}.id", "=", "{$registerTeamTable}.team_id")
            ->where("{$registerTeamTable}.register_id", $registerId)
            ->select("{$teamTable}.id as team_id", "{$teamTable}.name as team_name")
            ->get();
    }
}