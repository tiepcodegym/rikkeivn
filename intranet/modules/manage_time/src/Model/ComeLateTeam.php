<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class ComeLateTeam extends CoreModel
{
    protected $table = 'come_late_teams';

    public static function getTeams($registerId)
    {
    	$registerTable = ComeLateRegister::getTableName();
        $registerTeamTable = self::getTableName();
        $teamTable = Team::getTableName();
        
        return self::join("{$registerTable}", "{$registerTable}.id", "=", "{$registerTeamTable}.come_late_id")
        	->join("{$teamTable}", "{$teamTable}.id", "=", "{$registerTeamTable}.team_id")
            ->where("{$registerTeamTable}.come_late_id", $registerId)
            ->select("{$teamTable}.id as team_id", "{$teamTable}.name as team_name")
            ->get();
    }
}